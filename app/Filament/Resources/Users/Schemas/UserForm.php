<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Employee Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('phone')
                            ->tel(),

                        // Password handling: Required on create, nullable on edit
                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),

                        // ✅ القيد: إخفاء الفرع الرئيسي من القائمة
                        // بما أن الـ Owner فقط هو من يملك الفرع الرئيسي، ونحن هنا ننشئ موظفين، نمنع اختيار الفرع الرئيسي.
                        Select::make('branch_id')
                            ->relationship(
                                'branch',
                                'name',
                                // شرط: اعرض الفروع التابعة للصالون الحالي والتي ليست "رئيسية"
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->where('salon_id', Auth::user()->salon_id)
                                    ->where('is_main', false)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Assigned Branch')
                            ->helperText('Main Branch is reserved for the Owner.'),

                        // ✅ القيد: إخفاء دور Owner
                        Select::make('roles')
                            ->relationship(
                                'roles',
                                'name',
                                modifyQueryUsing: fn (Builder $query) => $query->where('name', '!=', 'Owner')
                            )
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
