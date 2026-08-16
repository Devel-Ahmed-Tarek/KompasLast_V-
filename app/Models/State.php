<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class State extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'country_id',
        'name',
        'code',
        'latitude',
        'longitude',
    ];

    public $translatable = ['name'];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
