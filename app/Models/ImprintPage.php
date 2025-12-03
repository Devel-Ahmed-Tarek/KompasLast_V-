<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ImprintPage extends Model
{
    use HasFactory, HasTranslations;
    protected $guarded      = [];
    protected $translatable = [
        'title',
        'sub_title',
        'body',
        'meta_key',
        'meta_title',
        'meta_description',
    ];

}
