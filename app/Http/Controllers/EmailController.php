<?php
namespace App\Http\Controllers;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function sendEmailToCompany(Request $request)
    {
        // Step 1: Validate the request
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', // Ensure the user ID exists in the database
            'to'      => 'required|email',           // Recipient email (company email)
            'message' => 'required|string',          // Message body
        ]);

        // Step 2: Fetch user data based on ID
        $user = User::find($validated['user_id']);

        if (! $user) {
            return HelperFunc::apiResponse(false, 404, ['User not found.']);
        }

        try {
            // Step 3: Prepare email data
            $emailData = [
                'user_name'    => $user->name,
                'user_email'   => $validated['to'],
                'message_body' => $validated['message'],
            ];

            Mail::send('email.company_message', ['emailData' => $emailData], function ($message) use ($validated, $user) {
                $message->to($validated['to'])
                    ->subject("Message from User: {$user->name}");
            });

            // Step 5: Return success response
            return HelperFunc::apiResponse(true, 200, ['Email sent successfully to the company.']);

        } catch (\Exception $e) {
            // Step 6: Handle email sending failure
            return HelperFunc::apiResponse(false, 403, 'Failed to send email. ' . $e->getMessage(), );
        }
    }
}