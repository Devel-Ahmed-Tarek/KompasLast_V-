<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;
    protected $fillable = [

        'type_id',
        'country_id',
        'city_id',
        'anrede',
        'name',
        'email',
        'phone',
        'date',
        'adresse',
        'ort',
        'zimmer',
        'etage',
        'vorhanden',
        'Nach_Adresse',
        'Nach_Ort',
        'Nach_Zimmer',
        'Nach_Etage',
        'Nach_vorhanden',
        'count',
        'Number_of_offers',
        'cheek',
        'lang',
        'ip',
        'country',
        'city',
        'Besonderheiten',
        'carrent_country',
        'zipcode',
        'Nach_country',
        'Nach_zipcode',
        'completion_status',
        'confirm_status',
        'confirm_token',
        'confirmed_at',
    ];

    protected $casts = [
        'completion_status' => 'string',
    ];

    // داخل نموذج Offer
    public function Shopping_list()
    {
        return $this->hasMany(Shopping_list::class, 'offer_id');
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function coupons()
    {
        return $this->hasMany(CompanyCoupon::class, 'offer_id');
    }

    // علاقات الأسئلة والإجابات
    public function answers()
    {
        return $this->hasMany(OfferAnswer::class);
    }

    public function countryRelation()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function cityRelation()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    // Accessor لحساب حالة الإكمال
    public function getCompletionStatusAttribute($value)
    {
        // لو القيمة موجودة في قاعدة البيانات، نرجعها
        if ($value) {
            return $value;
        }

        // لو مش موجودة، نحسبها
        $totalQuestions = $this->type->mainQuestions()->count();
        $answeredQuestions = $this->answers()->count();

        if ($answeredQuestions == 0) {
            return 'draft';
        } elseif ($answeredQuestions < $totalQuestions) {
            return 'in_progress';
        } else {
            return 'completed';
        }
    }
}
