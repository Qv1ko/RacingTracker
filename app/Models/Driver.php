<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\DriverStatsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $surname
 * @property string|null $nationality
 * @property bool $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Collection<int, Participation> $participations
 */
class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'surname',
        'nationality',
        'status',
        'color',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $driver) {
            if ($driver->color === null) {
                $driver->color = \App\Support\Color::fromString($driver->name.' '.$driver->surname);
            }
        });
    }

    /** @return HasMany<Participation, $this> */
    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    /** @return BelongsToMany<Team, $this> */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'participations');
    }

    /** @return BelongsToMany<Race, $this> */
    public function races(): BelongsToMany
    {
        return $this->belongsToMany(Race::class, 'participations');
    }

    public function stats(): DriverStatsService
    {
        return new DriverStatsService($this);
    }
}
