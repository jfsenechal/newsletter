<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EmailStatus;
use Database\Factories\EmailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Email extends Model
{
    /** @use HasFactory<EmailFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Sender, $this>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(Sender::class);
    }

    /**
     * @return HasMany<EmailRecipient, $this>
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(EmailRecipient::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'status' => EmailStatus::class,
        ];
    }
}
