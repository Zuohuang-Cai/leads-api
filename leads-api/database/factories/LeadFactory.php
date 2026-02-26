<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Lead\ValueObjects\LeadSource;
use App\Domain\Lead\ValueObjects\LeadStatus;
use App\Infrastructure\Lead\Models\LeadEloquentModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadEloquentModel>
 */
final class LeadFactory extends Factory
{
    protected $model = LeadEloquentModel::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'source' => fake()->randomElement(LeadSource::values()),
            'status' => fake()->randomElement(LeadStatus::values()),
        ];
    }
}
