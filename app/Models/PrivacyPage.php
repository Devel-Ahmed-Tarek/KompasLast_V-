<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PrivacyPage extends Model
{
    use HasTranslations, HasFactory;

    protected $fillable  = ['title', 'body', 'meta_key', 'meta_description', 'meta_title'];
    public $translatable = ['title', 'body', 'meta_key', 'meta_description', 'meta_title'];

}
