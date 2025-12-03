<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'admin.dashboard',
            'admin.users',
            'admin.config',
            'admin.users.create',
            'admin.users.edit',
            'admin.users.delete',
            'admin.roles',
            'admin.roles.create',
            'admin.roles.edit',
            'admin.roles.delete',
            'admin.roles.permissions',
            'admin.roles.permissions.create',
            'admin.logs',
            'companies',
            'companies Partners',
            'Active companies Partners',
            'Actions companies Partners',
            'Update Actions companies Partners',
            'Approved Actions companies Partners',
            'Show File Actions companies Partners',
            'Send Email Actions companies Partners',
            'Show Wallet Actions companies Partners',
            'Show Review Actions companies Partners',
            'companies Applications',
            'Actions companies Applications',
            'Send Approved Actions companies Applications',
            'companies Activity',
            'Offers Tab',
            'Offers',
            'Offers Filtered',
            'Offers Show',
            'Offers Status',
            'Offers Types',
            'Offers Types Add',
            'Offers Types Edit',
            'Offers Create',
            'Offers Fack',
            'finance Tap',
            'finance Filter',
            'finance Approve',
            'finance Cancel',
            'Visitors',
            'Shop',
            'Shop Filter',
            'Shop Show',
            'Complaints',
            'Complaints Approved',
            'Complaints Rejected',
            'Coupons',
            'User Managemant',
            'User Managemant Actions',
            'User Managemant Ban',
            'User Managemant Deleted',
            'User Managemant Edite',
            'Contact Us Tap',
            'Reviews Tap',
            'Reviews company',
            'Reviews company Actions',
            'Reviews company Actions Delete',
            'Reviews company Actions Update',
            'Reviews company Actions Update Status',
            'Reviews parteners',
            'Reviews parteners Actions',
            'Reviews parteners Actions Delete',
            'Reviews parteners Actions Update',
            'Reviews parteners Actions Update Status',
            'Remove Reviews',
            'WebsiteTap',
            'Website About Page Show',
            'Website About Page update',
            'Website About Page Show content',
            'Website About Page Show Seo',
            'Website home Page Show',
            'Website home Page update',
            'Website home Page Show content',
            'Website home Page Show Seo',
            'Website Blogs update',
            'Website Blogs show',
            'Website Blogs delete',
            'Website Blogs store',
            'Website Blogs Update Status',
            'Website FAqs Page Show',
            'Website FAqs Page update',
            'Website FAqs Page Show content',
            'Website FAqs Page Show Seo',
            'Website Imprit Page Show',
            'Website Imprit Page update',
            'Website Imprit Page Show content',
            'Website Imprit Page Show Seo',
            'Website Companies Page Show',
            'Website Companies Page update',
            'Website Companies Page Show content',
            'Website Companies Page Show Seo',
            'Website Privacy Page Show',
            'Website Privacy Page update',
            'Website Privacy Page Show content',
            'Website Privacy Page Show Seo',
            'Website Terms Page Show',
            'Website Terms Page update',
            'Website Terms Page Show content',
            'Website Terms Page Show Seo',
            'Website Model Offers Page Show',
            'Website Model Offers Page update',
            'Website Paetner Page Show',
            'Website Paetner Page update',
            'Website Paetner Page Show content',
            'Website Paetner Page Show Seo',
            'Website Contact As Page Show',
            'Website Contact As Page update',
            'Website Contact As Page Show content',
            'Website Contact As Page Show Seo',
            'Website Blog Page Show',
            'Website Blog Page update',
            'Website Blog Page Show content',
            'Website Blog Page Show Seo',
            'Website Services Page Show',
            'Website Services Page update',
            'Website Services Page Show content',
            'Website Services Page Show Seo',
            'Website Nav And Footer Page Show',
            'Website Nav And Footer Page update',
            'Website Form Page Show',
            'Website Foem Page update',
        ];

        foreach ($permissions as $permission) {
            if (! Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission]);
            }
        }

        $superAdminRole = Role::where('name', 'Super Admin')->first();

        if ($superAdminRole) {
            $superAdminPermissions = $superAdminRole->permissions()->pluck('name')->toArray();
            $missingPermissions    = array_diff($permissions, $superAdminPermissions);

            if (! empty($missingPermissions)) {
                $superAdminRole->givePermissionTo($missingPermissions);
            }
        }

        $adminRole = Role::find(2);
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::pluck('id'));
        }

        $adminUser = User::where('role', 'admin')->first();
        if ($adminUser && ! $adminUser->hasRole($adminRole->name)) {
            $adminUser->assignRole($adminRole);
        }
    }
}
