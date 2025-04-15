<?php

namespace App\Models;

use App\Models\Participation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'name',
        'nationality',
        'status'
    ];

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }
}
