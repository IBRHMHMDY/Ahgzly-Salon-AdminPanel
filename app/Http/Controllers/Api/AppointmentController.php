<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BranchClosure;
use App\Models\BranchWorkingHour;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    use AuthorizesRequests;
    /**
     * 1. جلب المواعيد المتاحة (Available Slots)
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'employee_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $date = $request->date;
        $branchId = $request->branch_id;
        $carbonDate = Carbon::parse($date);

        // --- 1. التحقق من إغلاق الفرع الاستثنائي (Branch Closures) ---
        $isClosed = BranchClosure::where('branch_id', $branchId)
            ->whereDate('closure_date', $date)
            ->exists();

        if ($isClosed) {
            return response()->json(['available_slots' => []]); // الفرع مغلق في هذا اليوم
        }

        // --- 2. التحقق من ساعات العمل الأساسية للفرع (Working Hours) ---
        // نستخدم اسم اليوم باللغة الإنجليزية (مثلاً: Monday) أو رقمه حسب ما خزنته في قاعدة البيانات
        // سأفترض هنا أنك تخزن اسم اليوم (English Day Name). إذا كنت تخزن أرقام، استخدم $carbonDate->dayOfWeek
        $dayOfWeek = $carbonDate->dayOfWeek;

        $workingHour = BranchWorkingHour::where('branch_id', $branchId)
            ->where('day_of_week', $dayOfWeek)
            ->first();
        // إذا لم يكن هناك إعدادات لهذا اليوم، أو كان اليوم مغلقاً (is_closed = true)
        if (! $workingHour || $workingHour->is_closed) {
            return response()->json(['available_slots' => []]);
        }

        // --- 3. تهيئة أوقات العمل وحساب المواعيد ---
        $workStart = Carbon::parse("$date {$workingHour->open_time}");
        $workEnd = Carbon::parse("$date {$workingHour->close_time}");
        $service = Service::findOrFail($request->service_id);

        // 🚀 الحل: معالجة الإغلاق بعد منتصف الليل (Overnight Shifts)
        if ($workEnd->lte($workStart)) {
            $workEnd->addDay(); // إضافة 24 ساعة لوقت الإغلاق ليكون في اليوم التالي
        }

        // جلب المواعيد المؤكدة/المعلقة للموظف في هذا اليوم لمنع التعارض
        // ملاحظة: يمكنك فلترة الـ Status هنا بناءً على الـ Enum الخاص بك (مثلاً تجاهل المواعيد الملغاة)
        $existingAppointments = Appointment::where('employee_id', $request->employee_id)
            ->whereDate('appointment_date', $date)
            ->get();

        $availableSlots = [];
        $currentTime = $workStart->copy();
        $serviceDuration = $service->duration_minutes;

        // توليد الفترات الزمنية والتحقق من التداخل
        while ($currentTime->copy()->addMinutes($serviceDuration)->lte($workEnd)) {
            $slotStart = $currentTime->format('H:i:s');
            $slotEnd = $currentTime->copy()->addMinutes($serviceDuration)->format('H:i:s');

            $isOverlapping = $existingAppointments->contains(function ($appointment) use ($slotStart, $slotEnd) {
                // تحويل أوقات الموعد الحالي إلى H:i:s للمقارنة الدقيقة
                $appStart = Carbon::parse($appointment->start_time)->format('H:i:s');
                $appEnd = Carbon::parse($appointment->end_time)->format('H:i:s');

                return ($slotStart >= $appStart && $slotStart < $appEnd) ||
                       ($slotEnd > $appStart && $slotEnd <= $appEnd) ||
                       ($slotStart <= $appStart && $slotEnd >= $appEnd);
            });

            if (! $isOverlapping) {
                // نُرجع الوقت بتنسيق H:i ليسهل استخدامه في الموبايل
                $availableSlots[] = $currentTime->format('H:i');
            }

            $currentTime->addMinutes($serviceDuration);
        }

        return response()->json([
            'date' => $date,
            'employee_id' => $request->employee_id,
            'available_slots' => $availableSlots,
        ]);
    }

    /**
     * 2. إنشاء الموعد (Create Appointment)
     */
    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'employee_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        $service = Service::findOrFail($request->service_id);

        $startTime = Carbon::parse($request->date.' '.$request->start_time);
        $endTime = $startTime->copy()->addMinutes($service->duration_minutes);

        // Concurrency Check: التأكد مرة أخيرة قبل الحفظ في قاعدة البيانات
        $overlap = Appointment::where('employee_id', $request->employee_id)
            ->whereDate('appointment_date', $request->date)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime->format('H:i:s'), $endTime->format('H:i:s')])
                    ->orWhereBetween('end_time', [$startTime->format('H:i:s'), $endTime->format('H:i:s')]);
            })->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_time' => ['عذراً، هذا الموعد تم حجزه للتو. يرجى اختيار موعد آخر.'],
            ]);
        }

        // الحفظ داخل Transaction
        $appointment = DB::transaction(function () use ($request, $service, $startTime, $endTime) {
            return Appointment::create([
                'branch_id' => $request->branch_id,
                'customer_id' => $request->user()->id, // من Sanctum Token
                'employee_id' => $request->employee_id,
                'service_id' => $service->id,
                'appointment_date' => $request->date,
                'start_time' => $startTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'total_price' => $service->price,
                'notes' => $request->notes,
                // استبدل 'Pending' بالقيمة الصحيحة الموجودة داخل ملف الـ Enum الخاص بك
                'status' => AppointmentStatus::PENDING,
            ]);
        });
        $appointment->load(['branch', 'employee', 'service']);

        return response()->json([
            'message' => 'تم تأكيد الموعد بنجاح',
            // 2. استخدام الـ Resource لتنسيق المخرجات
            'appointment' => new \App\Http\Resources\Api\AppointmentResource($appointment),
        ], 201);
    }

    /**
     * 3. جلب حجوزات العميل الحالي (My Appointments)
     */
    public function myAppointments(Request $request)
    {
        // استخدام with() لجلب العلاقات من قاعدة البيانات بـ Query واحد
        $appointments = Appointment::with(['branch', 'employee', 'service'])
            ->where('customer_id', $request->user()->id)
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        // إرجاع مصفوفة من الـ Resources
        return response()->json([
            'appointments' => \App\Http\Resources\Api\AppointmentResource::collection($appointments),
        ]);
    }

   public function updateStatus(Request $request, Appointment $appointment)
    {
        // 1. التحقق من الصلاحيات (Authorization) باستخدام الـ Policy
        // $this->authorize('update', Appointment::find($appointment->id));

        // 2. التحقق من البيانات المرسلة
        $validated = $request->validate([
            'status' => ['required', Rule::in([AppointmentStatus::CONFIRMED, AppointmentStatus::CANCELLED, AppointmentStatus::COMPLETED])],
        ]);

        // 3. تحديث الحالة
        $appointment->update([
            'status' => $validated['status']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الحجز بنجاح',
            'data' => [
                'id' => $appointment->id,
                'status' => $appointment->status,
                'reference_number' => $appointment->reference_number,
            ]
        ]);
    }
}
