<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return HelperFunc::apiResponse(false, 422, __('messages.validation_errors'), $validator->messages()->all());
        }

        $user = DB::table('users')
            ->where('email', $request->email)
            ->where('role', 'admin')
            ->first();

        if (! $user) {
            return HelperFunc::apiResponse(false, 401, ['message' => 'The login data is incorrect']);
        }

        // 3. التحقق من كلمة المرور
        if (! Hash::check($request->password, $user->password)) {
            return HelperFunc::apiResponse(false, 401, ['message' => 'The login data is incorrect']);
        }

        // 4. توليد التوكن
        $plainTextToken = Str::random(40);
        $hashedToken    = hash('sha256', $plainTextToken);

        // 5. تخزين التوكن
        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => 'App\Models\User',
            'tokenable_id'   => $user->id,
            'name'           => 'Admin Manual Token',
            'token'          => $hashedToken,
            'abilities'      => json_encode(['*']),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // 6. إرسال الاستجابة
        return HelperFunc::apiResponse(true, 200, [
            'message' => 'Login successful',
            'user'    => $user,
            'token'   => $plainTextToken,
        ]);
    }

    public function registerPost(Request $request)
    {
        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string',
            'img'      => 'image|mimes:jpg,jpeg,png,gif',       // Ensure it's an image file
            'email'    => 'required|email|unique:users,email',  // Ensure email is unique
            'phone'    => 'required|string|unique:users,phone', // Ensure phone number is unique
            'password' => 'required|confirmed|string|min:8',
            'roles'    => 'array|required',
            'roles .*' => 'exists:roles,id', // Ensure password is confirmed and has a minimum length
        ]);

        // If validation fails, return the errors
        if ($validator->fails()) {
            return HelperFunc::apiResponse(false, 422, $validator->messages()->all());
        }

        DB::beginTransaction();
        try {
            // Handle image upload
            $imagePath = 'https://ui-avatars.com/api/?name=' . $request->name;

            if ($request->hasFile('img')) {
                // Store the image and get its path
                $imagePath = $request->file('img')->store('images', 'public');
            }

            // Create the new user
            $admin           = new User();
            $admin->name     = $request->name;
            $admin->phone    = $request->phone;
            $admin->email    = $request->email;
            $admin->img      = $imagePath; // Save the image path in the database
            $admin->status   = 0;          // inactive by default
            $admin->role     = 'admin';
            $admin->password = bcrypt($request->password);
            $admin->save();

            $admin->assignRole($request->roles);

            DB::commit();
            return HelperFunc::apiResponse(true, 201, ['message' => 'Admin registered successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return HelperFunc::apiResponse(false, 500, ['message' => 'Error occurred while registering admin', 'error' => $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return HelperFunc::apiResponse(true, 200, ['message' => 'Logged out successfully']);
    }
}
