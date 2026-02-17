<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Staff extends User
{
    protected $table = 'users';

    protected $guard_name = 'web';

    // هام جداً لكي تعمل صلاحيات Spatie بشكل صحيح مع هذا الموديل الوهمي
    public function getMorphClass()
    {
        return User::class;
    }

    protected static function booted()
    {
        parent::booted();

        // فلتر: إظهار من ليسوا عملاء (الملاك، مديرين الفروع، الموظفين)
        static::addGlobalScope('staff_only', function (Builder $builder) {
            $builder->whereHas('roles', function ($query) {
                $query->where('name', '!=', 'Customer');
            });
        });
    }

    // Relationships
    public function services(): BelongsToMany
    {
        // يجب تحديد اسم الجدول الوسيط، ومفتاح الموظف (user_id)، ومفتاح الخدمة (service_id)
        return $this->belongsToMany(Service::class, 'service_user', 'user_id', 'service_id', 'id', 'id');
    }
}
