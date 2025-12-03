<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TypeFeature extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'type_id',
        'image',
        'title',
        'description',
    ];

    public $translatable = ['title', 'description'];

    public function type()
    {
        return $this->belongsTo(Type::class);
    }
}
