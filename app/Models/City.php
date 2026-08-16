<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasFactory, HasTranslations;

    public const TYPE_CITY = 'city';
    public const TYPE_MUNICIPALITY = 'municipality';
    public const TYPE_REGION = 'region';

    protected $fillable = [
        'name',
        'country_id',
        'state_id',
        'place_type',
        'ags_code',
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

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function companies()
    {
        return $this->belongsToMany(User::class, 'company_cities')
            ->withPivot('radius_km')
            ->withTimestamps();
    }

    public function scopeSelectable($query)
    {
        return $query->whereIn('place_type', [self::TYPE_CITY, self::TYPE_MUNICIPALITY]);
    }
}
