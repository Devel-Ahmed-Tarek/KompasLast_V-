<?php

namespace App\Http\Controllers\Api\Website;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\ConfigApp;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\CustomerSetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerAuthController extends Controller
{
    /**
     * After offer submit popup: create customer account from offer and email OTP to set password.
     */
    public function createFromOffer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'offer_id' => 'required|integer|exists:offers,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors()->all());
        }

        $offer = Offer::find($request->offer_id);
        if (! $offer) {
            return HelperFunc::sendResponse(404, 'Offer not found', []);
        }

        $email = strtolower(trim((string) $offer->email));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return HelperFunc::sendResponse(422, 'Offer has no valid email', [
                'Offer email is required to create an account.',
            ]);
        }

        $existing = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($existing && $existing->role !== 'user') {
            return HelperFunc::sendResponse(409, 'Email already used by another account type', [
                'This email is already registered as ' . $existing->role . '.',
            ]);
        }

        if ($existing && $existing->role === 'user' && $existing->email_verified_at) {
            // Already has a finished account → tell frontend to go login.
            if ($offer->user_id !== $existing->id) {
                $offer->user_id = $existing->id;
                $offer->save();
            }

            return HelperFunc::sendResponse(200, 'Account already exists', [
                'status'          => 'already_exists',
                'user_id'         => $existing->id,
                'offer_id'        => $offer->id,
                'email'           => $existing->email,
                'login_url'       => $this->loginUrl($request),
                'set_password_url'=> null,
            ]);
        }

        try {
            $user = DB::transaction(function () use ($existing, $offer, $email) {
                if ($existing) {
                    $user = $existing;
                    $user->name  = $offer->name ?: $user->name;
                    $user->phone = $offer->phone ?: $user->phone;
                } else {
                    $user = User::create([
                        'name'     => $offer->name ?: 'Customer',
                        'email'    => $email,
                        'phone'    => $offer->phone,
                        'password' => Hash::make(Str::random(48)),
                        'role'     => 'user',
                        'ban'      => 0,
                        'status'   => 1,
                    ]);
                }

                $user->otp            = (string) random_int(100000, 999999);
                $user->otp_expired_at = now()->addMinutes(30);
                $user->email_verified_at = null;
                $user->save();

                $offer->user_id = $user->id;
                $offer->save();

                return $user;
            });
        } catch (\Throwable $e) {
            return HelperFunc::sendResponse(500, 'Could not create account', [$e->getMessage()]);
        }

        $setPasswordUrl = $this->setPasswordUrl($request, $user->email);

        try {
            $user->notify(new CustomerSetPasswordNotification($user->otp, $setPasswordUrl));
        } catch (\Throwable $e) {
            return HelperFunc::sendResponse(500, 'Account created but email failed', [
                'user_id'          => $user->id,
                'email'            => $user->email,
                'set_password_url' => $setPasswordUrl,
                'error'            => $e->getMessage(),
            ]);
        }

        return HelperFunc::sendResponse(201, 'Account created — set password via email', [
            'status'           => 'pending_password',
            'user_id'          => $user->id,
            'offer_id'         => $offer->id,
            'email'            => $user->email,
            'set_password_url' => $setPasswordUrl,
            'login_url'        => $this->loginUrl($request),
        ]);
    }

    /**
     * Customer sets password using OTP from email, then goes to login.
     */
    public function setPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'                 => 'required|email',
            'otp'                   => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors()->all());
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])
            ->where('role', 'user')
            ->first();

        if (! $user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        if (! $user->otp || (string) $user->otp !== (string) $request->otp) {
            return HelperFunc::sendResponse(400, 'Invalid OTP', ['Invalid or expired OTP.']);
        }

        if ($user->otp_expired_at && now()->greaterThan($user->otp_expired_at)) {
            return HelperFunc::sendResponse(400, 'OTP expired', ['OTP expired. Please request a new one.']);
        }

        $user->password          = $request->password; // cast hashed
        $user->otp               = null;
        $user->otp_expired_at    = null;
        $user->email_verified_at = now();
        $user->save();

        return HelperFunc::sendResponse(200, 'Password set — please login', [
            'status'    => 'ready',
            'email'     => $user->email,
            'login_url' => $this->loginUrl($request),
        ]);
    }

    /**
     * Resend setup OTP (pending password accounts only).
     */
    public function resendSetupOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'nullable|email',
            'offer_id' => 'nullable|integer|exists:offers,id',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors()->all());
        }

        if (! $request->filled('email') && ! $request->filled('offer_id')) {
            return HelperFunc::sendResponse(422, 'Validation errors', ['email or offer_id is required']);
        }

        $user = null;
        if ($request->filled('offer_id')) {
            $offer = Offer::find($request->offer_id);
            if ($offer?->user_id) {
                $user = User::where('id', $offer->user_id)->where('role', 'user')->first();
            } elseif ($offer?->email) {
                $user = User::whereRaw('LOWER(email) = ?', [strtolower($offer->email)])
                    ->where('role', 'user')
                    ->first();
            }
        } else {
            $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])
                ->where('role', 'user')
                ->first();
        }

        if (! $user) {
            return HelperFunc::sendResponse(404, 'User not found', []);
        }

        if ($user->email_verified_at) {
            return HelperFunc::sendResponse(200, 'Account already ready', [
                'status'    => 'already_exists',
                'login_url' => $this->loginUrl($request),
            ]);
        }

        $user->otp            = (string) random_int(100000, 999999);
        $user->otp_expired_at = now()->addMinutes(30);
        $user->save();

        $setPasswordUrl = $this->setPasswordUrl($request, $user->email);

        try {
            $user->notify(new CustomerSetPasswordNotification($user->otp, $setPasswordUrl));
        } catch (\Throwable $e) {
            return HelperFunc::sendResponse(500, 'Failed to send email', [$e->getMessage()]);
        }

        return HelperFunc::sendResponse(200, 'OTP resent', [
            'status'           => 'pending_password',
            'email'            => $user->email,
            'set_password_url' => $setPasswordUrl,
            'login_url'        => $this->loginUrl($request),
        ]);
    }

    /**
     * Customer login (role=user) after password is set.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors()->all());
        }

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])
            ->where('role', 'user')
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return HelperFunc::sendResponse(401, 'Invalid login', []);
        }

        if ((int) $user->ban === 1) {
            return HelperFunc::sendResponse(403, 'Account inactive', []);
        }

        if (! $user->email_verified_at) {
            return HelperFunc::sendResponse(403, 'Password not set yet', [
                'status'           => 'pending_password',
                'set_password_url' => $this->setPasswordUrl($request, $user->email),
            ]);
        }

        $token = $user->createToken('Customer Login Token')->plainTextToken;

        return HelperFunc::sendResponse(200, 'Login success', [
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role'  => $user->role,
            ],
        ]);
    }

    private function loginUrl(Request $request): string
    {
        $base = rtrim(
            env('CUSTOMER_LOGIN_URL')
                ?: (ConfigApp::first()?->website ?: 'https://auftragkompass.de'),
            '/'
        );

        $lang = $this->resolveLang($request);

        // If env already points to a full login page, use as-is.
        if (env('CUSTOMER_LOGIN_URL')) {
            return env('CUSTOMER_LOGIN_URL');
        }

        return $base . '/' . $lang . '/login';
    }

    private function setPasswordUrl(Request $request, string $email): string
    {
        if ($custom = env('CUSTOMER_SET_PASSWORD_URL')) {
            $sep = str_contains($custom, '?') ? '&' : '?';
            return $custom . $sep . 'email=' . urlencode($email);
        }

        $base = rtrim(ConfigApp::first()?->website ?: 'https://auftragkompass.de', '/');
        $lang = $this->resolveLang($request);

        return $base . '/' . $lang . '/set-password?email=' . urlencode($email);
    }

    private function resolveLang(Request $request): string
    {
        $allowed = ['de', 'en', 'ar', 'fr', 'it'];
        $raw     = $request->query('lang')
            ?: $request->header('Accept-Language')
            ?: 'de';
        $short = strtolower(substr((string) $raw, 0, 2));

        return in_array($short, $allowed, true) ? $short : 'de';
    }
}
