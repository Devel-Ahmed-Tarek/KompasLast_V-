<?php

namespace App\Services;

use App\Models\CompanyInvoice;
use App\Models\order as Order;
use App\Models\User;

class CompanyInvoiceSyncService
{
    public function syncForCompany(User $company): void
    {
        $orders = Order::query()
            ->where('user_id', $company->id)
            ->orderBy('id')
            ->get();

        foreach ($orders as $order) {
            $status = match ($order->status) {
                'confirmed' => 'paid',
                'pending'   => 'pending',
                'canceled', 'cancelled' => 'cancelled',
                default     => 'pending',
            };

            $invoice = CompanyInvoice::firstOrNew([
                'source_type' => 'order',
                'source_id'   => $order->id,
            ]);

            if (! $invoice->exists) {
                $invoice->company_id = $company->id;
                $invoice->number     = sprintf('INV-%s-%04d', $order->created_at?->format('Y') ?: date('Y'), $order->id);
                $invoice->type       = 'credit_topup';
                $invoice->title      = 'Guthabenaufladung';
                $invoice->currency   = 'EUR';
                $invoice->order_id   = $order->id;
                $invoice->created_at = $order->created_at;
            }

            $invoice->amount  = (float) $order->amount;
            $invoice->status  = $status;
            $invoice->paid_at = $status === 'paid' ? ($order->updated_at ?: $order->created_at) : null;
            $invoice->save();
        }
    }
}
