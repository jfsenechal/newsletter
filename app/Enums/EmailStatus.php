<?php

declare(strict_types=1);

namespace App\Enums;

enum EmailStatus: string
{
    case Draft = 'draft';
    case Sending = 'sending';
    case Sent = 'sent';
    case Failed = 'failed';
}
