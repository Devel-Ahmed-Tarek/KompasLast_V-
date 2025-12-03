<?php
namespace App\Http\Controllers\Api\Company;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\OfferFakeReport;
use App\Models\User;
use App\Notifications\PaymentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class OfferFakeReportController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search'); // Get search query

        // Build the query
        $query = OfferFakeReport::whereHas('shopping_list', function ($q) {
            $q->where('user_id', auth()->user()->id);
        });

        // Apply status filter if provided
        if ($status) {
            if (! in_array($status, ['open', 'testing', 'pending', 'confirmed', 'canceled'])) {
                return HelperFunc::sendResponse(422, 'Invalid status filter');
            }
            $query->where('status', $status)
                ->with('shopping_list.offer', 'shopping_list.company');
        }

        // Apply search filter if provided
        if ($search) {
            $query->whereHas('shopping_list.offer', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%'); // Adjust the field name if needed
            });
        }

        // Paginate results
        $reports = $query->with(['shopping_list.offer', 'shopping_list.company'])->paginate(10);

        // Return paginated response
        return HelperFunc::pagination($reports, $reports->items());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shopping_list_id' => 'required|exists:shopping_lists,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, __('Validation errors'), $validator->errors()->all());
        }

        $report = OfferFakeReport::create($request->all());

        $report->load('shopping_list.offer', 'shopping_list.company');

        $offerName   = $report->shopping_list->offer->name ?? 'Unknown Offer';
        $companyName = $report->shopping_list->company->name ?? 'Unknown Company';

        $admins = User::where('role', 'admin')
            ->where('available_notification', '1')
            ->get();

        Notification::send($admins, new PaymentNotification([
            'type'    => 'fakeOffer',
            'type_id' => $report->shopping_list_id,
            'mgs'     => [
                'en' => "A user has reported a fake offer: {$offerName} from company: {$companyName}.",
                'de' => "Ein Benutzer hat ein gefälschtes Angebot gemeldet: {$offerName} von der Firma: {$companyName}.",
            ],
        ]));

        return HelperFunc::sendResponse(201, __('messages.created_successfully'), $report);
    }

}
