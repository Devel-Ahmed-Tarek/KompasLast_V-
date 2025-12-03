<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class AdminContactController extends Controller
{
    public function index()
    {
        $data = Contact::paginate(10);
        return HelperFunc::pagination($data, $data->items());
    }

    public function store(Request $request)
    {
        $contact = Contact::create($request->all());
        return HelperFunc::sendResponse(200, '', $contact);

    }
}
