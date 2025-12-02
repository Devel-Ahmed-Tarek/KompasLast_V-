<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserAdminResource;
use App\Http\Resources\UserCompanyResource;
use App\Models\CompanyDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index($type, $limit = 10)
    {

        if ($type == "company") {

            $user = User::where('type', 'company')->with('copany_details')->orderBy('id', 'dese')->paginate($limit);

            return HelperFunc::pagination($user, UserCompanyResource::collection($user));

        }

        if ($type == "admin") {

            $user = User::where('type', 'admin')->orderBy('id', 'dese')->paginate($limit);

        }

        return HelperFunc::pagination($user, UserAdminResource::collection($user));

    }

    public function updateCompany(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'nullable|string',
            'phone'        => 'nullable|string',
            'phone2'       => 'nullable|string',
            'email'        => 'nullable|email|unique:users,email,' . $id,
            'img'          => 'nullable|file|mimes:jpg,jpeg,png',
            'address'      => 'nullable|string',
            'website'      => 'nullable|string',
            'about'        => 'nullable|string',
            'founded_year' => 'nullable|string',
            'banc_count'   => 'nullable|string',
            'banc_ip'      => 'nullable|string',
            'banc_name'    => 'nullable|string',
            'file'         => 'nullable|file|mimes:pdf,doc,docx',
            'file2'        => 'nullable|file|mimes:pdf,doc,docx',
            'file3'        => 'nullable|file|mimes:pdf,doc,docx',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        $company = User::find($id);

        if (! $company || $company->role !== 'company') {
            return response()->json(['message' => 'Company not found'], 404);
        }

        $fieldsToUpdate = ['name', 'phone', 'email'];
        foreach ($fieldsToUpdate as $field) {
            if ($request->has($field)) {
                $company->$field = $request->$field;
            }
        }

        if ($request->hasFile('img')) {
            $company->img = HelperFunc::uploadFile('images', $request->file('img'));
        }

        $company->save();

        // تحديث أو إنشاء تفاصيل الشركة
        $companyDetails = $company->companyDetails ?? new CompanyDetail(['user_id' => $company->id]);

        $detailsFields = ['address', 'website', 'about', 'number', 'phone2', 'description', 'banc_ip', 'banc_count', 'banc_name', 'founded_year'];
        foreach ($detailsFields as $field) {
            if ($request->has($field)) {
                $companyDetails->$field = $request->$field;
            }
        }

        // تحديث الملفات
        $files = ['file', 'file2', 'file3'];
        foreach ($files as $file) {
            if ($request->hasFile($file)) {
                $companyDetails->$file = HelperFunc::uploadFile('files', $request->file($file));
                $companyDetails->count_offer++;
            }
        }

        // تحقق من الحالة بناءً على عدد العروض
        if ($companyDetails->count_offer > 3) {
            $companyDetails->status = 1;
        }

        $companyDetails->save();
        return HelperFunc::sendResponse(200, __('messages.updated_successfully'));
    }
    public function update($id, Request $request)
    {
        // Validate inputs using Validator
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $id,
            'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|min:8|confirmed',
            'roles'    => 'array|nullable',
            'roles.*'  => 'exists:roles,id', // Ensure roles exist in the database
        ]);

        // Check for validation errors
        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        // Find the user
        $user = User::findOrFail($id);

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($user->img) {
                HelperFunc::deleteFile(public_path($user->img));
            }
            // Store new image
            $user->img = HelperFunc::uploadFile('/user', $request->file('image'));
        }

        // Update user data
        $user->name  = $request->name;
        $user->phone = $request->phone;
        $user->email = $request->email;

        // Update password only if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Update user roles if provided
        if ($request->has('roles')) {
            $user->syncRoles($request->input('roles')); // Correct method
        }

        // API response
        return HelperFunc::sendResponse(200, "User updated successfully", $user);
    }
    public function profile($id)
    {
        $user = User::findOrFail($id);

        $user['roles'] = $user->roles->map(fn($role) => [
            'id'   => $role->id,
            'name' => $role->name,
        ]);

        return HelperFunc::sendResponse(200, 'User profile retrieved successfully', $user);
    }

    public function profile_auth()
    {
        $user = Auth::user();

        // Format user image URL
        $user->img = asset($user->img);

        // Get all roles with their permissions
        $roles = $user->roles->map(function ($role) {
            return [
                'id'          => $role->id,
                'name'        => $role->name,
                'permissions' => $role->permissions->map(function ($permission) {
                    return [
                        'id'   => $permission->id,
                        'name' => $permission->name,
                    ];
                }),
            ];
        });

        // Collect all permissions from all roles without duplicates
        $allPermissions = $user->roles
            ->flatMap(function ($role) {
                return $role->permissions;
            })
            ->unique('id') // Remove duplicate permissions by ID
            ->values()     // Reindex the collection
            ->map(function ($permission) {
                return [
                    'id'   => $permission->id,
                    'name' => $permission->name,
                ];
            });

        // Prepare response data
        $userData = [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'img'         => $user->img,
            'roles'       => $roles,
            'permissions' => $allPermissions,
        ];

        return HelperFunc::sendResponse(200, 'User profile retrieved successfully', $userData);
    }

    public function changePassword(Request $request)
    {
        try {
            // Validate the incoming request data
            $request->validate([
                'current_password' => 'required',
                'new_password'     => 'required|min:8|confirmed', // Ensure password confirmation
            ]);

            // Get the authenticated user
            $user = User::findOrFail($request->id);

            // Verify the current password
            if (! Hash::check($request->current_password, $user->password)) {
                return HelperFunc::sendResponse(400, 'Current password is incorrect.');
            }

            // Update the password
            $user->password = Hash::make($request->new_password);
            $user->save();

            return HelperFunc::sendResponse(200, 'Password changed successfully.');
        } catch (\Exception $e) {
            return HelperFunc::sendResponse(500, 'An error occurred: ' . $e->getMessage());
        }
    }
    public function acceptCompany(Request $request, $company_id)
    {
        // Validate inputs using Validator
        $validator = Validator::make($request->all(), [
            'exp_date' => 'required',
        ]);

        // Check for validation errors
        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }
        // Find the user by company ID
        $user = User::findOrFail($company_id);

        // Check if the user has associated company details
        if ($user->companyDetails) {

            // Set the success status to true (1)
            $user->companyDetails->sucsses  = 1;
            $user->companyDetails->exp_date = $request->exp_date;

            // Save the changes to the company details
            $user->companyDetails->save();
        } else {
            // If no company details are found, return an error response
            return HelperFunc::sendResponse(403, ['message' => 'Company details not found']);
        }

        // Return a success response
        return HelperFunc::sendResponse(200, ['message' => 'Company accepted successfully'], []);
    }
    public function statusCompany($company_id, $status)
    {
        // Find the user by company ID
        $user = User::findOrFail($company_id);

        $user->status = $status;

        // Save the changes to the company details
        $user->save();

        // Return a success response
        return HelperFunc::sendResponse(200, ['message' => ' successfully'], ['user' => $user]);
    }
    public function banCompany($company_id, $ban)
    {
        // Find the user by company ID
        $user = User::findOrFail($company_id);

        // Set the success status to true (1
        $user->ban = $ban;

        $user->save();

        // Return a success response
        return HelperFunc::sendResponse(200, ['message' => 'Company accepted successfully'], ['user' => $user]);
    }

    public function getPendingCompanies(Request $request)
    {
        // Retrieve companies with role 'company' and sucsses = 0, including companyDetails
        $query = User::where('role', 'company')->whereHas('companyDetails', function ($query) {
            $query->where('sucsses', 0);
        })->with('companyDetails');

                                                                               // Paginate the query results
        $paginatedCompanies = $query->paginate($request->get('per_page', 10)); // Use per_page parameter from the request (defaults to 10)

        // Prepare the resource collection for pending companies
        $companiesResource = CompanyResource::collection($paginatedCompanies);

        // Use the pagination function from HelperFunc (passing the pagination data and resource collection)
        return HelperFunc::pagination($paginatedCompanies, $companiesResource);
    }

    public function getsucsessCompanies(Request $request)
    {
        // Retrieve companies with role 'company' and sucsses = 0, including companyDetails
        $query = User::where('role', 'company')->whereHas('companyDetails', function ($query) {
            $query->where('sucsses', 1);
        })->with('companyDetails'); // Don't apply paginate here directly

                                                                               // Paginate the query results
        $paginatedCompanies = $query->paginate($request->get('per_page', 10)); // Use per_page parameter from the request (defaults to 10)

        // Prepare the resource collection for pending companies
        $companiesResource = CompanyResource::collection($paginatedCompanies);

        // Use the pagination function from HelperFunc (passing the pagination data and resource collection)
        return HelperFunc::pagination($paginatedCompanies, $companiesResource);

    }

    public function getUserAdmin(Request $request)
    {
        // جلب المستخدمين الذين لديهم الدور "admin" مع علاقة roles
        $query = User::where('role', 'admin')->with('roles');

        // تنفيذ الـ pagination
        $paginatedAdmins = $query->paginate($request->get('per_page', 10));

        // تحويل البيانات لتنسيق معين حسب الحاجة (هنا نستخدم map داخل الpagination items)
        $data = $paginatedAdmins->getCollection()->map(function ($user) {
            return [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'created_at' => $user->created_at,
                'roles'      => $user->roles->map(fn($role) => [
                    'id'   => $role->id,
                    'name' => $role->name,
                ]),
            ];
        });

        // إرسال البيانات باستخدام الـ HelperFunc
        return HelperFunc::pagination($paginatedAdmins, $data);
    }

    public function getCompany($company_id)
    {
        // البحث عن الشركة مع التفاصيل المرتبطة بها
        $company = User::whereHas('companyDetails')->with('companyDetails')->findOrFail($company_id);

        return HelperFunc::apiResponse(true, 200, new CompanyResource($company));
    }
    public function destroy($user_id)
    {
        // Find the user or return 404 if not found
        $user = User::findOrFail($user_id);

        // Delete user image if it exists
        if ($user->img) {
            HelperFunc::deleteFile(public_path($user->img));
        }

        // Detach all roles associated with the user (if using Spatie Laravel Permissions)
        $user->roles()->detach();

        // Delete the user
        $user->delete();

        // Return a success response
        return HelperFunc::sendResponse(200, "User deleted successfully");
    }

    public function updateAvailableNotification(Request $request)
    {
        $request->validate([
            'available_notification' => 'required|boolean',
        ]);

        $user = auth()->user();

        $user->available_notification = $request->available_notification;
        $user->save();

        return HelperFunc::sendResponse(200, "Notification setting updated successfully.", $user);
    }

}
