<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use Illuminate\Http\Request as Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleController extends BaseController
{

    public function __construct()
    {
        $this->middleware('can:admin.roles', ['only' => ['index']]);
        $this->middleware('can:admin.roles.permissions', ['only' => ['permissions']]);
        $this->middleware('can:admin.roles.permissions.create', ['only' => ['permissions_create']]);
        $this->middleware('can:admin.roles.create', ['only' => ['create', 'index']]);
        $this->middleware('can:admin.roles.edit', ['only' => ['update', 'index']]);
        $this->middleware('can:admin.roles.delete', ['only' => ['destroy', 'index']]);
    }
    public function index()
    {
        $roles = Role::select('id', 'name')
            ->get();
        return HelperFunc::sendResponse(200, 'done', $roles);
    }
    public function permissions()
    {
        $roles = Permission::select('id', 'name')
            ->get();
        return HelperFunc::sendResponse(200, 'done', $roles);
    }
    public function update(Request $request, $id)
    {
        Validator::make($request->all(), [
            'name'           => 'string|max:255|nullable',
            'permissions'    => 'array',
            'permissions .*' => 'exists:permissions,id',
        ]);

        $role = Role::find($id);

        if (! $role) {
            return HelperFunc::sendResponse(422, 'Role not found', []);
        }

        if ($request->name) {
            $role->name = $request->name;
            $role->save();
        }

        $role->syncPermissions($request->permissions);
        return HelperFunc::sendResponse(200, 'Role updated successfully', $role);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }
        // Create a new role...
        $role = Role::create([
            'name'       => $request->name,
            'guard_name' => 'web', // or 'api'
        ]);

        // Assign the created role to the user
        $role->syncPermissions($request->permissions);

        return HelperFunc::apiResponse(true, 200, $role);
    }
    public function permissions_create(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
        }
        // Create a new permission...
        $permission = Permission::create([
            'name' => $request->name,
        ]);

        return HelperFunc::apiResponse(true, 200, $permission);
    }
    public function destroy($id)
    {
        $role = Role::find($id);

        if (! $role) {
            return HelperFunc::sendResponse(404, 'Role not found', []);
        }
        // ✅ استخدم delete() بدلاً من deleted()
        $role->delete();

        return HelperFunc::sendResponse(200, 'Role deleted successfully', []);
    }

    public function getSingeleRole($roleId)
    {
        $role = Role::find($roleId)->load('permissions');
        if (! $role) {
            return HelperFunc::sendResponse(404, 'Role not found', []);
        }
        return HelperFunc::sendResponse(200, 'Role fetched successfully', $role);
    }
}