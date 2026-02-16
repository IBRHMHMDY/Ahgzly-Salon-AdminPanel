<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Enums\AppointmentStatus;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Appointment Details')
                    ->schema([
                        Select::make('branch_id')
                            ->relationship('branch', 'name', fn (Builder $query) => $query->where('salon_id', auth()->user()->salon_id))
                            ->required(),

                        Select::make('customer_id')
                            ->relationship(
                                name: 'customer', // اسم العلاقة في الموديل
                                titleAttribute: 'name', // العمود الذي سيظهر للمستخدم (الاسم وليس الرقم)
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->whereHas('roles', fn ($q) => $q
                                        ->where('name', 'customer')))
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('email')->email()->required()->unique(table: 'users', column: 'email'),
                                TextInput::make('password')->password()
                                    ->revealable() // يسمح للموظف برؤية كلمة المرور لإخبار العميل بها
                                    ->default(fn () => Str::random(8)) // توليد 8 أحرف عشوائية تلقائياً
                                    ->required()
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                                // سيتم إعطاؤه دور customer برمجياً لاحقاً
                            ])
                            ->required(),

                        Select::make('service_id')
                            ->relationship('service', 'name', fn (Builder $query) => $query->where('salon_id', auth()->user()->salon_id))
                            ->required()
                            ->live(), // Live لجلب المدة الزمنية

                        Select::make('employee_id')
                            ->relationship('employee', 'name', fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'employee')))
                            ->searchable()
                            ->preload()
                            ->label('Employee (Leave empty for Auto-Assign)'), // اختياري للتعيين التلقائي
                    ])->columns(2)
                    ->columnSpanFull(),

                Section::make('Schedule')
                    ->schema([
                        DatePicker::make('appointment_date')
                            ->required()
                            ->native(false)
                            ->minDate(today()),

                        TimePicker::make('start_time')
                            ->required()
                            ->seconds(false)
                            ->rules([
                                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    $appointmentDate = $get('appointment_date');

                                    if ($appointmentDate && $value) {
                                        // دمج التاريخ المختار مع الوقت المختار
                                        $selectedDateTime = Carbon::parse($appointmentDate.' '.$value);

                                        // إذا كان الوقت المدمج في الماضي مقارنة بوقت السيرفر الآن
                                        if ($selectedDateTime->isPast()) {
                                            $fail('عذراً، لا يمكنك اختيار وقت قد مضى في هذا اليوم.');
                                        }
                                    }
                                },
                            ]),
                        ToggleButtons::make('status')
                            ->options(AppointmentStatus::class)
                            ->inline()
                            ->required()
                            ->default(AppointmentStatus::PENDING),

                        Textarea::make('notes'),

                    ])->columns(2)
                    ->columnSpanFull(),
            ]);

    }
}
