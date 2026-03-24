<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RecipientStatus;
use App\Models\Contact;
use App\Models\Email;
use App\Models\EmailRecipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailRecipient>
 */
final class EmailRecipientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email_id' => Email::factory(),
            'contact_id' => Contact::factory(),
            'email_address' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'status' => RecipientStatus::Pending,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'status' => RecipientStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => RecipientStatus::Failed,
            'error' => fake()->sentence(),
        ]);
    }
}
