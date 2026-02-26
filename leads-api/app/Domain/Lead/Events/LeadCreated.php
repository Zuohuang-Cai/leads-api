<?php

declare(strict_types=1);

namespace App\Domain\Lead\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class LeadCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $leadId,
        public readonly string $name,
        public readonly string $email,
    ) {}
}
