<?php

namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\FaqPageResource;
use App\Models\ConfigApp;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class FaqPageController extends Controller
{
    public function index(Request $request)
    {
        $language = $request->get('lang', 'en'); // Default to 'en' if no language is provided
        App::setLocale($language);
        $faq = Faq::select('question', 'answer')->get();

        return HelperFunc::sendResponse(200, 'done', new FaqPageResource($faq));
    }

    public function sendFaq(Request $request)
    {
        // Fetch the email address from the configuration
        $email = ConfigApp::first()->email2;

        // Validate the request data
        $validated = Validator::make($request->all(), [
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'question' => 'required|string',
        ])->validate();

        // Prepare email data
        $emailData = [
            'email' => $validated['email'],
            'name' => $validated['name'],
            'question' => $validated['question'],
        ];
        // Send the email
        try {
            Mail::send('email.spourt', ['emailData' => $emailData], function ($message) use ($validated, $email) {
                $message->to($email) // Company's email from configuration
                    ->subject("Message from User: {$validated['name']}");
            });
            return HelperFunc::apiResponse(200, 'Your question has been sent successfully!', []);
        } catch (\Exception $e) {
            // Handle email sending errors
            return HelperFunc::apiResponse(500, 'Failed to send your question. Please try again later.', $e);
        }
    }

}
