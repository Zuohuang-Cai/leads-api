<?php

declare(strict_types=1);

namespace App\Domain\Lead\ValueObjects;

enum LeadStatus: string
{
    case Nieuw = 'nieuw';
    case Opgepakt = 'opgepakt';
    case Proefrit = 'proefrit';
    case Offerte = 'offerte';
    case Verkocht = 'verkocht';
    case Afgevallen = 'afgevallen';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
