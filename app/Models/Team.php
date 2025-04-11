<?php

namespace App\Models;

use App\Models\Participation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'name',
        'nationality',
    ];

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }
}
