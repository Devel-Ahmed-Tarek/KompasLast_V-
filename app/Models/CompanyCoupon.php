<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyCoupon extends Model
{
    use HasFactory;
    protected $table = 'company_coupons';

    protected $fillable = [
        'company_id',
        'coupon_id',
        'offer_id',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
