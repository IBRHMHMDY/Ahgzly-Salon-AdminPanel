<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'salon' => $this->salon->name,
            'name' => $this->name,
            'price' => (float) $this->price, // تحويل صريح لتجنب مشاكل الأنواع في الموبايل
            'duration_minutes' => (int) $this->duration_minutes, // المدة بالدقائق
            'is_active' => (bool) $this->is_active,
        ];
    }
}
