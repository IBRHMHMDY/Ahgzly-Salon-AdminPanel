<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'reference_number',
        'branch_id', 'customer_id', 'employee_id', 'service_id',
        'appointment_date', 'start_time', 'end_time',
        'status', 'total_price', 'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'status' => AppointmentStatus::class,
    ];

    protected static function booted()
    {
        static::creating(function ($appointment) {
            do {
                // توليد رقم عشوائي من 6 أرقام
                $number = mt_rand(100000, 999999);
            } while (self::where('reference_number', $number)->exists()); // التأكد من عدم تكراره في قاعدة البيانات

            $appointment->reference_number = (string) $number;
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
