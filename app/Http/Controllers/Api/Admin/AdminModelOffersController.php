<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Models\ModelOffers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class AdminModelOffersController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Website Model Offers Page Show', ['only' => ['index']]);
        $this->middleware('can:Website Model Offers Page update', ['only' => ['update']]);
    }

    public function index(Request $request)
    {
        // Fetch paginated list of offers
        $offers = ModelOffers::paginate(10);

        // Append full image URL to each offer
        $offers->getCollection()->transform(function ($offer) {
            $offer->img = $offer->img ? asset($offer->img) : null;
            return $offer;
        });

        // Return paginated response using HelperFunc
        return HelperFunc::pagination($offers, $offers->items());
    }

    public function store(Request $request)
    {
        // Validate inputs
        $Validator = Validator::make($request->all(), [
            'link'          => 'nullable|string|url',
            'img'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'btn'           => 'nullable|array',
            'btn.*'         => 'string',
            'title'         => 'nullable|array',
            'title.*'       => 'string',
            'description'   => 'nullable|array',
            'description.*' => 'string',
        ]);

        if ($Validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $Validator->messages()->all());
        }

        $validated = $Validator->validate();

        $page = new ModelOffers;

        // Save translatable fields
        foreach ($page->getTranslatableAttributes() as $field) {
            if (! empty($validated[$field]) && is_array($validated[$field])) {
                foreach ($validated[$field] as $locale => $value) {
                    $page->setTranslation($field, $locale, $value);
                }
            }
        }

        // Assign link if provided
        $page->link = $validated['link'] ?? null;

        // Upload image if exists
        if ($request->hasFile('img')) {
            $page->img = HelperFunc::uploadFile('model', $request->file('img'));
        }

        $page->save();

        return HelperFunc::sendResponse(201, 'Offer created successfully', $page);
    }

    public function update(Request $request, $id)
    {
        $page = ModelOffers::find($id);
        if (! $page) {
            return HelperFunc::sendResponse(404, 'Offer not found');
        }

        // Validate inputs
        $Validator = Validator::make($request->all(), [
            'link'          => 'nullable|string|url',
            'img'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'btn'           => 'nullable|array',
            'btn.*'         => 'string',
            'title'         => 'nullable|array',
            'title.*'       => 'string',
            'description'   => 'nullable|array',
            'description.*' => 'string',
        ]);

        if ($Validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $Validator->messages()->all());
        }

        $validated = $Validator->validate();

        // Update translatable fields
        foreach ($page->getTranslatableAttributes() as $field) {
            if (! empty($validated[$field]) && is_array($validated[$field])) {
                foreach ($validated[$field] as $locale => $value) {
                    $page->setTranslation($field, $locale, $value);
                }
            }
        }

        // Handle image upload
        if ($request->hasFile('img')) {
            if ($page->img) {
                HelperFunc::deleteFile($page->img); // Remove old image
            }
            $page->img = HelperFunc::uploadFile($request->file('img'), 'model'); // Corrected order
        }

        // Update link if provided
        $page->link = $validated['link'] ?? $page->link;

        $page->save();

        return HelperFunc::sendResponse(200, 'Offer updated successfully', $page);
    }

    public function destroy($id)
    {
        $page = ModelOffers::find($id);
        if (! $page) {
            return HelperFunc::sendResponse(404, 'Offer not found');
        }

        // Delete the associated image if it exists
        if ($page->img) {
            HelperFunc::deleteFile($page->img);
        }

        // Delete the offer
        $page->delete();

        return HelperFunc::sendResponse(200, 'Offer deleted successfully');
    }

    public function updateStatus(Request $request, $id)
    {
        $page = ModelOffers::find($id);
        if (! $page) {
            return HelperFunc::sendResponse(404, 'Offer not found');
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'status' => 'required|boolean', // Ensures status is either 1 or 0
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        // Update status
        $page->status = $request->status;
        $page->save();

        return HelperFunc::sendResponse(200, 'Offer status updated successfully', $page);
    }
}
