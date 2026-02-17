<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'is_main' => (bool) $this->is_main,
            // يمكنك إضافة خطوط الطول والعرض هنا إذا كنت ستعرض الفروع على خريطة في التطبيق
        ];
    }
}
