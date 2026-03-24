<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EmailStatus;
use App\Models\Email;
use App\Models\Sender;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Email>
 */
final class EmailFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'sender_id' => Sender::factory(),
            'subject' => fake()->sentence(),
            'body' => fake()->paragraphs(3, true),
            'attachments' => null,
            'status' => EmailStatus::Draft,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'status' => EmailStatus::Sent,
        ]);
    }

    public function sending(): static
    {
        return $this->state(fn (): array => [
            'status' => EmailStatus::Sending,
        ]);
    }
}
