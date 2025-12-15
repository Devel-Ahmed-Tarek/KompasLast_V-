<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\ReviewSite;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AdminReviewSiteController extends Controller
{
    public function index(Request $request)
    {
        $reviews = ReviewSite::paginate($request->get('per_page', 10));
        return HelperFunc::pagination($reviews, $reviews->items());
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'stars' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        $review = ReviewSite::create($validatedData);

        return HelperFunc::sendResponse(201, 'Review added successfully', $review);
    }

    public function update(Request $request, $id)
    {
        try {
            $review = ReviewSite::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|max:255',
                'stars' => 'sometimes|required|integer|min:1|max:5',
                'comment' => 'sometimes|required|string',
                'status' => 'sometimes|boolean',
            ]);

            $review->update($validatedData);

            return HelperFunc::sendResponse(200, 'Review updated successfully', $review);
        } catch (ModelNotFoundException $e) {
            return HelperFunc::sendResponse(404, 'Review not found');
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred while updating the review: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $review = ReviewSite::findOrFail($id);

        $request->validate([
            'status' => 'required|boolean',
        ]);

        $review->update(['status' => $request->status]);

        return HelperFunc::sendResponse(200, 'Review status updated successfully', $review);
    }

    public function overview(Request $request)
    {
        // Get the 'stars' value from the request if available
        $stars = $request->get('stars');

        // Get the current year if no year is provided
        $year = $request->get('year', date('Y'));

        // If a specific 'stars' value is provided, calculate based on that
        if ($stars) {
            // Filter by 'stars' and 'year'
            $reviews = ReviewSite::where('stars', $stars)
                ->whereYear('created_at', $year)
                ->get();

            // Calculate the average rating for reviews with the specified stars in the specified year
            $averageRating = $reviews->avg('stars');

            // Count the total number of reviews with the specified stars in the specified year
            $totalRatings = $reviews->count();

            // Breakdown of ratings from 1 to 5 (only for the specified stars and year)
            $ratingBreakdown = [
                '1_star' => $reviews->where('stars', 1)->count(),
                '2_stars' => $reviews->where('stars', 2)->count(),
                '3_stars' => $reviews->where('stars', 3)->count(),
                '4_stars' => $reviews->where('stars', 4)->count(),
                '5_stars' => $reviews->where('stars', 5)->count(),
            ];
        } else {
            // If no 'stars' are provided, calculate the overall average rating for the specified year
            $reviews = ReviewSite::whereYear('created_at', $year)->get();

            // Calculate the average rating for all reviews in the specified year
            $averageRating = $reviews->avg('stars');

            // Count the total number of reviews in the specified year
            $totalRatings = $reviews->count();

            // Breakdown of ratings from 1 to 5 for all reviews in the specified year
            $ratingBreakdown = [
                '1_star' => $reviews->where('stars', 1)->count(),
                '2_stars' => $reviews->where('stars', 2)->count(),
                '3_stars' => $reviews->where('stars', 3)->count(),
                '4_stars' => $reviews->where('stars', 4)->count(),
                '5_stars' => $reviews->where('stars', 5)->count(),
            ];
        }

        // Return the response using HelperFunc
        return HelperFunc::sendResponse(200, 'Visitors rating overview retrieved successfully', [
            'average_rating' => $averageRating,
            'total_ratings' => $totalRatings,
            'rating_breakdown' => $ratingBreakdown,
            'year' => $year, // Return the year in the response
        ]);
    }

    public function destroy($id)
    {
        try {
            $review = ReviewSite::findOrFail($id);
            $review->delete();

            return HelperFunc::sendResponse(200, 'Review deleted successfully');
        } catch (ModelNotFoundException $e) {
            return HelperFunc::sendResponse(404, 'Review not found');
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred while deleting the review');
        }
    }
}
