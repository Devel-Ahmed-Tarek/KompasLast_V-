<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ModelOffers extends Model
{
    use HasTranslations;
    protected $fillable = [
        'title',
        'description',
        'btn',
        'link',
        'img',
    ];

    protected $translatable = [
        'title',
        'description',
        'btn',
    ];
}
