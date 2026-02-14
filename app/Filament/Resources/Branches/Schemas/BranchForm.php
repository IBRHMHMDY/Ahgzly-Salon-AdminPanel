<?php

namespace App\Filament\Resources\Branches\Schemas;

use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Branch Details')
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
                    ])->columns(2),
            ]);
    }
}
