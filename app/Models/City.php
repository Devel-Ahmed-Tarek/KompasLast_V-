<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasFactory, HasTranslations;
    
    protected $fillable = ['name', 'country_id'];
    public $translatable = ['name']; // تحديد الحقول التي سيتم ترجمتها

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
