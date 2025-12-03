<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Nav;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminNavController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'home' => 'required|string|max:255',
            'home' => 'required|array',
            'home.en' => 'required|string',
            'home.de' => 'required|string',
            'home.fr' => 'required|string',
            'home.it' => 'required|string',

            'services' => 'required|array',
            'services.en' => 'required|string',
            'services.de' => 'required|string',
            'services.fr' => 'required|string',
            'services.it' => 'required|string',

            'aboutUs' => 'required|array',
            'aboutUs.en' => 'required|string',
            'aboutUs.de' => 'required|string',
            'aboutUs.fr' => 'required|string',
            'aboutUs.it' => 'required|string',

            'blogs' => 'required|array',
            'blogs.en' => 'required|string',
            'blogs.de' => 'required|string',
            'blogs.fr' => 'required|string',
            'blogs.it' => 'required|string',

            'contactUs' => 'required|array',
            'contactUs.en' => 'required|string',
            'contactUs.de' => 'required|string',
            'contactUs.fr' => 'required|string',
            'contactUs.it' => 'required|string',

            'button' => 'required|array',
            'button.en' => 'required|string',
            'button.de' => 'required|string',
            'button.fr' => 'required|string',
            'button.it' => 'required|string',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }
        // Either update the existing entry or create a new one
        $nav = Nav::updateOrCreate(['id' => 1], $validator);

        return HelperFunc::sendResponse(201, 'Navigation data saved successfully', $nav);

    }

    public function index()
    {
        $nav = Nav::all();

        return HelperFunc::sendResponse(200, 'Navigation data saved successfully', $nav);
    }
}
