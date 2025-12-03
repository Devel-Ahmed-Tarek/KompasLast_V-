<?php
namespace App\Http\Middleware;

use App\Models\Visitor;
use App\Models\VisitorLink;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use Stevebauman\Location\Facades\Location;

class LogVisitor
{
    public function handle(Request $request, Closure $next)
    {
        $ip       = $request->ip();
        $url      = $request->url();
        $location = Location::get($ip);
        $agent    = new Agent();

        DB::transaction(function () use ($ip, $location, $agent, $url) {
            // الحصول على الزائر أو إنشاؤه إذا لم يكن موجودًا
            $visitor = Visitor::firstOrCreate(
                ['ip_address' => $ip], // البحث باستخدام الـ IP
                [
                    'country' => $location->countryName ?? 'Unknown',
                    'city'    => $location->cityName ?? 'Unknown',
                    'device'  => $agent->device(),
                    'browser' => $agent->browser(),
                ]
            );

            // تحديث بيانات الموقع فقط إذا كان الزائر موجودًا مسبقًا
            if (! $visitor->wasRecentlyCreated) {
                $visitor->update([
                    'country' => $location->countryName ?? 'Unknown',
                    'city'    => $location->cityName ?? 'Unknown',
                ]);
            }

            // تحديث أو إنشاء الرابط الخاص بالزائر وزيادة عدد الزيارات
            $visitorLink = VisitorLink::firstOrCreate(
                ['visitor_id' => $visitor->id, 'url' => $url],
                ['visit_count' => 0]// القيمة الابتدائية في حال كان جديدًا
            );

            // زيادة عدد الزيارات
            $visitorLink->increment('visit_count');
        });

        return $next($request);
    }
}