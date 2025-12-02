<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\ReviewsCompanyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminReviewsCompanyReportController extends Controller
{
    public function index(Request $request)
    {
        // Retrieve the 'status' query parameter
        $status = $request->query('status');

        // Base query
        $query = ReviewsCompanyReport::with('reviewCompany');

        // Apply filter if 'status' is provided
        if ($status) {
            if ($status && !in_array($status, ['open', 'testing', 'pending', 'confirmed', 'canceled'])) {
                return HelperFunc::sendResponse(422, 'Invalid status filter');
            }

            $query->where('status', $status);
        }

        // Paginate the results
        $reports = $query->paginate(10);

        // Return the paginated results
        return HelperFunc::pagination($reports, $reports->items());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reviews_company_id' => 'required|exists:reviews_company,id',
            'comment' => 'nullable|string',
            'file' => 'nullable|string',
            'status' => 'nullable|in:open,testing,pending,confirmed,canceled',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        $data = $request->all();

        // Handle file upload
        if ($request->hasFile('file')) {
            // HelperFunc::deleteFile($order->image);
            $data['file'] = HelperFunc::uploadFile('/reports', $request->file('file'));
        }

        // Create the report
        $report = ReviewsCompanyReport::create($data);
        return HelperFunc::sendResponse(201, 'Report created successfully', $report);
    }

    public function show($id)
    {
        $report = ReviewsCompanyReport::with('reviewCompany')->find($id);

        if (!$report) {
            return HelperFunc::sendResponse(404, 'Report not found');
        }

        return HelperFunc::sendResponse(200, 'Report retrieved successfully', $report);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpeg,png,pdf,docx|max:2048', // Validate file
            'status' => 'nullable|in:open,testing,pending,confirmed,canceled',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        $report = ReviewsCompanyReport::find($id);

        if (!$report) {
            return HelperFunc::sendResponse(404, 'Report not found');
        }

        $data = $request->all();

        // Handle file upload
        // if ($request->hasFile('file')) {
        //     // Delete old file if it exists
        //     HelperFunc::deleteFile($report->file);

        //     // Upload new file and update file path
        //     $data['file'] = HelperFunc::uploadFile('reports', $request->file('file'));
        // }

        // Update the report
        $report->update($data);

        return HelperFunc::sendResponse(200, 'Report updated successfully', $report);
    }

    public function destroy($id)
    {
        $report = ReviewsCompanyReport::find($id);

        if (!$report) {
            return HelperFunc::sendResponse(404, 'Report not found');
        }

        $report->delete();
        return HelperFunc::sendResponse(200, 'Report deleted successfully');
    }
}