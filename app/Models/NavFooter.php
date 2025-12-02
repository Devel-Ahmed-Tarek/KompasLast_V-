<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class NavFooter extends Model
{
    use HasFactory, HasTranslations;
    protected $guarded      = [];
    protected $translatable = [
        'button',
        'loin_btn',
        'contactUs',
        'label_input',
        'imprint',
        'compalins',
        'faqs',
        'blogs',
        'aboutUs',
        'services',
        'home',
    ];

}