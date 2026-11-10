<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OfferConversation extends Model
{
    protected $fillable = [
        'offer_id',
        'company_id',
        'last_message_at',
        'customer_last_read_at',
        'company_last_read_at',
    ];

    protected $casts = [
        'last_message_at'       => 'datetime',
        'customer_last_read_at' => 'datetime',
        'company_last_read_at'  => 'datetime',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OfferMessage::class, 'conversation_id')->orderBy('id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(OfferMessage::class, 'conversation_id')->latestOfMany();
    }
}
