<?php

declare(strict_types=1);

namespace App\Filament\Resources\Emails\Tables;

use App\Enums\EmailStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class EmailsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->sortable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('sender.name')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (EmailStatus $state): string => match ($state) {
                        EmailStatus::Draft => 'gray',
                        EmailStatus::Sending => 'warning',
                        EmailStatus::Sent => 'success',
                        EmailStatus::Failed => 'danger',
                    }),
                TextColumn::make('sent_count')
                    ->label('Progress')
                    ->state(fn ($record): string => "{$record->sent_count}/{$record->total_count}")
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
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
