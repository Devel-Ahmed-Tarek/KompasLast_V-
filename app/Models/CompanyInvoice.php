<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyInvoice extends Model
{
    protected $fillable = [
        'company_id',
        'number',
        'type',
        'title',
        'amount',
        'currency',
        'status',
        'paid_at',
        'order_id',
        'offer_id',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'amount'  => 'float',
        'paid_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function toApiArray(): array
    {
        return [
            'id'         => $this->id,
            'number'     => $this->number,
            'type'       => $this->type,
            'title'      => $this->title,
            'amount'     => $this->amount,
            'currency'   => $this->currency,
            'status'     => $this->status,
            'paid_at'    => optional($this->paid_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'related'    => [
                'order_id' => $this->order_id,
                'offer_id' => $this->offer_id,
            ],
            'pdf_url'    => url('/api/company/invoices/' . $this->id . '/pdf'),
        ];
    }
}
