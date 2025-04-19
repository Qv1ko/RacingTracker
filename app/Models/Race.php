<?php

namespace App\Models;

use App\Models\Participation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Race extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'name',
        'date'
    ];

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function winner()
    {
        return $this->hasOneThrough(
            Driver::class,
            Participation::class,
            'race_id',
            'id',
            'id',
            'driver_id'
        )->where('participations.position', 1);
    }

    public function second()
    {
        return $this->hasOneThrough(
            Driver::class,
            Participation::class,
            'race_id',
            'id',
            'id',
            'driver_id'
        )->where('participations.position', 2);
    }

    public function third()
    {
        return $this->hasOneThrough(
            Driver::class,
            Participation::class,
            'race_id',
            'id',
            'id',
            'driver_id'
        )->where('participations.position', 3);
    }

    public function better()
    {
        return $this->hasOneThrough(
            Driver::class,
            Participation::class,
            'race_id',
            'id',
            'id',
            'driver_id'
        )
            ->selectRaw('drivers.*, 
            (participations.points - COALESCE(
                (SELECT points 
                FROM participations p2 
                WHERE p2.driver_id = participations.driver_id 
                AND p2.race_id < participations.race_id 
                ORDER BY p2.race_id DESC 
                LIMIT 1), 0
            )) AS points_diff')
            ->orderBy('points_diff', 'asc')
            ->limit(1);
    }
}
