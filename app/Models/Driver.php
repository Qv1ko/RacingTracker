<?php

namespace App\Models;

use App\Models\Participation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;

class Driver extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'name',
        'surname',
        'nationality',
        'status'
    ];
    protected $appends = ['teams', 'races', 'wins', 'second_positions', 'third_positions', 'points'];

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    protected function teams(): Attribute
    {
        return Attribute::make(
            get: function () {
                $season = request('season', Race::orderBy('date', 'desc')->value(DB::raw("strftime('%Y', date)")));

                return $this->participations()
                    ->when($season !== 'all', function ($query) use ($season) {
                        $query->whereHas('race', function ($q) use ($season) {
                            $q->whereYear('date', $season);
                        });
                    })
                    ->get()
                    ->sortByDesc(function ($participation) {
                        return $participation->race->date;
                    })
                    ->pluck('team')
                    ->unique('id')
                    ->map(function ($team) {
                        if (!$team) {
                            return null;
                        }

                        return (object) [
                            'id' => $team->id,
                            'name' => $team->name,
                            'nationality' => $team->nationality
                        ];
                    })
                    ->values();
            },
        );
    }

    protected function races(): Attribute
    {
        return Attribute::make(
            get: function () {
                $season = request('season', Race::orderBy('date', 'desc')->value(DB::raw("strftime('%Y', date)")));

                return $this->participations()
                    ->toBase()
                    ->join('races', 'participations.race_id', '=', 'races.id')
                    ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
                    ->distinct('participations.race_id')
                    ->count('participations.race_id');
            },
        );
    }


    protected function wins(): Attribute
    {
        return Attribute::make(
            get: function () {
                $season = request('season', Race::orderBy('date', 'desc')->value(DB::raw("strftime('%Y', date)")));

                return $this->participations()
                    ->toBase()
                    ->join('races', 'participations.race_id', '=', 'races.id')
                    ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
                    ->where('position', 1)
                    ->distinct('race_id')
                    ->count('race_id');
            },
        );
    }

    protected function secondPositions(): Attribute
    {
        return Attribute::make(
            get: function () {
                $season = request('season', Race::orderBy('date', 'desc')->value(DB::raw("strftime('%Y', date)")));

                return $this->participations()
                    ->toBase()
                    ->join('races', 'participations.race_id', '=', 'races.id')
                    ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
                    ->where('position', 2)
                    ->distinct('race_id')
                    ->count('race_id');
            },
        );
    }

    protected function thirdPositions(): Attribute
    {
        return Attribute::make(
            get: function () {
                $season = request('season', Race::orderBy('date', 'desc')->value(DB::raw("strftime('%Y', date)")));

                return $this->participations()
                    ->toBase()
                    ->join('races', 'participations.race_id', '=', 'races.id')
                    ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
                    ->where('position', 3)
                    ->distinct('race_id')
                    ->count('race_id');
            },
        );
    }

    protected function points(): Attribute
    {
        return Attribute::make(
            get: function () {
                $season = request('season', Race::orderBy('date', 'desc')->value(DB::raw("strftime('%Y', date)")));
                $points = $this->participations()
                    ->select('participations.points')
                    ->join('races', 'participations.race_id', '=', 'races.id')
                    ->when($season !== 'all', fn($q) => $q->whereYear('races.date', $season))
                    ->orderBy('races.date', 'desc')
                    ->limit(1)
                    ->value('points') ?? null;

                return $points !== null ? number_format((float)$points, 3) : null;
            },
        );
    }
}
