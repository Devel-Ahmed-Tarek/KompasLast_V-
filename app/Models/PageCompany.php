<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PageCompany extends Model
{
    protected $table = 'page_comapnies';

    use HasFactory, HasTranslations;

    // Define the translatable fields
    public $translatable = [
        'title',
        'sub_title',
        'description',
        'form_title',
        'image_title',
        'information',
        'slug',
        'meta_key',
        'meta_description',
    ];

    // Specify other fields
    protected $fillable = [
        'image',
        'title',
        'sub_title',
        'description',
        'form_title',
        'image_title',
        'information',
        'slug',
        'meta_key',
        'meta_description',
    ];
}
