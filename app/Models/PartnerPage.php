<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PartnerPage extends Model
{
    use HasTranslations;

    protected $fillable = [
        'title',
        'body',
        'image_card1',
        'image_card2',
        'image_card3',
        'title_card1',
        'title_card2',
        'title_card3',
        'sub_title',
        'first_section_title',
        'first_section_sub_title',
        'join_sud_title',
        'description_card1',
        'description_card2',
        'description_card3',
        'join_title',
        'join_step_image1',
        'join_step_image2',
        'join_step_image3',
        'join_step_title1',
        'join_step_title2',
        'join_step_title3',
        'join_step_description1',
        'join_step_description2',
        'join_step_description3',
        'last_section_title',
        'last_section_description',
        'last_section_btn',
        'last_section_login_title',
        'last_section_login_sub_title',
        'meta_key',
        'meta_title',
        'meta_description',
    ];

    public $translatable = [
        'title',
        'body',
        'title_card1',
        'title_card2',
        'title_card3',
        'description_card1',
        'description_card2',
        'description_card3',
        'join_title',
        'join_step_title1',
        'join_step_title2',
        'join_step_title3',
        'join_step_description1',
        'join_step_description2',
        'join_step_description3',
        'last_section_title',
        'last_section_description',
        'last_section_btn',
        'last_section_login_title',
        'last_section_login_sub_title',
        'sub_title',
        'first_section_title',
        'first_section_sub_title',
        'join_sud_title',
        'meta_key',
        'meta_title',
        'meta_description',
    ];
}
