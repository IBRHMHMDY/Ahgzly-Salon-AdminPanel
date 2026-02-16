<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Enums\AppointmentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('appointment_date')->date()->sortable(),
                TextColumn::make('start_time')->time('H:i'),
                TextColumn::make('customer.name')->searchable(),
                TextColumn::make('employee.name')->label('Staff'),
                TextColumn::make('service.name'),
                TextColumn::make('status')->badge(),
                TextColumn::make('total_price')->money('EGP'),
            ])
            ->filters([
                TernaryFilter::make('status')->options(AppointmentStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
