<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BranchResource;
use App\Http\Resources\Api\ServiceResource;
use App\Http\Resources\Api\StaffResource; // نستخدم موديل الـ STI الخاص بالموظفين
use App\Models\Branch;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    // 1. جلب الفروع
    public function branches()
    {
        // نجلب الفروع الفعالة فقط (إذا كان لديك عمود is_active)
        $branches = Branch::all();

        return BranchResource::collection($branches);
    }

    // 2. جلب الخدمات
    public function services()
    {
        $services = Service::all();

        return ServiceResource::collection($services);
    }

    // 3. جلب الموظفين (مع إمكانية الفلترة)
    public function staff(Request $request)
    {
        // نبدأ الكويري باستخدام موديل الموظف، مع جلب العلاقات (Eager Loading) لمنع مشكلة N+1 Query
        $query = Staff::with(['branch', 'services']);

        // فلترة بالفرع (إذا اختار العميل فرعاً معيناً في التطبيق)
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // فلترة بالخدمة (لمعرفة من هم الموظفون الذين يقدمون خدمة معينة)
        if ($request->has('service_id')) {
            $query->whereHas('services', function ($q) use ($request) {
                $q->where('services.id', $request->service_id);
            });
        }

        $staff = $query->get();

        return StaffResource::collection($staff);
    }
}
