<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Type extends Model
{
    use HasFactory, HasTranslations;
    protected $fillable  = ['name', 'price', 'parent_id', 'order', 'is_active'];
    public $translatable = ['name']; // تحديد الحقول التي سيتم ترجمتها

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the parent type (if this is a subtype)
     */
    public function parent()
    {
        return $this->belongsTo(Type::class, 'parent_id');
    }

    /**
     * Get the children types (subtypes)
     */
    public function children()
    {
        return $this->hasMany(Type::class, 'parent_id')->orderBy('order');
    }

    /**
     * Check if this type is a parent (has no parent_id)
     */
    public function isParent()
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this type is a child (has parent_id)
     */
    public function isChild()
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get all parent types only
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get all child types only
     */
    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Get active types only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

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
