<?php
namespace App\Http\Controllers;

use App\Helpers\HelperFunc;
use App\Http\Resources\Website\typeDitaliServesPageFormResource;
use App\Models\Form;
use App\Models\ServesPageForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PageServesFormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $slug)
    {
        $request  = request();
        $language = $request->get('lang', 'en'); // Default to 'en' if no language is provided
        App::setLocale($language);

        // نبحث داخل كل اللغات في JSON slug
        $record = ServesPageForm::with('type.typeDitaliServices:type_id,short_description')
            ->whereRaw("JSON_SEARCH(slug, 'all', ?) IS NOT NULL", [$slug])
            ->first();

        if (! $record) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $record->form = Form::first();

        return HelperFunc::sendResponse(200, 'done', new typeDitaliServesPageFormResource($record));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
