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
}
