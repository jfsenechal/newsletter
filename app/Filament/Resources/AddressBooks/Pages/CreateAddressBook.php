<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressBooks\Pages;

use App\Filament\Resources\AddressBooks\AddressBookResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateAddressBook extends CreateRecord
{
    protected static string $resource = AddressBookResource::class;
}
