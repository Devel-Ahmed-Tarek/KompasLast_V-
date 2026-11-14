<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyCreditNote extends Model
{
    protected $fillable = [
        'company_id',
        'number',
        'type',
        'amount',
        'currency',
        'status',
        'reason',
        'invoice_id',
        'offer_id',
        'order_id',
        'completed_at',
    ];

    protected $casts = [
        'amount'       => 'float',
        'completed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function toApiArray(): array
    {
        return [
            'id'           => $this->id,
            'number'       => $this->number,
            'type'         => $this->type,
            'amount'       => $this->amount,
            'currency'     => $this->currency,
            'status'       => $this->status,
            'reason'       => $this->reason,
            'related'      => [
                'invoice_id' => $this->invoice_id,
                'offer_id'   => $this->offer_id,
                'order_id'   => $this->order_id,
            ],
            'created_at'   => optional($this->created_at)->toIso8601String(),
            'completed_at' => optional($this->completed_at)->toIso8601String(),
            'pdf_url'      => null,
        ];
    }
}
