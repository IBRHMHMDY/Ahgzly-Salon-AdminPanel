<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Models\Branch;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')->tabs([
                    Tab::make('Branch Details')

                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('phone')
                                ->tel()
                                ->maxLength(20),
                            TextInput::make('address')
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Toggle::make('is_main')
                                ->label('Main Branch')
                                ->default(false)
                                ->helperText('Only one branch can be main.')
                                ->rule(static function (?Model $record) {
                                    return function (string $attribute, $value, Closure $fail) use ($record) {
                                        // نتحقق إذا كانت القيمة الممررة true (أو 1)
                                        if ($value == true) {
                                            $query = \App\Models\Branch::where('is_main', true);

                                            // ✅ الطريقة الصحيحة في Filament لاستثناء السجل الحالي أثناء التعديل
                                            if ($record) {
                                                $query->where('id', '!=', $record->id);
                                            }

                                            if ($query->exists()) {
                                                $fail('The system can only have ONE Main Branch.');
                                            }
                                        }
                                    };
                                }),

                            Toggle::make('is_active')
                                ->default(true),
                        ]),
                    Tab::make('Working Hours')

                        ->schema([
                            Repeater::make('workingHours')
                                ->relationship() // يعتمد على علاقة workingHours في الموديل
                                ->schema([
                                    Select::make('day_of_week')
                                        ->label('Day')
                                        ->options([
                                            0 => 'Sunday',
                                            1 => 'Monday',
                                            2 => 'Tuesday',
                                            3 => 'Wednesday',
                                            4 => 'Thursday',
                                            5 => 'Friday',
                                            6 => 'Saturday',
                                        ])
                                        ->required()
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems(), // يمنع اختيار نفس اليوم مرتين

                                    TimePicker::make('open_time')
                                        ->label('Opening Time')
                                        ->seconds(false) // لا نحتاج ثواني
                                        ->required(fn (Get $get) => ! $get('is_closed')) // مطلوب فقط إذا لم يكن مغلقاً
                                        ->disabled(fn (Get $get) => $get('is_closed')),

                                    TimePicker::make('close_time')
                                        ->label('Closing Time')
                                        ->seconds(false)
                                        ->required(fn (Get $get) => ! $get('is_closed'))
                                        ->disabled(fn (Get $get) => $get('is_closed')),

                                    Toggle::make('is_closed')
                                        ->label('Closed Day')
                                        ->live() // لتحديث حالة الحقول الأخرى فوراً عند التبديل
                                        ->default(false),
                                ])
                                ->columns(4)
                                ->defaultItems(7) // افتراضياً يعرض 7 حقول للأيام
                                ->maxItems(7)    // أقصى عدد 7 أيام
                                ->reorderable(false) // لا داعي لإعادة الترتيب
                                ->addActionLabel('Add Day'),
                        ]),
                    Tab::make('Exceptional Closures (Holidays)')

                        ->schema([
                            Repeater::make('closures')
                                ->relationship() // يعتمد على علاقة closures في موديل Branch
                                ->schema([
                                    DatePicker::make('closure_date')
                                        ->label('Closure Date')
                                        ->required()
                                        ->native(false)
                                        // يمكن إضافة validation لعدم اختيار تاريخ في الماضي لو أردت
                                        ->minDate(now()),

                                    TextInput::make('reason')
                                        ->label('Reason (Optional)')
                                        ->maxLength(255)
                                        ->placeholder('e.g. National Holiday, Renovation'),
                                ])
                                ->columns(2)
                                ->defaultItems(0) // لا نضيف عناصر افتراضية
                                ->addActionLabel('Add Closure Date'),
                        ]),
                ])->columnSpanFull(),

            ]);
    }
}
