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
}
