<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Service;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $service = Service::findOrFail($data['service_id']);

        // 1. حساب وقت الانتهاء والسعر بناءً على الخدمة
        $startTime = Carbon::parse($data['start_time']);
        $endTime = $startTime->copy()->addMinutes($service->duration_minutes);

        $data['end_time'] = $endTime->format('H:i');
        $data['total_price'] = $service->price;

        $appointmentService = app(AppointmentService::class);

        // 2. Auto-Assign logic إذا لم يقم موظف الاستقبال باختيار موظف
        if (empty($data['employee_id'])) {
            $assignedEmployeeId = $appointmentService->autoAssignEmployee(
                $data['branch_id'],
                $data['service_id'],
                $data['appointment_date'],
                $data['start_time'],
                $data['end_time']
            );

            if (! $assignedEmployeeId) {
                Notification::make()
                    ->danger()
                    ->title('No Available Staff')
                    ->body('There are no available employees for this service at the selected time.')
                    ->send();

                throw ValidationException::withMessages(['employee_id' => 'No staff available.']);
            }

            $data['employee_id'] = $assignedEmployeeId;
        } else {
            // 3. Overlap Prevention logic في حال تم اختيار الموظف يدوياً
            $isAvailable = $appointmentService->isEmployeeAvailable(
                $data['employee_id'],
                $data['appointment_date'],
                $data['start_time'],
                $data['end_time']
            );

            if (! $isAvailable) {
                Notification::make()
                    ->danger()
                    ->title('Time Conflict')
                    ->body('The selected employee already has an appointment at this time.')
                    ->send();

                throw ValidationException::withMessages(['start_time' => 'Employee is not available at this time.']);
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
