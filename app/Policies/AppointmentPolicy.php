<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    // إدارة المواعيد (Appointments)
    // 'view_all_appointment'  رؤية مواعيد الجميع
    // 'view_own_appointment' رؤية مواعيدالخاصة بالموظف الحالى
    // 'create_appointment'
    // 'update_appointment'
    // 'delete_appointment'

    public function viewAny(User $user): bool
    {
        // يسمح بالدخول لصفحة المواعيد لمن يملك رؤية الكل أو رؤية الخاص به
        return $user->hasAnyPermission(['view_any_appointment', 'view_own_appointment']);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->hasPermissionTo('view_appointment');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_appointment');
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->can('update_appointment') || $user->id === $appointment->employee_id;
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->hasPermissionTo('delete_appointment');
    }
}
