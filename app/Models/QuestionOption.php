<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class QuestionOption extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'question_id',
        'option_text',
        'order',
    ];

    // الحقول القابلة للترجمة (5 لغات: en, de, fr, it, ar)
    public $translatable = [
        'option_text',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    // العلاقات
    public function question()
    {
        return $this->belongsTo(TypeQuestion::class, 'question_id');
    }

    public function answers()
    {
        return $this->belongsToMany(OfferAnswer::class, 'offer_answer_options', 'option_id', 'offer_answer_id')
            ->withTimestamps();
    }
}
