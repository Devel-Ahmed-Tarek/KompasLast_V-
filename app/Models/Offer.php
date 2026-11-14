<?php

namespace App\Models;

use App\Exceptions\CustomerAccountRequiredException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Offer extends Model
{
    use HasFactory;
    protected $fillable = [

        'type_id',
        'country_id',
        'city_id',
        'latitude',
        'longitude',
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
        'unit_price',
        'status',
        'confirm_status',
        'confirm_token',
        'track_token',
        'confirmed_at',
        'user_id',
    ];

    protected $casts = [
        'completion_status' => 'string',
        'unit_price'        => 'float',
        'latitude'          => 'float',
        'longitude'         => 'float',
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

    public function user()
    {
        return $this->belongsTo(User::class);
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

    /**
     * Resolve offer by track_token, confirm_token, or customer email (latest).
     */
    public static function findByTrackIdentifier(string $token, array $with = []): ?self
    {
        $token = trim(rawurldecode($token));
        if ($token === '') {
            return null;
        }

        $query = static::query()->with($with);

        $offer = (clone $query)
            ->where(function ($q) use ($token) {
                $q->where('track_token', $token)
                    ->orWhere('confirm_token', $token);
            })
            ->first();

        if ($offer) {
            return $offer;
        }

        if (! filter_var($token, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $email = mb_strtolower($token);

        // If this email has a customer account, email-only tracking is blocked.
        // They must log in; secret track_token links still work above.
        $hasCustomerAccount = User::whereRaw('LOWER(email) = ?', [$email])
            ->where('role', 'user')
            ->exists();

        if ($hasCustomerAccount) {
            throw new CustomerAccountRequiredException($email);
        }

        $offer = $query
            ->whereRaw('LOWER(email) = ?', [$email])
            ->orderByDesc('id')
            ->first();

        if ($offer && ! $offer->track_token) {
            $offer->track_token = Str::random(64);
            $offer->save();
        }

        return $offer;
    }
}
