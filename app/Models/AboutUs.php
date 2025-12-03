<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class AboutUs extends Model
{
    protected $table = 'about_us';
    use HasFactory, HasTranslations;

    protected $fillable = [
        'title', 'hero_image', 'hero_title', 'hero_decsription',
        'why_title', 'why_sub_title', 'why_name', 'why_name2', 'why_name3',
        'why_imge', 'why_imge2', 'why_imge3', 'why_decsription',
        'why_decsription2', 'why_decsription3', 'target_title',
        'target_imge', 'target_body', 'informaion_customer_cont',
        'informaion_company_cont', 'informaion_offer_cont',
        'informaion_company_decsription', 'informaion_offer_decsription',
        'informaion_customer_decsription', 'informaion_company_name',
        'informaion_offer_name', 'informaion_customer_name', 'meta_key',
        'meta_description',
        'meta_title',
    ];

    protected $translatable = [
        'title', 'hero_title', 'hero_decsription',
        'why_title', 'why_sub_title', 'why_name', 'why_name2', 'why_name3',
        'why_decsription',
        'why_decsription2', 'why_decsription3', 'target_title',
        'target_body',
        'informaion_company_decsription', 'informaion_offer_decsription',
        'informaion_customer_decsription', 'informaion_company_name',
        'informaion_offer_name', 'informaion_customer_name', 'meta_key',
        'meta_description',
        'meta_title',
    ];
}
