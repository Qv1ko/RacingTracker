<?php

namespace App\Models;

use App\Models\Participation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Driver extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'name',
        'surname',
        'nationality',
        'status'
    ];
    protected $appends = ['races', 'wins', 'second_positions', 'third_positions', 'points'];

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    protected function races(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->participations()
                ->toBase()
                ->distinct('race_id')
                ->count('race_id'),
        );
    }

    protected function wins(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->participations()
                ->toBase()
                ->where('position', 1)
                ->distinct('race_id')
                ->count('race_id'),
        );
    }

    protected function secondPositions(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->participations()
                ->toBase()
                ->where('position', 2)
                ->distinct('race_id')
                ->count('race_id'),
        );
    }

    protected function thirdPositions(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->participations()
                ->toBase()
                ->where('position', 3)
                ->distinct('race_id')
                ->count('race_id'),
        );
    }

    protected function points(): Attribute
    {
        return Attribute::make(
            get: function () {
                $points = $this->participations()
                    ->select('participations.points')
                    ->join('races', 'participations.race_id', '=', 'races.id')
                    ->orderBy('races.date', 'desc')
                    ->limit(1)
                    ->value('points') ?? null;

                return $points !== null ? number_format((float)$points, 3) : null;
            },
        );
    }

    public function latestParticipation()
    {
        return $this->hasOne(Participation::class)
            ->join('races', 'participations.race_id', '=', 'races.id')
            ->select('participations.*')
            ->latest('races.date')
            ->limit(1);
    }
}
