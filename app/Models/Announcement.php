<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'comment',
        'offer_id',
    ];
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
