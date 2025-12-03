<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class FaqPage extends Model
{
    use HasFactory, HasTranslations;
    protected $guarded = [];

    protected $translatable = [
        'title',
        'sub_title',
        'form_title',
        'form_sub_title',
        'meta_key',
        'meta_title',
        'meta_description',
    ];
}
