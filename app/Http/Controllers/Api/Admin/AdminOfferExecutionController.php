<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\OfferExecution;

class AdminOfferExecutionController extends Controller
{

    public function index()
    {
        $data = OfferExecution::with(['company', 'offer'])->paginate(10);

        return HelperFunc::pagination($data, $data->items());
    }

    public function AnnouncementGet()
    {
        $data = Announcement::with(['offer'])->paginate(10);
        return HelperFunc::pagination($data, $data->items());
    }
}
