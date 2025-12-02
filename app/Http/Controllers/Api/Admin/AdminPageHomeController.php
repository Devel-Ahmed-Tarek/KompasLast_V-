<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Models\Home;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminPageHomeController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:Website home Page Show', ['only' => ['index']]);
        $this->middleware('can:Website home Page update', ['only' => ['update']]);

    }

    public function index()
    {
        $data = Home::first();
        return HelperFunc::sendResponse(200, 'done', $data);
    }

    /**
     * Update home data with image upload
     */
    public function update(Request $request)
    {
        // Find the home by ID
        $home = Home::first();

        // If home not found, return an error response
        if (! $home) {
            return HelperFunc::apiResponse(false, 404, ['error' => 'Home not found']);
        }

        // Validate incoming request
        $validatedData = $request->validate([
            'hero_imge'                     => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'hero_title'                    => 'nullable|array',
            'hero_sub_title'                => 'nullable|array',
            'hero_description'              => 'nullable|array',
            'hero_button'                   => 'nullable|array',
            'services_title'                => 'nullable|array',
            'services_sub_title'            => 'nullable|array',
            'work_sub_title'                => 'nullable|array',
            'faq_sub_title'                 => 'nullable|array',
            'our_clients_pinions_sub_title' => 'nullable|array',
            'work_title'                    => 'nullable|array',
            'work_name'                     => 'nullable|array',
            'work_name2'                    => 'nullable|array',
            'work_name3'                    => 'nullable|array',
            'work_icon'                     => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'work_icon2'                    => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'work_icon3'                    => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'work_description'              => 'nullable|array',
            'work_description2'             => 'nullable|array',
            'work_description3'             => 'nullable|array',
            'review_form_title'             => 'nullable|array',
            'review_form_sub_title'         => 'nullable|array',
            'our_clients_pinions'           => 'nullable|array',
            'faq_title'                     => 'nullable|array',
            'faq_image'                     => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'our_trusted_Companies'         => 'nullable|array',
            'sub_our_trusted_Companies'     => 'nullable|array',
            'what_clients_say_cabout'       => 'nullable|array',
            'slug'                          => 'nullable|array',
            'meta_key'                      => 'nullable|array',
            'meta_titel'                    => 'nullable|array',
            'meta_description'              => 'nullable|array',
        ]);

        // return request();
        // Handle image upload if file is present
        if ($request->hasFile('hero_imge')) {
            // Upload the file and get the file path
            $path            = HelperFunc::uploadFile('homes', $request->file('hero_imge'));
            $home->hero_imge = $path; // Store the file path
        }
        if ($request->hasFile('faq_image')) {
            // Upload the file and get the file path
            $path            = HelperFunc::uploadFile('homes', $request->file('faq_image'));
            $home->faq_image = $path; // Store the file path
        }
        if ($request->hasFile('work_icon')) {
            // Upload the file and get the file path
            $path            = HelperFunc::uploadFile('homes', $request->file('work_icon'));
            $home->work_icon = $path; // Store the file path
        }
        if ($request->hasFile('work_icon2')) {
            // Upload the file and get the file path
            $path             = HelperFunc::uploadFile('homes', $request->file('work_icon2'));
            $home->work_icon2 = $path; // Store the file path
        }
        if ($request->hasFile('work_icon3')) {
            // Upload the file and get the file path
            $path             = HelperFunc::uploadFile('homes', $request->file('work_icon3'));
            $home->work_icon3 = $path; // Store the file path
        }

        // Update the other fields
        $home->hero_title       = $request->input('hero_title', $home->hero_title);
        $home->hero_sub_title   = $request->input('hero_sub_title', $home->hero_sub_title);
        $home->hero_description = $request->input('hero_description', $home->hero_description);
        $home->hero_button      = $request->input('hero_button', $home->hero_button);
        $home->services_title   = $request->input('services_title', $home->services_title);
        $home->work_title       = $request->input('work_title', $home->work_title);
        $home->work_name        = $request->input('work_name', $home->work_name);
        $home->work_name2       = $request->input('work_name2', $home->work_name2);
        $home->work_name3       = $request->input('work_name3', $home->work_name3);

        $home->work_description      = $request->input('work_description', $home->work_description);
        $home->work_description2     = $request->input('work_description2', $home->work_description2);
        $home->work_description3     = $request->input('work_description3', $home->work_description3);
        $home->review_form_title     = $request->input('review_form_title', $home->review_form_title);
        $home->review_form_sub_title = $request->input('review_form_sub_title', $home->review_form_sub_title);
        $home->our_clients_pinions   = $request->input('our_clients_pinions', $home->our_clients_pinions);
        $home->faq_title             = $request->input('faq_title', $home->faq_title);
        $home->our_trusted_Companies = $request->input('our_trusted_Companies', $home->our_trusted_Companies);
        // Parteners Section
        $home->parteners_description = $request->input('parteners_description', $home->parteners_description);
        $home->parteners_btn         = $request->input('parteners_btn', $home->parteners_btn);
        $home->parteners_sub_title   = $request->input('parteners_sub_title', $home->parteners_sub_title);
        $home->parteners_title       = $request->input('parteners_title', $home->parteners_title);

        $home->what_clients_say_cabout   = $request->input('what_clients_say_cabout', $home->what_clients_say_cabout);
        $home->meta_key                  = $request->input('meta_key', $home->meta_key);
        $home->meta_description          = $request->input('meta_description', $home->meta_description);
        $home->meta_titel                = $request->input('meta_titel', $home->meta_titel);
        $home->sub_our_trusted_Companies = $request->input('sub_our_trusted_Companies', $home->sub_our_trusted_Companies);

        $home->services_sub_title            = $request->input('services_sub_title', $home->services_sub_title);
        $home->faq_sub_title                 = $request->input('faq_sub_title', $home->faq_sub_title);
        $home->work_sub_title                = $request->input('work_sub_title', $home->work_sub_title);
        $home->our_clients_pinions_sub_title = $request->input('our_clients_pinions_sub_title', $home->our_clients_pinions_sub_title);

        // Save the updated home data
        $home->save();

        // Return success response
        return HelperFunc::apiResponse(true, 200, ['message' => 'Home data updated successfully', 'data' => $home]);
    }
}
