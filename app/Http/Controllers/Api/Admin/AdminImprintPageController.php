<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\ImprintPage;
use Illuminate\Http\Request;

class AdminImprintPageController extends Controller
{
    public function index()
    {
        $data = ImprintPage::first();
        return HelperFunc::sendResponse(200, '', $data);

    }

    public function update(Request $request)
    {
        // Validate the input data
        $validated = $request->validate([
            'title'            => ['nullable', 'array'],
            'sub_title'        => ['nullable', 'array'],
            'body'             => ['nullable', 'array'],
            'meta_key'         => ['nullable', 'array'],
            'meta_title'       => ['nullable', 'array'],
            'meta_description' => ['nullable', 'array'],
        ]);

        // Fetch the first ImprintPage record
        $imprint = ImprintPage::first();

        // If ImprintPage record not found, return a not found response
        if (! $imprint) {
            return HelperFunc::sendResponse(404, 'Imprint page not found');
        }

        // Update translatable attributes if they exist in the request
        foreach ($imprint->getTranslatableAttributes() as $field) {
            if (isset($validated[$field]) && is_array($validated[$field])) {
                foreach ($validated[$field] as $locale => $value) {
                    $imprint->setTranslation($field, $locale, $value);
                }
            }
        }

        // Update the ImprintPage record
        $imprint->update($validated);

        // Return the updated ImprintPage data
        return HelperFunc::sendResponse(200, 'Imprint page updated successfully', $imprint);
    }

}
