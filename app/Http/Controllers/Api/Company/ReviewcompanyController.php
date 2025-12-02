<?php
namespace App\Http\Controllers\Api\Company;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\ReviewCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewcompanyController extends Controller
{

    public function index()
    {
        $id      = Auth::user()->id;
        $reviews = ReviewCompany::where('user_id', $id)->with('user')->paginate(10);
        return HelperFunc::pagination($reviews, $reviews->items());
    }

    public function overview(Request $request)
    {
        // Get the 'stars' value from the request if available
        $stars = $request->get('stars');

        // Get the current year if no year is provided
        $year = $request->get('year', date('Y'));

        // Get the company ID from the request (to filter by a specific company if necessary)
        $companyId = Auth::user()->id;

        // If a specific 'stars' value is provided, calculate based on that
        if ($stars) {
            // Filter by 'stars', 'year', and optionally 'company_id'
            $reviews = ReviewCompany::where('stars', $stars)
                ->whereYear('created_at', $year)
                ->when($companyId, function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })
                ->get();

            // Calculate the average rating for reviews with the specified stars in the specified year
            $averageRating = $reviews->avg('stars');

            // Count the total number of reviews with the specified stars in the specified year
            $totalRatings = $reviews->count();

            // Breakdown of ratings from 1 to 5 (only for the specified stars and year)
            $ratingBreakdown = [
                '1_star'  => $reviews->where('stars', 1)->count(),
                '2_stars' => $reviews->where('stars', 2)->count(),
                '3_stars' => $reviews->where('stars', 3)->count(),
                '4_stars' => $reviews->where('stars', 4)->count(),
                '5_stars' => $reviews->where('stars', 5)->count(),
            ];
        } else {
            // If no 'stars' are provided, calculate the overall average rating for the specified year
            $reviews = ReviewCompany::whereYear('created_at', $year)
                ->when($companyId, function ($query) use ($companyId) {
                    return $query->where('user_id', $companyId);
                })
                ->get();

            // Calculate the average rating for all reviews in the specified year
            $averageRating = $reviews->avg('stars');

            // Count the total number of reviews in the specified year
            $totalRatings = $reviews->count();

            // Breakdown of ratings from 1 to 5 for all reviews in the specified year
            $ratingBreakdown = [
                '1_star'  => $reviews->where('stars', 1)->count(),
                '2_stars' => $reviews->where('stars', 2)->count(),
                '3_stars' => $reviews->where('stars', 3)->count(),
                '4_stars' => $reviews->where('stars', 4)->count(),
                '5_stars' => $reviews->where('stars', 5)->count(),
            ];
        }

        // Return the response using HelperFunc
        return HelperFunc::sendResponse(200, 'Company ratings overview retrieved successfully', [
            'average_rating'   => $averageRating ?? 0,
            'total_ratings'    => $totalRatings,
            'rating_breakdown' => $ratingBreakdown,
            'year'             => $year,      // Return the year in the response
            'company_id'       => $companyId, // Return the company ID in the response
        ]);
    }

}
