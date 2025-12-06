<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TypeTips extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['type_id', 'name'];

    public $translatable = ['name']; // الحقول متعددة اللغات

    public function media()
    {
        return $this->hasMany(TypeTipsMedia::class, 'type_tip_id');
    }

    /**
     * جلب الصور لحقل معين و لغة معينة
     */
    public function getMediaByField($fieldName, $language = null)
    {
        $query = $this->media()->where('field_name', $fieldName);
        
        if ($language) {
            $query->where('language', $language);
        }
        
        return $query->orderBy('order')->get();
    }
}
