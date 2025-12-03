<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\ReviewCompany;
use Illuminate\Http\Request;

class AdminReviewcompanyController extends Controller
{
    public function index()
    {
        $reviews = ReviewCompany::with('user')->paginate(10);
        return HelperFunc::pagination($reviews, $reviews->items());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'stars' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $review = ReviewCompany::create($request->all());

        return HelperFunc::sendResponse(201, 'Review added successfully', $review);
    }

    public function show($id)
    {
        $review = ReviewCompany::with('user')->findOrFail($id);
        return HelperFunc::sendResponse(200, 'Review details', $review);
    }

    public function update(Request $request, $id)
    {
        $review = ReviewCompany::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'stars' => 'sometimes|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        $review->update($request->all());

        return HelperFunc::sendResponse(200, 'Review updated successfully', $review);
    }

    public function destroy($id)
    {
        $review = ReviewCompany::findOrFail($id);
        $review->delete();

        return HelperFunc::sendResponse(200, 'Review deleted successfully');
    }
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|boolean', // يجب أن تكون القيمة Boolean (true أو false)
        ]);
        // التحقق من أن المراجعة موجودة
        $review = ReviewCompany::findOrFail($id);

        // التحقق من صحة البيانات الواردة

        // تحديث الحالة
        $review->update([
            'status' => $request->status,
        ]);

        // إرجاع استجابة
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
            $reviews = ReviewCompany::where('stars', $stars)
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
            $reviews = ReviewCompany::whereYear('created_at', $year)
                ->get();

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
        return HelperFunc::sendResponse(200, 'Company ratings overview retrieved successfully', [
            'average_rating' => $averageRating,
            'total_ratings' => $totalRatings,
            'rating_breakdown' => $ratingBreakdown,
            'year' => $year, // Return the year in the response
        ]);
    }

}
