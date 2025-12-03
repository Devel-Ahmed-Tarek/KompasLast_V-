<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Models\PrivacyPage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminPrivacyPageController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:Website Privacy Page Show', ['only' => ['index']]);
        $this->middleware('can:Website Privacy Page update', ['only' => ['update']]);

    }
    public function index()
    {
        $page = PrivacyPage::first();
        return HelperFunc::sendResponse(200, '', $page);
    }
    public function update(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'nullable|array',
            'body'             => 'nullable|array',
            'meta_key'         => 'nullable|array',
            'meta_title'       => 'nullable|array',
            'meta_description' => 'nullable|array',
        ]);

        $page = PrivacyPage::first();
        foreach ($page->getTranslatableAttributes() as $field) {
            if (isset($validated[$field]) && is_array($validated[$field])) {
                foreach ($validated[$field] as $locale => $value) {
                    $page->setTranslation($field, $locale, $value);
                }
            }
        }
        $page->save();

        return HelperFunc::sendResponse(200, '', $page);
    }

}
