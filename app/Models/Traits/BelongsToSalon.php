<?php

namespace App\Models\Traits;

use App\Models\Salon;
use App\Models\Scopes\SalonScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToSalon
{
    protected static function bootBelongsToSalon(): void
    {
        static::addGlobalScope(new SalonScope);

        // عند الإنشاء، قم بتعبئة salon_id تلقائياً من المستخدم الحالي
        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->salon_id && ! $model->salon_id) {
                $model->salon_id = Auth::user()->salon_id;
            }
        });
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }
}
