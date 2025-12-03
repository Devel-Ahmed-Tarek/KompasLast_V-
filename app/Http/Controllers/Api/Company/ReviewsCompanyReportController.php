<?php

namespace App\Http\Controllers\Api\Company;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\ReviewsCompanyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewsCompanyReportController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        // Validate the status filter
        if ($status && !in_array($status, ['open', 'testing', 'pending', 'confirmed', 'canceled'])) {
            return HelperFunc::sendResponse(422, 'Invalid status filter');
        }

        // Fetch reports for the logged-in user's reviews with optional status filter
        $reportsCompany = ReviewsCompanyReport::whereHas('reviewCompany', function ($query) {
            $query->where('user_id', auth()->user()->id); // Ensure the reports belong to the logged-in user
        })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status); // Apply status filter if provided
            })
            ->with('reviewCompany') // Include the related reviewCompany data
            ->paginate(10);

        // Return the paginated response using HelperFunc
        return HelperFunc::pagination($reportsCompany, $reportsCompany->items());
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
            $filePath = HelperFunc::uploadFile($request->file('file'), 'reports'); // Use the HelperFunc for file upload
            $data['file'] = $filePath;
        }

        // Create the report
        $report = ReviewsCompanyReport::create($data);
        return HelperFunc::sendResponse(201, 'Report created successfully', $report);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }

        $report = ReviewsCompanyReport::find($id);

        if (!$report) {
            return HelperFunc::sendResponse(404, 'Report not found');
        }
        // Check if the status is not 'pending'
        if ($report->status != 'pending') {
            return HelperFunc::sendResponse(403, 'You do not have permission to update this report');
        }

        $data = $request->all();

        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file if it exists
            HelperFunc::deleteFile($report->file);

            // Upload new file and update file path
            $data['file'] = HelperFunc::uploadFile('reports', $request->file('file'));
        }

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

        if ($report->reviewCompany->user_id != auth()->user()->id) {
            return HelperFunc::sendResponse(403, 'You do not have permission to access this resource.');
        }
        // Check if the status is not 'pending'
        if ($report->status != 'pending') {
            return HelperFunc::sendResponse(403, 'You do not have permission to deleted this report');
        }

        $report->delete();
        return HelperFunc::sendResponse(200, 'Report deleted successfully');
    }

}
