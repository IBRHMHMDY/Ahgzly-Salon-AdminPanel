<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title ?? 'Staff Member', // المسمى الوظيفي إن وجد
            'branch' => new BranchResource($this->whenLoaded('branch')), // جلب الفرع التابع له
            'services' => ServiceResource::collection($this->whenLoaded('services')), // جلب الخدمات التي يقدمها
            // 'services' => ServiceResource::collection($this->services),
        ];
    }
}
