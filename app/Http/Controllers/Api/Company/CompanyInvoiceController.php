<?php

namespace App\Http\Controllers\Api\Company;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\CompanyInvoice;
use App\Models\ConfigApp;
use App\Services\CompanyInvoiceSyncService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyInvoiceController extends Controller
{
    public function __construct(private CompanyInvoiceSyncService $sync)
    {
    }

    public function index(Request $request)
    {
        $company = Auth::user();
        $this->sync->syncForCompany($company);

        $limit  = max(1, min(50, (int) $request->query('limit', 10)));
        $status = $request->query('status', 'all');

        $query = CompanyInvoice::where('company_id', $company->id)->orderByDesc('id');
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $page = $query->paginate($limit);

        return HelperFunc::sendResponse(200, 'OK', [
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

    public function show(int $id)
    {
        $invoice = $this->findOwned($id);
        if (! $invoice) {
            return HelperFunc::sendResponse(404, 'Invoice not found', []);
        }

        return HelperFunc::sendResponse(200, 'OK', $invoice->toApiArray());
    }

    public function pdf(int $id)
    {
        $invoice = $this->findOwned($id);
        if (! $invoice) {
            return HelperFunc::sendResponse(404, 'Invoice not found', []);
        }

        $company = Auth::user()->loadMissing('companyDetails');
        $config  = ConfigApp::first();

        $pdf = Pdf::loadView('pdf.company_invoice', [
            'invoice' => $invoice,
            'company' => $company,
            'config'  => $config,
        ]);

        return $pdf->download($invoice->number . '.pdf');
    }

    private function findOwned(int $id): ?CompanyInvoice
    {
        return CompanyInvoice::where('id', $id)
            ->where('company_id', Auth::id())
            ->first();
    }
}
