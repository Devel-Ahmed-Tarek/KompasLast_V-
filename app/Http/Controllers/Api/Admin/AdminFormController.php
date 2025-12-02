<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminFormController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Website Form Page Show', ['only' => ['index']]);
        $this->middleware('can:Website Form Page update', ['only' => ['update']]);

    }

    public function index(Request $request)
    {
        $data        = Form::first();
        $data->image = asset($data->image);
        return HelperFunc::sendResponse(200, 'Forms retrieved successfully', $data);
    }

    public function update(Request $request)
    {
        // Validate the request data
        $data = $request->validate([
            'header'                => 'nullable|array',
            'sub_title'             => 'nullable|array',
            'step1_title'           => 'nullable|array',
            'step2_title'           => 'nullable|array',
            'service'               => 'nullable|array',
            'name_last'             => 'nullable|array',
            'name_first'            => 'nullable|array',
            'email'                 => 'nullable|array',
            'phone_number'          => 'nullable|array',
            'current_location'      => 'nullable|array',
            'current_city'          => 'nullable|array',
            'current_rooms_number'  => 'nullable|array',
            'current_floor'         => 'nullable|array',
            'current_elevator'      => 'nullable|array',
            'new_location'          => 'nullable|array',
            'new_city'              => 'nullable|array',
            'new_rooms_number'      => 'nullable|array',
            'new_floor'             => 'nullable|array',
            'new_elevator'          => 'nullable|array',
            'date'                  => 'nullable|array',
            'offers_number'         => 'nullable|array',
            'other_details'         => 'nullable|array',
            'note'                  => 'nullable|array',
            'next_button'           => 'nullable|array',
            'submit_button'         => 'nullable|array',
            'success_message'       => 'nullable|array',
            'success_message_title' => 'nullable|array',
            'new_country'           => 'nullable|array',
            'new_zipcode'           => 'nullable|array',
            'current_country'       => 'nullable|array',
            'current_zipcode'       => 'nullable|array',
            'image'                 => 'nullable|file|image',
        ]);

        // Find the form by ID
        $form = Form::first();

        if (! $form) {
            return response()->json([
                'message' => 'Form not found',
            ], 404);
        }

        $form->fill($data);

        if ($request->hasFile('image')) {
            // HelperFunc::deleteFile($form->image);
            $path        = HelperFunc::uploadFile('homes', $request->file('image'));
            $form->image = $path;
        }

        // Save the form
        $form->save();

        return response()->json([
            'message' => 'Form updated successfully',
            'data'    => $form,
        ], 200);
    }
}
