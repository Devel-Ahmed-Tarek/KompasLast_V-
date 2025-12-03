<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Home extends Model
{
    use HasFactory, HasTranslations;
    protected $guarded      = [];
    protected $translatable = [
        'hero_title',
        'hero_sub_title',
        'hero_description',
        'hero_button',
        'services_title',
        'work_title',
        'work_name',
        'work_name2',
        'work_name3',
        'work_description',
        'work_description2',
        'work_description3',
        'review_form_title',
        'review_form_sub_title',
        'our_clients_pinions',
        'faq_title',
        'parteners_title',
        'parteners_sub_title',
        'parteners_description',
        'parteners_btn',
        'our_trusted_Companies',
        'what_clients_say_cabout',
        'meta_key',
        'meta_description',
        'meta_titel',
        'sub_our_trusted_Companies',
        'services_sub_title',
        'work_sub_title',
        'faq_sub_title',
        'our_clients_pinions_sub_title',
    ];

    public function faqImage(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? asset($value) : null
        );
    }
    public function heroImge(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? asset($value) : null
        );
    }

    public function workIcon(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? asset($value) : null
        );
    }
    public function workIcon2(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? asset($value) : null
        );
    }

    public function workIcon3(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? asset($value) : null
        );
    }
}
