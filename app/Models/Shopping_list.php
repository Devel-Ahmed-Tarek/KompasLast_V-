<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shopping_list extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fakeOffer()
    {
        return $this->hasOne(OfferFakeReport::class, 'shopping_list_id');
    }

}
