<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TypeQuestion extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'type_id',
        'question_text',
        'question_type',
        'is_required',
        'order',
        'parent_question_id',
        'parent_option_id',
    ];

    // الحقول القابلة للترجمة (5 لغات: en, de, fr, it, ar)
    public $translatable = [
        'question_text',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'order' => 'integer',
    ];

    // العلاقات
    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class, 'question_id')->orderBy('order');
    }

    public function parentQuestion()
    {
        return $this->belongsTo(TypeQuestion::class, 'parent_question_id');
    }

    public function childQuestions()
    {
        return $this->hasMany(TypeQuestion::class, 'parent_question_id')->orderBy('order');
    }

    public function answers()
    {
        return $this->hasMany(OfferAnswer::class, 'question_id');
    }
}
