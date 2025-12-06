<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TypeServiceMedia extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'type_ditali_service_id',
        'field_name',
        'language',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'metadata',
        'order',
    ];

    public $translatable = []; // metadata يمكن أن يكون مترجم إذا أردت

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
        'order' => 'integer',
    ];

    public function service()
    {
        return $this->belongsTo(TypeDitaliServices::class, 'type_ditali_service_id');
    }
}

