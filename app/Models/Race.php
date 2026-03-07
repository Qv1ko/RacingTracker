<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property string $season
 */
class Race extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
    ];

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'participations');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'participations');
    }

    public static function seasons(): Collection
    {
        return self::orderByDesc('date')
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->format('Y'))
            ->unique()
            ->sort()
            ->values();
    }

    protected function season(): Attribute
    {
        return Attribute::make(
            get: fn () => Carbon::parse($this->date)->format('Y'),
        );
    }

    public function getMore(): Collection
    {
        return Race::where('name', $this->name)
            ->whereNot('id', $this->id)
            ->get();
    }

    public function participant(int $position): ?Participation
    {
        return $this->participations()
            ->with('driver', 'team')
            ->where('position', $position)
            ->whereHas('driver')
            ->first();
    }

    public function betterDriver(): ?Participation
    {
        return $this->participations()
            ->with('driver')
            ->get()
            ->map(function ($participation) {
                $previous = Participation::where('driver_id', $participation->driver_id)
                    ->whereHas('race', fn ($q) => $q->where('date', '<', $this->date))
                    ->orderByDesc(
                        Race::select('date')
                            ->whereColumn('races.id', 'participations.race_id')
                            ->limit(1)
                    )
                    ->value('points');

                if (! $previous) {
                    return null;
                }

                $participation->points_diff = $participation->points - $previous;

                return $participation;
            })
            ->filter()
            ->sortByDesc('points_diff')
            ->first();
    }

    public function betterTeam(): ?Participation
    {
        return $this->participations()
            ->with('team')
            ->whereNotNull('team_id')
            ->get()
            ->groupBy('team_id')
            ->map(function ($teamParticipations) {
                $previous = Participation::where('team_id', $teamParticipations->first()->team_id)
                    ->whereHas('race', fn ($q) => $q->where('date', '<', $this->date))
                    ->orderByDesc(
                        Race::select('date')
                            ->whereColumn('races.id', 'participations.race_id')
                            ->limit(1)
                    )
                    ->value('points');

                if (! $previous) {
                    return null;
                }

                $participation = $teamParticipations->first();
                $participation->points_diff = $teamParticipations->avg('points') - $previous;

                return $participation;
            })
            ->filter()
            ->sortByDesc('points_diff')
            ->first();
    }

    public function scopeInSeason(Builder $query, ?string $season = null): Builder
    {
        return $query->when($season, fn ($q) => $q->whereYear('date', $season));
    }
}
