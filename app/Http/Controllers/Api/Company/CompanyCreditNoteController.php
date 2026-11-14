<?php

namespace App\Http\Controllers\Api\Company;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\CompanyCreditNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyCreditNoteController extends Controller
{
    public function index(Request $request)
    {
        $company = Auth::user()->loadMissing('wallet');
        $limit   = max(1, min(50, (int) $request->query('limit', 10)));
        $status  = $request->query('status', 'all');

        $query = CompanyCreditNote::where('company_id', $company->id)->orderByDesc('id');
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $page = $query->paginate($limit);

        $completedTotal = (float) CompanyCreditNote::where('company_id', $company->id)
            ->where('status', 'completed')
            ->sum('amount');

        // Fallback to wallet.refund when no credit-note rows exist yet.
        $walletRefund = (float) ($company->wallet->refund ?? 0);
        $totalCredit  = $completedTotal > 0 ? $completedTotal : $walletRefund;

        $pendingRefunds = (float) CompanyCreditNote::where('company_id', $company->id)
            ->where('status', 'pending')
            ->sum('amount');

        return HelperFunc::sendResponse(200, 'OK', [
            'summary' => [
                'total_credit_notes' => $totalCredit,
                'pending_refunds'    => $pendingRefunds,
                'currency'           => 'EUR',
            ],
            'rows'            => collect($page->items())->map->toApiArray()->values(),
            'paginationLinks' => [
                'currentPages' => $page->currentPage(),
                'perPage'      => $page->perPage(),
                'total'        => $page->total(),
                'links'        => [
                    'first' => $page->url(1),
                    'last'  => $page->url($page->lastPage()),
                ],
            ],
            'total'           => $page->total(),
        ]);
    }
}
