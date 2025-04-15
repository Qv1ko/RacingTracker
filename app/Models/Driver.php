<?php

namespace App\Models;

use App\Models\Participation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'name',
        'surname',
        'nationality',
        'status'
    ];

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->name . ' ' . $this->surname;
            }
        );
    }

    public function sortName(): Attribute
    {
        return Attribute::make(
            get: function () {
                return substr($this->name, 0, 1) . '. ' . $this->surname;
            }
        );
    }

    public function seasonTeams(): Attribute
    {
        return Attribute::make(
            get: function ($season) {
                // return $this->participations()->where('season', '==', $season)->get();
            }
        );
    }
}
