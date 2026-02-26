<?php
namespace App\Http\Controllers\Api\Company;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\ConfigApp;
use App\Models\User;
use App\Notifications\ForgettingPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CompanyAuthController extends Controller
{

    public function loginPost(Request $request)
    {
        $config = DB::table('config_apps')->first();

        if ($config && $config->on_auth_company == 1) {
            return HelperFunc::sendResponse(200, __('auth.auth_disabled'), []);
        }

        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, __('auth.validation_error'), $validator->errors()->all());
        }

        // البحث عن المستخدم بالشروط
        $user = DB::table('users')
            ->where('email', $request->email)
            ->where('role', 'company')
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return HelperFunc::sendResponse(401, __('auth.invalid_login'), []);
        }

        // تحميل بيانات الشركة المرتبطة
        $companyDetails = DB::table('company_details')
            ->where('user_id', $user->id)
            ->first();

        // التحقق من الحظر
        if ($user->ban == 1) {
            return HelperFunc::sendResponse(403, __('auth.account_inactive'), []);
        }

        // التحقق من تاريخ الانتهاء
        if ($companyDetails && $companyDetails->exp_date < now()) {
            DB::table('company_details')
                ->where('id', $companyDetails->id)
                ->update(['sucsses' => 0]);

            $companyDetails->sucsses = 0;
        }

        // توليد التوكن يدويًا
        $plainTextToken = Str::random(40);
        $hashedToken    = hash('sha256', $plainTextToken);

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => 'App\Models\User',
            'tokenable_id'   => $user->id,
            'name'           => 'Company Login Token',
            'token'          => $hashedToken,
            'abilities'      => json_encode(['*']),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $admins = User::where("role", "admin")->where('available_notification', '1')->get();
        Notification::send($admins, new \App\Notifications\PaymentNotification([
            'type'    => "loginCompany",
            'type_id' => $user->id,
            'mgs'     => [
                'en' => 'The company ' . $user->name . ' logged in',
                'de' => 'Die Firma ' . $user->name . ' hat sich eingeloggt',
            ],
        ]));

        // إرجاع الاستجابة
        return HelperFunc::sendResponse(200, __('auth.login_success'), [
            'user'             => $user,
            'company_approved' => $companyDetails ? $companyDetails->sucsses : 0,
            'token'            => $plainTextToken,
        ]);
    }
    public function registerPost(Request $request)
    {
        $config = ConfigApp::first();
        if ($config->add_company == 1) {
            return HelperFunc::sendResponse(200, __('auth.register_disabled'), []);
        }

        $validator = Validator::make($request->all(), [
            'companyName'  => 'required|string',
            'regNumber'    => 'nullable',
            'foundedYear'  => 'nullable',
            'ownerName'    => 'nullable',
            'email'        => 'required|unique:users,email',
            'website'      => 'nullable|',
            'address'      => 'required|string',
            'country'      => 'required|string',
            'phone'        => 'nullable',
            'ZIPCode'      => 'required',
            'city'         => 'required',
            'mobileNumber' => 'nullable',
            'works'        => 'required|array',
            'works.*'      => 'exists:types,id',
            'img'          => 'nullable|file|mimes:jpg,jpeg,png',
            'about'        => 'required|string',
            'password'     => 'required|confirmed|string',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, __('auth.validation_error'), $validator->errors()->all());
        }

        DB::beginTransaction();
        try {
            $imagePath = 'default.png';
            if ($request->hasFile('img')) {
                $imagePath = HelperFunc::uploadFile('images', $request->file('img'));
            }

            // إنشاء المستخدم باستخدام Query Builder
            $userId = DB::table('users')->insertGetId([
                'name'       => $request->companyName,
                'phone'      => $request->phone,
                'email'      => $request->email,
                'img'        => $imagePath,
                'status'     => 0,
                'role'       => 'company',
                'password'   => Hash::make($request->password),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // إنشاء CompanyDetail
            DB::table('company_details')->insert([
                'user_id'      => $userId,
                'reg_name'     => $request->regNumber ?: 'N/A',
                'founded_year' => $request->foundedYear,
                'owner_name'   => $request->ownerName,
                'website'      => $request->website,
                'address'      => $request->address,
                'city'         => $request->city,
                'ZIPCode'      => $request->ZIPCode,
                'country'      => $request->country,
                'phone2'       => $request->phone2 ?: 'N/A',
                'banc_ip'      => $request->banc_ip,
                'banc_count'   => $request->banc_count,
                'banc_name'    => $request->banc_name,
                'number'       => $request->mobileNumber,
                'counties'     => $request->counties ?: 'N/A',
                'about'        => $request->about,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // ربط أنواع الأعمال باستخدام Pivot Table
            $userModel = User::find($userId);
            $userModel->typesComapny()->attach($request->works);

            // إنشاء المحفظة
            DB::table('wallets')->insert([
                'user_id'    => $userId,
                'amount'     => 100,
                'expense'    => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // send notification to Admin
            $admins = User::query()->where('role', 'admin')->where("available_notification", '1')->get();
            Notification::send($admins, new \App\Notifications\PaymentNotification([
                'type'    => "registerCompany",
                'type_id' => $userId,
                'mgs'     => [
                    'en' => 'You Have a New Order Payment From : ' . $request->companyName,
                    'de' => 'Sie haben eine neue Zahlungsbestellung von: ' . $request->companyName,

                ],
            ]));

            DB::commit();
            return HelperFunc::sendResponse(201, __('auth.register_success'), []);
        } catch (\Exception $e) {
            DB::rollBack();
            return HelperFunc::sendResponse(500, __('auth.server_error'), [$e->getMessage()]);
        }
    }
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, __('auth.validation_error'), $validator->errors()->all());
        }

        $user = User::where('email', $request->email)->first();

        $user->otp            = rand(100000, 999999);  // إنشاء OTP مكون من 6 أرقام
        $user->otp_expired_at = now()->addMinutes(10); // تحديد انتهاء صلاحية OTP بعد 10 دقائق
        $user->save();
        try {
            $user->notify(new ForgettingPasswordNotification($user->otp));
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, __('auth.otp_send_failed'), ['error' => $e->getMessage()]);
        }

        return HelperFunc::sendResponse(200, __('auth.otp_sent'), []);
    }
    public function checkOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp'   => 'required|digits:6', // تأكد من أنه مكون من 6 أرقام
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, __('auth.validation_error'), $validator->errors()->all());
        }

        $user = User::where('email', $request->email)->first();

        if ($request->otp == $user->otp) {
            return HelperFunc::sendResponse(200, __('auth.otp_valid'), []);
        } else {
            return HelperFunc::sendResponse(400, __('auth.otp_invalid'), []);
        }

    }
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|confirmed',
            'email'    => 'required|email|exists:users,email',
            'otp'      => 'required',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, __('auth.validation_error'), $validator->errors()->all());
        }
        $user = User::where('email', $request->email)->first();
        if ($request->otp == $user->otp) {
            $user->password       = bcrypt($request->password);
            $user->otp            = null; // إعادة تعيين OTP
            $user->otp_expired_at = null; // إعادة تعيين وقت انتهاء الصلاحية
            $user->save();
            return HelperFunc::sendResponse(200, __('auth.password_changed'), []);
        }
        return HelperFunc::sendResponse(200, __('auth.otp_valid'), []);

    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $user   = $request->user();
        $admins = User::where("role", "admin")->where('available_notification', '1')->get();
        Notification::send($admins, new \App\Notifications\PaymentNotification([
            'type'    => "logoutCompany",
            'type_id' => $user->id,
            'mgs'     => [
                'en' => 'The company ' . $user->name . ' logged out',
                'de' => 'Die Firma ' . $user->name . ' hat sich ausgeloggt',
            ],
        ]));

        return HelperFunc::apiResponse(true, 200, ['message' => 'Logged out successfully']);
    }
}
