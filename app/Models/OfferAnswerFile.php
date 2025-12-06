<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferAnswerFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_answer_id',
        'file_path',
        'file_name',
        'file_type',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // العلاقات
    public function offerAnswer()
    {
        return $this->belongsTo(OfferAnswer::class);
    }

    // Accessor للحصول على رابط الملف
    public function getFileUrlAttribute()
    {
        return asset($this->file_path);
    }
}

