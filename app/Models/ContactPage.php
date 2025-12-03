<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ContactPage extends Model
{
    use HasFactory, HasTranslations;

// Define the translatable fields
    public $translatable = [
        'title',
        'sub_title',
        'description',
        'form_title',
        'form_sub_title',
        'information',
        'meta_key',
        'meta_title',
        'meta_description',
    ];

// Specify other fields
    protected $fillable = [
        'image',
        'title',
        'sub_title',
        'description',
        'form_title',
        'form_sub_title',
        'information',
        'meta_key',
        'meta_title',
        'meta_description',
    ];
}
