<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferFakeReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminOfferFakeReportController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = OfferFakeReport::query();

        if ($status) {
            if (! in_array($status, ['open', 'testing', 'pending', 'confirmed', 'canceled'])) {
                return HelperFunc::sendResponse(422, 'Invalid status filter');
            }
            $query->where('status', $status)
                ->with('shopping_list.offer', 'shopping_list.company');
        }

        $reports = $query
            ->with('shopping_list.offer', 'shopping_list.company')

            ->paginate(10);

        return HelperFunc::pagination($reports, $reports->items());
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:open,testing,pending,confirmed,canceled',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors()->all());
        }

        $report = OfferFakeReport::with('shopping_list')->find($id);

        if (! $report) {
            return HelperFunc::sendResponse(404, 'Report not found');
        }

        DB::beginTransaction();

        try {
            $report->update($request->only('status'));

            if ($request->status === 'confirmed') {
                $shoppingList = $report->shopping_list;

                if (! $shoppingList) {
                    throw new \Exception('Shopping list not found for this report.');
                }

                $company = User::with('wallet')->findOrFail($shoppingList->user_id);
                $offer   = Offer::with('type')->findOrFail($shoppingList->offer_id);

                if (! $company->wallet) {
                    throw new \Exception('Company wallet not found.');
                }

                $numberOfOffers = $offer->Number_of_offers ?: 1;
                $price          = $offer->type->price / $numberOfOffers;

                $coupon = DB::table('client_coupons')
                    ->where('offer_id', $offer->id)
                    ->where('company_id', $company->id)
                    ->join('coupons', 'client_coupons.coupon_id', '=', 'coupons.id')
                    ->select('coupons.type', 'coupons.discount')
                    ->first();

                if ($coupon) {
                    if ($coupon->type === 'percentage') {
                        $price -= ($price * $coupon->discount / 100);
                    } elseif ($coupon->type === 'fixed') {
                        $price -= $coupon->discount;
                    }

                    $price = max($price, 0); // لا تسمح بسعر سلبي
                }

                $company->wallet->refund += $price;
                $company->wallet->save();
            }

            DB::commit();

            return HelperFunc::sendResponse(200, 'Report updated successfully', $report);
        } catch (\Exception $e) {
            DB::rollBack();
            return HelperFunc::sendResponse(500, 'An error occurred', ['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $report = OfferFakeReport::find($id);

        if (! $report) {
            return HelperFunc::sendResponse(404, 'Report not found');
        }

        $report->delete();

        return HelperFunc::sendResponse(200, 'Report deleted successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shopping_list_id' => 'required|exists:shopping_lists,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        $report = OfferFakeReport::create($request->all());

        return HelperFunc::sendResponse(201, 'Report created successfully', $report);
    }
}
