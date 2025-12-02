<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminCouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::when($request->search, function ($query) use ($request) {
            $query->where('code', 'like', '%' . $request->search . '%');
        })->orderBy('id', 'desc')->paginate(HelperFunc::limit($request->limit));

        return HelperFunc::Pagination($coupons, $coupons->items());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'        => 'required|unique:coupons,code',
            'discount'    => 'required|numeric',
            'type'        => ['required', Rule::in(['percentage', 'fixed'])],
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'multi_used'  => 'boolean',
            'is_active'   => 'boolean',
            'type_id'     => 'nullable|exists:types,id',
        ]);

        $coupon = Coupon::create($data);

        return HelperFunc::sendResponse(201, 'Coupon created successfully.', $coupon);
    }

    public function show($id)
    {
        $coupon = Coupon::find($id);

        if (! $coupon) {
            return HelperFunc::sendResponse(404, 'Coupon not found.');
        }

        return HelperFunc::sendResponse(200, 'Coupon details retrieved.', $coupon);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::find($id);

        if (! $coupon) {
            return HelperFunc::sendResponse(404, 'Coupon not found.');
        }

        $validated = Validator::make($request->all(), [
            'code'        => ['sometimes', 'unique:coupons,code,' . $coupon->id],
            'discount'    => 'sometimes|numeric',
            'type'        => ['sometimes', Rule::in(['percentage', 'fixed'])],
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'multi_used'  => 'boolean',
            'is_active'   => 'boolean',
            'type_id'     => 'nullable|exists:types,id',
        ])->validate();

        // إعادة ضبط القيم مع الحفاظ على القديمة في حالة غيابها
        $data = [
            'code'        => strtolower(Arr::get($validated, 'code', $coupon->code)),
            'discount'    => floatval(Arr::get($validated, 'discount', $coupon->discount)),
            'type'        => strtolower(Arr::get($validated, 'type', $coupon->type)),
            'start_date'  => Arr::get($validated, 'start_date') ? Carbon::parse($validated['start_date']) : $coupon->start_date,
            'end_date'    => Arr::get($validated, 'end_date') ? Carbon::parse($validated['end_date']) : $coupon->end_date,
            'usage_limit' => Arr::get($validated, 'usage_limit', $coupon->usage_limit),
            'multi_used'  => Arr::get($validated, 'multi_used', $coupon->multi_used),
            'is_active'   => Arr::get($validated, 'is_active', $coupon->is_active),
            'type_id'     => Arr::get($validated, 'type_id', $coupon->type_id),
        ];

        $coupon->update($data);

        return HelperFunc::sendResponse(200, 'Coupon updated successfully.', $coupon);
    }

    public function destroy($id)
    {
        $coupon = Coupon::find($id);

        if (! $coupon) {
            return HelperFunc::sendResponse(404, 'Coupon not found.');
        }

        $coupon->delete();

        return HelperFunc::sendResponse(200, 'Coupon deleted successfully.');
    }
}
