<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'salon_id',
        'branch_id',
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole(['Owner', 'Manager', 'Employee']);
    }

    // Check User
    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (Auth::check() && Auth::hasUser() && Auth::user()->salon_id && ! $user->salon_id) {
                $user->salon_id = Auth::user()->salon_id;
            }
            static::created(function ($user) {
                // إذا كنت تستخدم Spatie Permissions
                $user->assignRole('customer');
            });
        });
    }

    // Appear Current UserName and YourRole
    public function getFilamentName(): string
    {
        // جلب أول دور (Role) للمستخدم، وإذا لم يوجد نكتب (No Role)
        $roleName = $this->roles->first()?->name ?? 'No Role';

        // إرجاع الاسم وبجانبه الدور بين قوسين
        return "{$this->name} ({$roleName})";
    }

    // Relationships
    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_user');
    }
}
