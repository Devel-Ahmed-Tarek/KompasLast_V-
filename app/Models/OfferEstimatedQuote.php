<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferEstimatedQuote extends Model
{
    protected $fillable = [
        'offer_id',
        'company_id',
        'mode',
        'amount',
        'amount_from',
        'amount_to',
        'currency',
        'note',
        'conversation_id',
        'message_id',
    ];

    protected $casts = [
        'amount'      => 'float',
        'amount_from' => 'float',
        'amount_to'   => 'float',
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function formatMessageBody(): string
    {
        $lines = ['Kostenschätzung (unverbindlich)'];

        if ($this->mode === 'range') {
            $lines[] = $this->money($this->amount_from) . ' – ' . $this->money($this->amount_to);
        } else {
            $lines[] = $this->money($this->amount);
        }

        if ($this->note) {
            $lines[] = 'Notiz: ' . $this->note;
        }

        return implode("\n", $lines);
    }

    public function toApiArray(): array
    {
        return [
            'id'          => $this->id,
            'offer_id'    => $this->offer_id,
            'mode'        => $this->mode,
            'amount'      => $this->amount,
            'amount_from' => $this->amount_from,
            'amount_to'   => $this->amount_to,
            'note'        => $this->note,
            'currency'    => $this->currency,
            'created_at'  => optional($this->created_at)->toIso8601String(),
        ];
    }

    private function money(?float $value): string
    {
        return number_format((float) $value, 2, ',', '.') . ' €';
    }
}
