<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferExecution extends Model
{
    protected $fillable = [
        "company_id",
        'offer_id',
        'is_executed',
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id', 'id');
    }
}
