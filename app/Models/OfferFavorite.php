<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class OfferFavorite extends Model
{
    use HasFactory;

    protected $table = 'offer_favorites';

    protected $fillable = [
        'user_id',
        'offer_id',
    ];

    /**
     * Get the company that owns this favorite
     */
    public function company()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the offer that is favorited
     */
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }


    /**
     * Check if an offer is in user's favorites
     */
    public static function isFavorite($offer_id)
    {
        if (!Auth::check()) {
            return false;
        }
        
        return self::where('user_id', Auth::id())
            ->where('offer_id', $offer_id)
            ->exists();
    }
}
