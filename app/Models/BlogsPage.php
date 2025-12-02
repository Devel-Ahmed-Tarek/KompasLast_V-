<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class BlogsPage extends Model
{
    use HasFactory, HasTranslations;
    protected $guarded      = [];
    protected $translatable = [
        'title',
        'sub_title',
        'slug',
        'description',
        'meta_key',
        'meta_title',
        'meta_description',
        'blog_categories',
    ];
}
