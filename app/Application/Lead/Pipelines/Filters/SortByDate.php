<?php

declare(strict_types=1);

namespace App\Application\Lead\Pipelines\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

final readonly class SortByDate
{
    public function __construct(
        private string $direction = 'desc',
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        $query->orderBy('created_at', $this->direction);

        return $next($query);
    }
}
