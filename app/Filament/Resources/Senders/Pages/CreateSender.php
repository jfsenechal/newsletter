<?php

declare(strict_types=1);

namespace App\Filament\Resources\Senders\Pages;

use App\Filament\Resources\Senders\SenderResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateSender extends CreateRecord
{
    protected static string $resource = SenderResource::class;
}
