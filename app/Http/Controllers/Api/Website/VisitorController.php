<?php
namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use App\Models\VisitorLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class VisitorController extends Controller
{
    public function trackVisitor(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'ip_address' => 'required|ip',
            'country'    => 'nullable|string|max:255',
            'city'       => 'nullable|string|max:255',
            'device'     => 'nullable|string|max:255',
            'browser'    => 'nullable|string|max:255',
            'url'        => 'required|url',
        ]);

        // Run the process inside a transaction
        $visitorData = DB::transaction(function () use ($validatedData, $request) {
            // Retrieve or create the visitor
            $visitor = Visitor::firstOrCreate(
                ['ip_address' => $this->getClientIp($request)],
                [
                    'country' => $validatedData['country'] ?? 'Unknown',
                    'city'    => $validatedData['city'] ?? 'Unknown',
                    'device'  => $validatedData['device'] ?? 'Unknown',
                    'browser' => $validatedData['browser'] ?? 'Unknown',
                ]
            );

            // Update country/city if they changed
            if (! $visitor->wasRecentlyCreated &&
                ($visitor->country !== ($validatedData['country'] ?? 'Unknown') ||
                    $visitor->city !== ($validatedData['city'] ?? 'Unknown'))) {
                $visitor->update([
                    'country' => $validatedData['country'] ?? 'Unknown',
                    'city'    => $validatedData['city'] ?? 'Unknown',
                ]);
            }

            // Track the URL visit
            $visitorLink = VisitorLink::firstOrCreate(
                ['visitor_id' => $visitor->id, 'url' => $validatedData['url']],
                ['visit_count' => 1]
            );

            if (! $visitorLink->wasRecentlyCreated) {
                $visitorLink->increment('visit_count');
            }

            // Return the visitor with linked visits
            return Visitor::with('links')->find($visitor->id);
        });

        return response()->json([
            'message' => 'Visitor tracked successfully',
            'visitor' => $visitorData,
        ]);
    }

    private function getClientIp(Request $request)
    {
        $ip = $request->header('X-Forwarded-For');
        if ($ip) {
            // إذا كان هناك أكثر من IP مفصولين بفواصل، خذ أول واحد
            $ip = explode(',', $ip)[0];
        } else {
            $ip = $request->ip();
        }
        return trim($ip);
    }
}
