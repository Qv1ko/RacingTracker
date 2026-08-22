<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $driver_id
 * @property int|null $team_id
 * @property int $race_id
 * @property int|null $position
 * @property string $status
 * @property float $points
 * @property float $uncertainty
 * @property float|null $points_diff
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Driver $driver
 * @property Team|null $team
 * @property Race $race
 */
class Participation extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'team_id',
        'race_id',
        'position',
        'status',
        'points',
        'uncertainty',
    ];

    /** @return BelongsTo<Driver, $this> */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /** @return BelongsTo<Team, $this> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /** @return BelongsTo<Race, $this> */
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }
}
