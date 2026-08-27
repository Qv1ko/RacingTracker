<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\TeamStatsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $nationality
 * @property bool $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Collection<int, Participation> $participations
 */
class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nationality',
        'status',
        'color',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $team) {
            if ($team->color === null) {
                $team->color = \App\Support\Color::fromString($team->name);
            }
        });
    }

    /** @return HasMany<Participation, $this> */
    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    /** @return BelongsToMany<Driver, $this> */
    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'participations');
    }

    /** @return BelongsToMany<Race, $this> */
    public function races(): BelongsToMany
    {
        return $this->belongsToMany(Race::class, 'participations');
    }

    public function stats(): TeamStatsService
    {
        return new TeamStatsService($this);
    }
}
