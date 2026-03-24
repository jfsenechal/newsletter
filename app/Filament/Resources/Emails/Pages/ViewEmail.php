<?php

declare(strict_types=1);

namespace App\Filament\Resources\Emails\Pages;

use App\Enums\EmailStatus;
use App\Filament\Resources\Emails\EmailResource;
use App\Handler\MailerHandler;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

final class ViewEmail extends ViewRecord
{
    protected static string $resource = EmailResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('subject')
                    ->columnSpanFull(),
                TextEntry::make('sender.name')
                    ->label('Sender'),
                TextEntry::make('sender.email')
                    ->label('Sender Email'),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn (EmailStatus $state): string => match ($state) {
                        EmailStatus::Draft => 'gray',
                        EmailStatus::Sending => 'warning',
                        EmailStatus::Sent => 'success',
                        EmailStatus::Failed => 'danger',
                    }),
                TextEntry::make('total_count')
                    ->label('Recipients'),
                TextEntry::make('body')
                    ->html()
                    ->prose()
                    ->columnSpanFull(),
                RepeatableEntry::make('recipients')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email_address'),
                        TextEntry::make('status')
                            ->badge(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Send Email')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Send Newsletter')
                ->modalDescription(fn (): string => "This will send the email to {$this->record->total_count} recipients. Continue?")
                ->visible(fn (): bool => $this->record->status === EmailStatus::Draft || $this->record->status === EmailStatus::Failed)
                ->action(fn () => MailerHandler::sendEmail($this->record)),
            Action::make('progress')
                ->label(fn (): string => "Sent: {$this->record->sent_count}/{$this->record->total_count}")
                ->icon('heroicon-o-chart-bar')
                ->color(fn (): string => match ($this->record->status) {
                    EmailStatus::Sending => 'warning',
                    EmailStatus::Sent => 'success',
                    EmailStatus::Failed => 'danger',
                    default => 'gray',
                })
                ->disabled()
                ->visible(fn (): bool => $this->record->status !== EmailStatus::Draft),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
