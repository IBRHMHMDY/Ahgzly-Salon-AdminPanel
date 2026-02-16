<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Service;

class AppointmentService
{
    /**
     * التحقق من التداخل لموظف معين في وقت محدد
     */
    public function isEmployeeAvailable(int $employeeId, string $date, string $startTime, string $endTime): bool
    {
        $hasOverlap = Appointment::where('employee_id', $employeeId)
            ->where('appointment_date', $date)
            ->where('status', '!=', AppointmentStatus::CANCELLED->value)
            ->where(function ($query) use ($startTime, $endTime) {
                // شرط التداخل الرياضي: (بداية الجديد < نهاية القديم) و (نهاية الجديد > بداية القديم)
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->exists();

        return ! $hasOverlap;
    }

    /**
     * تعيين تلقائي لأول موظف متاح يقدم هذه الخدمة
     */
    public function autoAssignEmployee(int $branchId, int $serviceId, string $date, string $startTime, string $endTime): ?int
    {
        $service = Service::with(['providers' => function ($query) {
            $query->where('salon_id', auth()->user()->salon_id)
                ->whereHas('roles', fn ($q) => $q->where('name', 'employee'));
        }])->findOrFail($serviceId);

        foreach ($service->providers as $employee) {
            if ($this->isEmployeeAvailable($employee->id, $date, $startTime, $endTime)) {
                return $employee->id; // إرجاع أول موظف متاح
            }
        }

        return null; // لا يوجد موظف متاح
    }
}
