<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TypeDitaliServices extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'type_id',
        'title',
        'main_image',
        'service_home_icon',
        'small_image',
        'short_description',
        'feature_header',
        'feature_sub_title',
        'feature_in_service',
        'body',
        'tips_title',
        'tips_subtitle',
        'tip_in_service',
        'meta_keys',
        'meta_title',
        'slug',
        'meta_description',
        'blog_meta_description',
        'blog_meta_keys',
        'blog_meta_title',
    ];
    public $translatable = [
        'title',
        'short_description',
        'feature_header',
        'feature_sub_title',
        'feature_in_service',
        'body',
        'tips_title',
        'tips_subtitle',
        'tip_in_service',
        'slug',
        'meta_keys',
        'meta_title',
        'meta_description',
        'blog_meta_description',
        'blog_meta_keys',
        'blog_meta_title',
    ]; // تحديد الحقول التي سيتم ترجمتها
    protected $casts = [
        'main_image'  => 'array',
        'small_image' => 'array',

    ]; // تحديد الحقول التي سيتم ترجمتها

    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }
}
