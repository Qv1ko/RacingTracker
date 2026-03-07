<?php

namespace App\Models;

use App\Services\DriverStatsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'surname',
        'nationality',
        'status',
    ];

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'participations');
    }

    public function races(): BelongsToMany
    {
        return $this->belongsToMany(Race::class, 'participations');
    }

    public function stats(): DriverStatsService
    {
        return new DriverStatsService($this);
    }
}
