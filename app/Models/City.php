<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['name', 'country_id', 'latitude', 'longitude'];

    public $translatable = ['name'];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function companies()
    {
        return $this->belongsToMany(User::class, 'company_cities')
            ->withPivot('radius_km')
            ->withTimestamps();
    }
}
