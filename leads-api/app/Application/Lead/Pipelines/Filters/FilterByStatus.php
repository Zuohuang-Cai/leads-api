<?php

declare(strict_types=1);

namespace App\Application\Lead\Pipelines\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

final readonly class FilterByStatus
{
    public function __construct(
        private ?string $status,
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->status !== null && $this->status !== '') {
            $query->where('status', $this->status);
        }

        return $next($query);
    }
}
