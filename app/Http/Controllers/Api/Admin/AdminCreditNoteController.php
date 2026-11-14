<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\CompanyCreditNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminCreditNoteController extends Controller
{
    public function index(Request $request)
    {
        $limit = min(100, max(1, (int) $request->get('per_page', 20)));

        $query = CompanyCreditNote::with(['company:id,name,email'])
            ->orderByDesc('id');

        if ($request->filled('company_id')) {
            $query->where('company_id', (int) $request->company_id);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $page = $query->paginate($limit);

        $rows = collect($page->items())->map(function (CompanyCreditNote $note) {
            return array_merge($note->toApiArray(), [
                'company' => [
                    'id'    => $note->company?->id,
                    'name'  => $note->company?->name,
                    'email' => $note->company?->email,
                ],
            ]);
        })->values();

        return HelperFunc::pagination($page, $rows);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id'      => 'required|exists:users,id',
            'type'            => 'required|in:credit_note,refund,wallet_adjustment',
            'amount'          => 'required|numeric|gt:0',
            'currency'        => 'nullable|string|max:8',
            'status'          => 'nullable|in:pending,completed,rejected,cancelled',
            'reason'          => 'nullable|string|max:1000',
            'invoice_id'      => 'nullable|integer',
            'offer_id'        => 'nullable|integer|exists:offers,id',
            'order_id'        => 'nullable|integer|exists:orders,id',
            'apply_to_wallet' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors()->all());
        }

        $company = User::where('id', $request->company_id)->where('role', 'company')->first();
        if (! $company) {
            return HelperFunc::sendResponse(404, 'Company not found', []);
        }

        $status = $request->input('status', 'completed');
        $apply  = $request->boolean('apply_to_wallet', $status === 'completed');

        $note = DB::transaction(function () use ($request, $company, $status, $apply) {
            $note = CompanyCreditNote::create([
                'company_id'   => $company->id,
                'number'       => 'CN-TMP-' . uniqid(),
                'type'         => $request->input('type'),
                'amount'       => $request->input('amount'),
                'currency'     => strtoupper($request->input('currency', 'EUR')),
                'status'       => $status,
                'reason'       => $request->input('reason'),
                'invoice_id'   => $request->input('invoice_id'),
                'offer_id'     => $request->input('offer_id'),
                'order_id'     => $request->input('order_id'),
                'completed_at' => $status === 'completed' ? now() : null,
            ]);
            $note->number = sprintf('CN-%s-%04d', date('Y'), $note->id);
            $note->save();

            if ($apply && $status === 'completed') {
                $wallet = $company->wallet()->firstOrCreate(
                    ['user_id' => $company->id],
                    ['amount' => 0, 'expense' => 0, 'refund' => 0]
                );
                $wallet->refund = (float) $wallet->refund + (float) $note->amount;
                // Also restore spendable credit for refunds / adjustments.
                if (in_array($note->type, ['refund', 'wallet_adjustment', 'credit_note'], true)) {
                    $wallet->amount = (float) $wallet->amount + (float) $note->amount;
                }
                $wallet->save();
            }

            return $note;
        });

        return HelperFunc::sendResponse(201, 'Created', $note->toApiArray());
    }

    public function show(int $id)
    {
        $note = CompanyCreditNote::with(['company:id,name,email'])->find($id);
        if (! $note) {
            return HelperFunc::sendResponse(404, 'Credit note not found', []);
        }

        return HelperFunc::sendResponse(200, 'OK', array_merge($note->toApiArray(), [
            'company' => [
                'id'    => $note->company?->id,
                'name'  => $note->company?->name,
                'email' => $note->company?->email,
            ],
        ]));
    }
}
