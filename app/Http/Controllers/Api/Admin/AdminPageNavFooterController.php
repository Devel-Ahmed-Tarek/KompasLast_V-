<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Models\NavFooter;use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class AdminPageNavFooterController extends BaseController
{
    public function __construct()
    {
        $this->middleware('can:Website Nav And Footer Page Show', ['only' => ['index']]);
        $this->middleware('can:Website Nav And Footer Page update', ['only' => ['update']]);

    }

    public function index()
    {
        $data = NavFooter::first();
        return HelperFunc::sendResponse(200, 'done', $data);

    }

    public function update(Request $request)
    {
        try {

            // Fetch the first record in the NavFooter table
            $data = NavFooter::first();

            // Define the list of allowed fields
            $allowedFields = [
                'button', 'contactUs', 'label_input', 'imprint', 'compalins',
                'faqs', 'blogs', 'aboutUs', 'services', 'home', 'loin_btn',
            ];

            // Loop through the allowed fields and update only those present in the request
            foreach ($allowedFields as $field) {
                if ($request->has($field)) {
                    $data->$field = $request->$field;
                }
            }

            // Save the changes
            $data->save();

            // Return success response
            return HelperFunc::sendResponse(200, 'Update successful', []);
        } catch (\Exception $e) {
            \Log::error('Update NavFooter Error: ', ['error' => $e->getMessage()]);
            return HelperFunc::sendResponse(500, 'An error occurred while updating', ['error' => $e->getMessage()]);
        }
    }

}
