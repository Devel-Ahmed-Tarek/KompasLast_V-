<?php
namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\Website\ContactPageResource;
use App\Models\ContactPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ContactPageController extends Controller
{
    public function index(Request $request)
    {
        $language = $request->get('lang', 'en'); // Default to 'en' if no language is provided
        App::setLocale($language);
        $data = ContactPage::first();
        return HelperFunc::sendResponse(200, 'done', new ContactPageResource($data));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email',
            'description' => 'nullable|string',
            'question'    => 'nullable|string',
            'phone'       => 'nullable|string|max:15',
        ]);

        $data = [
            'name'        => $request->name,
            'email'       => $request->email,
            'description' => $request->description,
            'phone'       => $request->phone,
            'ip'          => $request->ip(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ];

        DB::table('contacts')->insert($data);

        return HelperFunc::sendResponse(200, 'done', $data);
    }

}
