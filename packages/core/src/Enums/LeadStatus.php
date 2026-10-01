<?php

declare(strict_types=1);

namespace Focal\Core\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Open = 'open';
    case InProgress = 'in_progress';
    case AttemptedContact = 'attempted_contact';
    case Connected = 'connected';
    case BadTiming = 'bad_timing';
    case Unqualified = 'unqualified';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New Lead',
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::AttemptedContact => 'Attempted Contact',
            self::Connected => 'Connected',
            self::BadTiming => 'Bad Timing',
            self::Unqualified => 'Unqualified',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Open => 'primary',
            self::InProgress => 'warning',
            self::AttemptedContact => 'warning',
            self::Connected => 'success',
            self::BadTiming => 'gray',
            self::Unqualified => 'danger',
        };
    }
}
