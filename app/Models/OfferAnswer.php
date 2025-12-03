<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id',
        'question_id',
        'answer_text', // إجابة المستخدم (غير قابلة للترجمة)
    ];

    // العلاقات
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function question()
    {
        return $this->belongsTo(TypeQuestion::class, 'question_id');
    }

    public function options()
    {
        return $this->belongsToMany(QuestionOption::class, 'offer_answer_options', 'offer_answer_id', 'option_id')
            ->withTimestamps();
    }
}
