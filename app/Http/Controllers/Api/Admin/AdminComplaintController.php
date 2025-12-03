<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminComplaintController extends Controller
{
    public function index(Request $request)
    {
        $complaints = Complaint::orderBy('created_at', 'desc')->paginate($request->get('per_page', 10));

        return HelperFunc::pagination($complaints, $complaints->items());
    }

    public function updateStatus(Request $request, $id)
    {
        // Validate input
        $request->validate([
            'status' => 'required',
        ]);

        // Find the complaint
        $complaint = Complaint::findOrFail($id);

        // Update status
        $complaint->update(['status' => $request->status]);

                                        // Prepare data for emails
        $userEmail = $complaint->email; // Email of the user

        $supportEmail = 'support@compass.com'; // Support email address

        if ($request->status == 'approved') {

            // Send email to support for approval
            Mail::to($supportEmail)->send(new \App\Mail\SupportNotification($complaint, 'approved'));

        } elseif ($request->status == 'rejected') {

            // Send email to support for rejection
            Mail::to($supportEmail)->send(new \App\Mail\SupportNotification($complaint, 'rejected'));

            // Send apology email to the user
            Mail::to($userEmail)->send(new \App\Mail\UserApology($complaint));
        }

        return HelperFunc::sendResponse(200, "Complaint status updated to {$request->status} successfully");
    }
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [

            'user_name' => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'phone'     => 'required|string|max:15',
            'complain'  => 'required|string|max:500',
        ]);
        $validatedData = $validator->validate();
        try {
            // Store the complaint in the database
            $complaint = Complaint::create([
                'user_name' => $validatedData['user_name'],
                'email'     => $validatedData['email'],
                'phone'     => $validatedData['phone'],
                'complain'  => $validatedData['complain'],
            ]);

            if ($validator->fails()) {
                return HelperFunc::sendResponse(422, 'Validation Error', [
                    'errors' => $validator->errors(),
                ]);
            }
            // Return success response
            return HelperFunc::sendResponse(201, 'Complaint submitted successfully.', $complaint);
        } catch (\Exception $e) {
            // Handle errors and return failure response
            return HelperFunc::apiResponse(false, 500, 'Failed to submit complaint. ' . $e->getMessage());
        }
    }

}
