<?php

namespace App\Models;

use App\Models\Participation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;

class Race extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'name',
        'date'
    ];
    protected $appends = ['winner', 'second', 'third', 'better'];

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    protected function winner(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->participations()
                ->where('position', 1)
                ->with([
                    'driver:id,name,surname,nationality',
                    'team:id,name,nationality'
                ])
                ->get()
                ->map(function ($participation) {
                    if (!$participation->driver) return null;

                    return (object) [
                        'id' => $participation->driver->id,
                        'name' => $participation->driver->name,
                        'surname' => $participation->driver->surname,
                        'nationality' => $participation->driver->nationality,
                        'team' => $participation->team ? (object) [
                            'id' => $participation->team->id,
                            'name' => $participation->team->name,
                            'nationality' => $participation->team->nationality
                        ] : null
                    ];
                })
                ->filter()
                ->first(),
        );
    }

    protected function second(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->participations()
                ->where('participations.position', 2)
                ->with('driver:id,name,surname,nationality')
                ->get()
                ->map(function ($participation) {
                    return $participation->driver ? (object) [
                        'id' => $participation->driver->id,
                        'name' => $participation->driver->name,
                        'surname' => $participation->driver->surname,
                        'nationality' => $participation->driver->nationality
                    ] : null;
                })
                ->filter()
                ->first(),
        );
    }

    protected function third(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->participations()
                ->where('participations.position', 3)
                ->with('driver:id,name,surname,nationality')
                ->get()
                ->map(function ($participation) {
                    return $participation->driver ? (object) [
                        'id' => $participation->driver->id,
                        'name' => $participation->driver->name,
                        'surname' => $participation->driver->surname,
                        'nationality' => $participation->driver->nationality
                    ] : null;
                })
                ->filter()
                ->first(),
        );
    }

    protected function better(): Attribute
    {
        return Attribute::make(
            get: function () {
                $season = request('season', Race::orderBy('date', 'desc')->value(DB::raw("strftime('%Y', date)")));

                return $this->participations()
                    ->when($season !== 'all', function ($query) use ($season) {
                        $query->whereHas('race', fn($q) => $q->whereYear('date', $season));
                    })
                    ->select([
                        'driver_id',
                        'team_id',
                        DB::raw('CASE 
                            WHEN EXISTS (
                                SELECT 1 FROM participations p2 
                                WHERE p2.driver_id = participations.driver_id 
                                AND p2.race_id < participations.race_id
                            ) THEN 
                                points - (
                                    SELECT p2.points 
                                    FROM participations p2 
                                    WHERE p2.driver_id = participations.driver_id 
                                    AND p2.race_id < participations.race_id 
                                    ORDER BY p2.race_id DESC 
                                    LIMIT 1
                                )
                            ELSE NULL
                        END AS points_diff')
                    ])
                    ->with([
                        'driver:id,name,surname,nationality',
                        'team:id,name,nationality'
                    ])
                    ->groupBy('driver_id', 'team_id', 'points_diff')
                    ->orderByDesc('points_diff')
                    ->get()
                    ->map(function ($participation) {
                        if (!$participation->points_diff) return null;

                        return (object) [
                            'id' => $participation->driver->id,
                            'name' => $participation->driver->name,
                            'surname' => $participation->driver->surname,
                            'nationality' => $participation->driver->nationality,
                            'team' => $participation->team ? (object) [
                                'id' => $participation->team->id,
                                'name' => $participation->team->name,
                                'nationality' => $participation->team->nationality
                            ] : null,
                            'points_diff' => $participation->points_diff
                        ];
                    })
                    ->filter()
                    ->first();
            },
        );
    }
}
