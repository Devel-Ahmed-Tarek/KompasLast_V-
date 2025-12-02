<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ServesBlogPage extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'title',
        'sub_title',
        'image',
        'description',
    ];

    protected $translatable = [
        'title',
        'sub_title',
        'description',
    ];
}
