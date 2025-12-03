<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Blog extends Model
{
    use HasFactory, HasTranslations;
    public $translatable = [
        'title',
        'meta_title',
        'short_description', 'body', 'key_key',
        'key_description', 'btn', 'slug',
    ];
    protected $fillable = [
        'type_id', 'title', 'main_image', 'image',
        'short_description', 'body', 'key_key', 'meta_title',
        'key_description', 'btn', 'slug', 'btn_hrf',
    ];
    protected $casts = [
        'image'      => 'array',
        'main_image' => 'array',
    ];

    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }
}
