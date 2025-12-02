<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Models\PartnerPage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminPartnerPageController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:Website Paetner Page Show', ['only' => ['index']]);
        $this->middleware('can:Website Partner Page update', ['only' => ['update']]);

    }
    /**
     * Display all partner pages.
     */
    public function index()
    {
        // Fetch all partner pages
        $page = PartnerPage::first();

        // Add full URL for images
        $page->image_card1      = asset($page->image_card1);
        $page->image_card2      = asset($page->image_card2);
        $page->image_card3      = asset($page->image_card3);
        $page->join_step_image1 = asset($page->join_step_image1);
        $page->join_step_image2 = asset($page->join_step_image2);
        $page->join_step_image3 = asset($page->join_step_image3);

        return HelperFunc::sendResponse(200, 'Partner pages fetched successfully', $page);
    }

    /**
     * Update a specific partner page.
     */
    public function update(Request $request)
    {
        // Validate input data
        $validated = $request->validate([
            'title'                        => 'nullable|array',
            'sub_title'                    => 'nullable|array',
            'first_section_title'          => 'nullable|array',
            'first_section_sub_title'      => 'nullable|array',
            'body'                         => 'nullable|array',
            'image_card1'                  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image_card2'                  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'image_card3'                  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'title_card1'                  => 'nullable|array',
            'title_card2'                  => 'nullable|array',
            'title_card3'                  => 'nullable|array',
            'description_card1'            => 'nullable|array',
            'description_card2'            => 'nullable|array',
            'description_card3'            => 'nullable|array',
            'join_title'                   => 'nullable|array',
            'join_sud_title'               => 'nullable|array',
            'join_step_image1'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'join_step_image2'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'join_step_image3'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'join_step_title1'             => 'nullable|array',
            'join_step_title2'             => 'nullable|array',
            'join_step_title3'             => 'nullable|array',
            'join_step_description1'       => 'nullable|array',
            'join_step_description2'       => 'nullable|array',
            'join_step_description3'       => 'nullable|array',
            'last_section_title'           => 'nullable|array',
            'last_section_description'     => 'nullable|array',
            'last_section_btn'             => 'nullable|array',
            'last_section_login_title'     => 'nullable|array',
            'last_section_login_sub_title' => 'nullable|array',
            'meta_key'                     => 'nullable|array',
            'meta_title'                   => 'nullable|array',
            'meta_description'             => 'nullable|array',
        ]);

        // Find the specific partner page
        $partnerPage = PartnerPage::first();

        if (! $partnerPage) {
            return HelperFunc::sendResponse(404, 'Partner page not found');
        }

        // Update translatable attributes
        foreach ($partnerPage->getTranslatableAttributes() as $field) {
            if (isset($validated[$field]) && is_array($validated[$field])) {
                foreach ($validated[$field] as $locale => $value) {
                    $partnerPage->setTranslation($field, $locale, $value);
                }
            }
        }

        // Handle image uploads
        foreach (['image_card1', 'image_card2', 'image_card3', 'join_step_image1', 'join_step_image2', 'join_step_image3'] as $imageField) {
            if ($request->hasFile($imageField)) {
                // Delete the old image
                HelperFunc::deleteFile($partnerPage->{$imageField});

                // Upload the new image
                $path                       = HelperFunc::uploadFile('partner_pages', $request->file($imageField));
                $partnerPage->{$imageField} = $path;
            }
        }

        // Save updates to the database
        $partnerPage->save();

        return HelperFunc::sendResponse(200, 'Partner page updated successfully', $partnerPage);
    }
}
