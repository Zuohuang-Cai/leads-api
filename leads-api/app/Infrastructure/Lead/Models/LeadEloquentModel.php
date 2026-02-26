<?php

declare(strict_types=1);

namespace App\Infrastructure\Lead\Models;

use App\Domain\Lead\ValueObjects\LeadSource;
use App\Domain\Lead\ValueObjects\LeadStatus;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property LeadSource $source
 * @property LeadStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
final class LeadEloquentModel extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    protected $table = 'leads';

    protected $fillable = [
        'name',
        'email',
        'source',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'source' => LeadSource::class,
            'status' => LeadStatus::class,
        ];
    }

    protected static function newFactory(): LeadFactory
    {
        return LeadFactory::new();
    }
}
