<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Country extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['name'];
    public $translatable = ['name']; // تحديد الحقول التي سيتم ترجمتها

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function companies()
    {
        return $this->belongsToMany(User::class, 'company_countries');
    }
}
