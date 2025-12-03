<?php

namespace App\Http\Controllers\Api;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Type;
use App\Models\Visitor;
use App\Models\Wallet;
use Carbon\Carbon; // نموذج الزوار
use Illuminate\Http\Request;

// نموذج العروض

class DashboardController extends Controller
{
    public function getDailyStatistics(Request $request)
    {
        // الحصول على السنة والشهر من الطلب، أو استخدام السنة والشهر الحاليين كقيمة افتراضية
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        // التحقق من صلاحية السنة والشهر
        if (!is_numeric($year) || !is_numeric($month) || $month < 1 || $month > 12) {
            return response()->json([
                'error' => 'Invalid year or month provided.',
            ], 400);
        }

        // تحديد عدد أيام الشهر
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        // إنشاء البيانات اليومية
        $data = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');

            // استعلام عدد الزوار والعروض يوميًا
            $visitorsCount = Visitor::whereDate('created_at', $date)->count();
            $offersCount = Offer::whereDate('created_at', $date)->count();

            $data[] = [
                'date' => $date,
                'visitors' => $visitorsCount,
                'offers' => $offersCount,
            ];
        }

        // إرجاع البيانات في استجابة JSON
        return HelperFunc::sendResponse(200, 'done', $data);
    }

    public function getOffersChartData(Request $request)
    {
        try {
            // الحصول على السنة والشهر من الطلب أو استخدام القيم الافتراضية
            $year = $request->input('year', Carbon::now()->year); // السنة الحالية كافتراضية
            $month = $request->input('month', Carbon::now()->month); // الشهر الحالي كافتراضي

            // استعلام للحصول على عدد العروض حسب السنة والشهر
            $formattedData = Type::withCount(['offers' => function ($offer) use ($year, $month) {
                $offer->whereYear('created_at', $year) // تصفية حسب السنة
                    ->whereMonth('created_at', $month); // تصفية حسب الشهر
            }])
                ->get()
                ->map(function ($type) {
                    return [
                        'name' => $type->name, // استخدام اسم الـ Type
                        'offers_count' => $type->offers_count, // إضافة عدد العروض
                    ];
                });

            // إرجاع البيانات بتنسيق JSON
            return HelperFunc::sendResponse(200, 'Chart data retrieved successfully.', $formattedData);
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred while fetching the chart data: ' . $e->getMessage(), []);
        }
    }

    public function getInfoDataToDashboard()
    {
        // GET ALL WALLETS FROM THE DATABASE
        $wallets = Wallet::all();
        // TOTAL PROFIT
        $totalProfit = $wallets->sum('expense');
        // TOTAL REVENUE
        $Revenue = $wallets->sum('amount');
        // PROFITS ARE NOT DUE = Total Revenue - Total Profit
        $ProfitsAreNotDue = $Revenue - $totalProfit;

        $lastTenOffers = Offer::with(['type:id,name', 'shopping_list'])
            ->select('id', 'type_id', 'date', 'count')
            ->take(10)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($offer) {
                $now = Carbon::now();

                // تحديد حالة العرض بناءً على الفلترة باستخدام switch
                switch (true) {
                    case Carbon::parse($offer->date)->subDay()->isToday() && $offer->date < $now && $offer->count != 0:
                        $offer->status = '24_hours_to_filed';
                        break;

                    case $offer->shopping_list->isNotEmpty():
                        $offer->status = 'not_completed';
                        break;

                    case $offer->shopping_list->isEmpty():
                        $offer->status = 'new';
                        break;

                    case Carbon::parse($offer->date)->isPast() && $offer->count != 0:
                        $offer->status = 'filed';
                        break;

                    case Carbon::parse($offer->date)->isPast() && $offer->count == 0:
                        $offer->status = 'completed';
                        break;

                    default:
                        $offer->status = 'unknown';
                        break;
                }
                unset($offer->shopping_list); // استبعاد shopping_list

                return $offer;
            });

        // إرجاع البيانات كاستجابة JSON

        return HelperFunc::sendResponse(200, 'Dashboard info retrieved successfully.', [
            'total_profit' => $totalProfit,
            'total_revenue' => $Revenue,
            'profits_are_not_due' => $ProfitsAreNotDue,
            'last_ten_offers' => $lastTenOffers,
        ]);
    }

}
