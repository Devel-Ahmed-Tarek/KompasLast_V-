<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ServesPageForm extends Model
{
    use HasTranslations;
    protected $fillable = [
        'type_id',
        'image',
        'title',
        'slug',
        'body',
        'description',
        'meta_key',
        'meta_title',
        'meta_description',
    ];

    public $translatable = [
        'title',
        'body',
        'slug',
        'description',
        'meta_key',
        'meta_title',
        'meta_description',
    ];
    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }
}
