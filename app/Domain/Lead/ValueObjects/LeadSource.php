<?php

declare(strict_types=1);

namespace App\Domain\Lead\ValueObjects;

enum LeadSource: string
{
    case Website = 'website';
    case Email = 'email';
    case Telefoon = 'telefoon';
    case Whatsapp = 'whatsapp';
    case Showroom = 'showroom';
    case Overig = 'overig';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
