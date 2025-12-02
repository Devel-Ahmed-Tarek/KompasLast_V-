<?php
namespace App\Http\Controllers\Api\Company;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyDachbouredController extends Controller
{
    public function index()
    {
        $company = Auth::user();

        // Fetch wallet details for the authenticated company
        $wallet = $company->wallet()->get();

        // Fetch shopping lists along with related offer and type
        $shoppingLists = $company->shopping_list()
            ->with(['offer.type']) // Load related offer and type
            ->get();

        // Group shopping lists by type ID and calculate counts
        $offerCountsByType = $shoppingLists->groupBy(function ($shoppingList) {
            return optional($shoppingList->offer->type)->id; // Group by type ID
        })->map(function ($group) {
            return [
                'type_name'   => optional($group->first()->offer->type)->name, // Type name
                'offer_count' => $group->count(),                              // Count of offers
            ];
        })->values(); // Reindex keys for cleaner JSON output

        // Get the last 10 offers with selected fields
        $lastOffer = $shoppingLists->take(10)->map(function ($shoppingList) {
            $offer = $shoppingList->offer;
            return $offer ? [
                'id'         => $offer->id,
                'created_at' => $offer->created_at,
                'date'       => $offer->date,
                'type'       => $offer->type,
            ] : null;
        })->filter(); // Remove null values if any shopping list lacks an offer

        // Return the response using a helper function
        return HelperFunc::sendResponse(200, 'success', [
            'offerCountsByType' => $offerCountsByType,
            'wallet'            => $wallet,
            'lastOffer'         => $lastOffer,
        ]);
    }

    public function calendar(Request $request)
    {
        $company = Auth::user();

                                                                 // Default year and day to the current year and day if not provided
        $year  = $request->input('year', Carbon::now()->year);   // Current year
        $month = $request->input('month', Carbon::now()->month); // Current month

        // Fetch shopping lists filtered by year and month for offers
        $shoppingLists = $company->shopping_list()
            ->whereHas('offer', function ($query) use ($year, $month) {
                $query->whereYear('date', $year) // Filter by year
                    ->whereMonth('date', $month);    // Filter by day
            })
            ->with(['offer:id,date,name']) // Load the related offer
            ->select('id', 'offer_id')
            ->get();

        return HelperFunc::sendResponse(200, 'success', $shoppingLists);
    }

}
