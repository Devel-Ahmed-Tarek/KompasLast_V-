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
        'show_before_purchase',
        'allows_file_upload',
        'allowed_file_types',
        'max_files',
        'max_file_size',
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
        'show_before_purchase' => 'boolean',
        'allows_file_upload' => 'boolean',
        'order' => 'integer',
        'max_files' => 'integer',
        'max_file_size' => 'integer',
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
