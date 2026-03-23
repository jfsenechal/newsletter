<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AddressBook;
use App\Models\Contact;
use App\Models\Sender;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => config('app.default_user.name'),
            'email' => config('app.default_user.email'),
            'password' => bcrypt(config('app.default_user.password')),
        ]);

        $otherUsers = User::factory(3)->create();

        // Create contacts for admin
        $adminContacts = Contact::factory(20)->create(['user_id' => $admin->id]);

        // Create address books for admin and attach contacts
        $adminAddressBooks = AddressBook::factory(3)->create(['user_id' => $admin->id]);
        foreach ($adminAddressBooks as $addressBook) {
            $addressBook->contacts()->attach(
                $adminContacts->random(rand(5, 10))->pluck('id')
            );
        }

        // Create senders for admin
        Sender::factory(2)->create(['user_id' => $admin->id]);

        // Share first address book with another user (read access)
        $adminAddressBooks->first()->sharedWithUsers()->attach($otherUsers->first()->id, ['permission' => 'read']);

        // Create data for other users
        foreach ($otherUsers as $user) {
            $contacts = Contact::factory(10)->create(['user_id' => $user->id]);
            $addressBooks = AddressBook::factory(2)->create(['user_id' => $user->id]);

            foreach ($addressBooks as $addressBook) {
                $addressBook->contacts()->attach(
                    $contacts->random(rand(3, 7))->pluck('id')
                );
            }

            Sender::factory(1)->create(['user_id' => $user->id]);
        }
    }
}
