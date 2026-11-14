<?php

namespace App\Http\Controllers\Api\Company;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\CompanyCreditNote;
use App\Models\CompanyInvoice;
use App\Models\order as Order;
use App\Services\CompanyInvoiceSyncService;
use Illuminate\Support\Facades\Auth;

class CompanyFinanceController extends Controller
{
    public function __construct(private CompanyInvoiceSyncService $sync)
    {
    }

    public function overview()
    {
        $company = Auth::user()->loadMissing('wallet');
        $this->sync->syncForCompany($company);

        $wallet   = $company->wallet;
        $amount   = (float) ($wallet->amount ?? 0);
        $expense  = (float) ($wallet->expense ?? 0);
        $refunds  = (float) ($wallet->refund ?? 0);
        $available = $amount - $expense;

        $pendingCount    = Order::where('user_id', $company->id)->where('status', 'pending')->count();
        $confirmedTotal  = (float) Order::where('user_id', $company->id)->where('status', 'confirmed')->sum('amount');
        $cancelledCount  = Order::where('user_id', $company->id)->whereIn('status', ['canceled', 'cancelled'])->count();

        $invoiceCount = CompanyInvoice::where('company_id', $company->id)->count();
        $unpaidCount  = CompanyInvoice::where('company_id', $company->id)->where('status', 'pending')->count();

        $creditNotesTotal = (float) CompanyCreditNote::where('company_id', $company->id)
            ->where('status', 'completed')
            ->sum('amount');
        if ($creditNotesTotal <= 0) {
            $creditNotesTotal = $refunds;
        }

        $pendingRefunds = (float) CompanyCreditNote::where('company_id', $company->id)
            ->where('status', 'pending')
            ->sum('amount');

        return HelperFunc::sendResponse(200, 'OK', [
            'wallet' => [
                'total_credit'     => $amount,
                'expenses'         => $expense,
                'refunds'          => $refunds,
                'available_credit' => $available,
                'currency'         => 'EUR',
            ],
            'orders' => [
                'pending_count'    => $pendingCount,
                'confirmed_total'  => $confirmedTotal,
                'cancelled_count'  => $cancelledCount,
            ],
            'invoices' => [
                'count'        => $invoiceCount,
                'unpaid_count' => $unpaidCount,
            ],
            'credit_notes' => [
                'total'            => $creditNotesTotal,
                'pending_refunds'  => $pendingRefunds,
            ],
            'pending_banner' => $pendingCount > 0,
        ]);
    }
}
