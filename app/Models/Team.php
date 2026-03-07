<?php

namespace App\Models;

use App\Services\TeamStatsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nationality',
        'status',
    ];

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'participations');
    }

    public function races(): BelongsToMany
    {
        return $this->belongsToMany(Race::class, 'participations');
    }

    public function stats(): TeamStatsService
    {
        return new TeamStatsService($this);
    }
}
