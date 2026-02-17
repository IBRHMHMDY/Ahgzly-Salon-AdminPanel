<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'appointment_date' => $this->appointment_date->format('Y-m-d'),
            'start_time' => $this->start_time->format('H:i'),
            'end_time' => $this->end_time->format('H:i'),
            'status' => $this->status, // سيعود كقيمة نصية أو كائن حسب إعدادات الـ Enum لديك
            'total_price' => (float) $this->total_price,
            'notes' => $this->notes,

            // 🚀 هنا السحر: جلب الكائنات المرتبطة بدلاً من مجرد IDs
            // نستخدم الـ Resources التي أنشأناها في المرحلة الثانية
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'employee' => new StaffResource($this->whenLoaded('employee')),
            'service' => new ServiceResource($this->whenLoaded('service')),
        ];
    }
}
