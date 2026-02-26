<?php

declare(strict_types=1);

namespace App\Domain\Lead\Exceptions;

use RuntimeException;

final class LeadNotFoundException extends RuntimeException
{
    public static function withId(int $id): self
    {
        return new self("Lead met ID {$id} niet gevonden.");
    }
}
