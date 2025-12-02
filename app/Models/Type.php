<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Type extends Model
{
    use HasFactory, HasTranslations;
    protected $fillable  = ['name', 'price'];
    public $translatable = ['name']; // تحديد الحقول التي سيتم ترجمتها

    public function company()
    {
        return $this->belongsToMany(User::class, 'type_user');
    }

    public function typeDitaliServices()
    {
        return $this->hasOne(TypeDitaliServices::class, 'type_id');
    }
    public function typeDitaliServesPageForm()
    {
        return $this->hasOne(ServesPageForm::class, 'type_id');
    }

    public function TypeFeature()
    {
        return $this->hasMany(TypeFeature::class);
    }
    public function TypeTips()
    {
        return $this->hasMany(TypeTips::class);
    }
    public function blogs()
    {
        return $this->hasMany(Blog::class, 'type_id', 'id');
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    // علاقة الأسئلة
    public function questions()
    {
        return $this->hasMany(TypeQuestion::class)->orderBy('order');
    }

    // الأسئلة الرئيسية فقط (بدون branching)
    public function mainQuestions()
    {
        return $this->hasMany(TypeQuestion::class)
            ->whereNull('parent_question_id')
            ->orderBy('order');
    }

}
