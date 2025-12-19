<?php
namespace Database\Seeders;

use App\Models\CustomPermission;
use App\Models\CustomRole;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ModuleRoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('module_permissions')->truncate();
        DB::table('role_module_permissions')->truncate();
        DB::table('permissions')->truncate();

        DB::table('modules')->truncate();

        // 1. Create Permissions
        $adminRole = CustomRole::firstOrCreate(['name' => "Super Admin", 'guard_name' => 'api']);

        $permission_data = [
            'approve',
            'assign',
            'change',
            'create',
            'delete',
            'detail',
            'disable',
            'download',
            'edit',
            'enable',
            'export',
            'generate',
            'import',
            'publish',
            'reject',
            'reset',
            'upload',
            'view',
        ];
        DB::table('permissions')->insert(
            collect($permission_data)->map(fn($name) => ['name' => $name])->toArray()
        );

        // 2. Insert Modules, Permissions and assign to role
        $data = [
            [
                'name'     => 'Employee Management',
                'children' => [
                    [
                        'name'        => 'employee',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'assign', 'import', 'export'],
                    ],
                    [
                        'name'        => 'employee-group',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
                    ],
                    [
                        'name'        => 'department',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'assign', 'import', 'export'],
                    ],
                    [
                        'name'        => 'branches',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
                    ],
                    [
                        'name'        => 'locations',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
                    ],
                    [
                        'name'        => 'designations',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
                    ],
                    [
                        'name'        => 'group list tree',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    [
                        'name'        => 'department tree',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                ],
            ],
            [
                'name'     => 'Leave Management',
                'children' => [
                    [
                        'name'        => 'leave request',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'approve', 'reject', 'export'],
                    ],
                    [
                        'name'        => 'leave-balance',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'assign', 'export'],
                    ],
                    [
                        'name'        => 'leave-type-configuration',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'assign', 'export'],
                    ],
                    [
                        'name'        => 'leave-types',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'assign', 'export'],
                    ],
                ],
            ],
            [
                'name'     => 'Time & Attendance',
                'children' => [
                    [
                        'name'        => 'attendance',
                        'permissions' => ['view', 'edit', 'import', 'export'],
                    ],
                    [
                        'name'        => 'late request',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'export'],
                    ],
                    // [
                    //     'name'        => 'monitoring',
                    //     'permissions' => ['view'],
                    // ],
                    [
                        'name'        => 'duty roster template',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    [
                        'name'        => 'duty roster',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'export'],
                    ],
                    [
                        'name'        => 'holidays',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'assign', 'import', 'export'],
                    ],
                    [
                        'name'        => 'shift',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'assign', 'export'],
                    ],
                ],
            ],
            [
                'name'     => 'HR Processes',
                'children' => [
                    [
                        'name'        => 'checklist',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    [
                        'name'        => 'offboarding',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                ],
            ],
            [
                'name'     => 'Claims',
                'children' => [
                    [
                        'name'        => 'claim type',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    [
                        'name'        => 'claim',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'approve', 'reject'],
                    ],
                ],
            ],
            [
                'name'     => 'Payroll Management',
                'children' => [
                    [
                        'name'        => 'payroll-component',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    [
                        'name'        => 'payroll-policy',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    [
                        'name'        => 'payslip-designer',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'assign'],
                    ],
                    [
                        'name'        => 'mapping-settings',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    [
                        'name'        => 'orso-schema',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    [
                        'name'        => 'mpf-schema',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    // [
                    //     'name'        => 'adw-opening-balance',
                    //     'permissions' => ['view', 'create', 'edit', 'delete'],
                    // ],
                    [
                        'name'        => 'payroll-listing',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'export'],
                    ],
                    [
                        'name'        => 'payroll-definition',
                        'permissions' => ['view', 'edit', 'export'],
                    ],
                    [
                        'name'        => 'tax-calculation',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    [
                        'name'        => 'enrollment',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    [
                        'name'        => 'adw-settings',
                        'permissions' => ['view', 'edit'],
                    ],
                    [
                        'name'        => 'filing-management',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'export'],
                    ],
                ],
            ],
            [
                'name'     => 'Documents',
                'children' => [
                    [
                        'name'        => 'documents',
                        'permissions' => ['view', 'upload', 'download', 'delete'],
                    ],
                ],
            ],
            [
                'name'     => 'Announcement',
                'children' => [
                    [
                        'name'        => 'announcement',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'publish'],
                    ],
                ],
            ],
            [
                'name'     => 'Password',
                'children' => [
                    [
                        'name'        => 'password',
                        'permissions' => ['view', 'change', 'reset'],
                    ],
                ],
            ],
            [
                'name'     => 'Setting',
                'children' => [
                    [
                        'name'        => 'tags',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    [
                        'name'        => 'role and permissions',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    [
                        'name'        => 'activity logs',
                        'permissions' => ['view'],
                    ],
                    [
                        'name'        => 'attributes',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    [
                        'name'        => 'module',
                        'permissions' => ['view', 'enable', 'disable', 'edit'],
                    ],
                    [
                        'name'        => 'folder',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    [
                        'name'        => 'company',
                        'permissions' => ['view', 'edit'],
                    ],
                    [
                        'name'        => 'approval-flow',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                ],
            ],
            [
                'name'     => 'OT Management',
                'children' => [
                    [
                        'name'        => 'overtime-request',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'export'],
                    ],
                    [
                        'name'        => 'overtime-settings',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'export'],
                    ],
                ],
            ],
            [
                'name'     => 'CRM',
                'children' => [
                    [
                        'name'        => 'omnichannel',
                        'permissions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    [
                        'name'        => 'contact',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
                    ],
                    [
                        'name'        => 'company',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
                    ],
                    [
                        'name'        => 'project',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
                    ],
                    [
                        'name'        => 'task',
                        'permissions' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
                    ],
                ],
            ],
        ];

        foreach ($data as $parent) {
            $parentModule = Module::create([
                'name'             => $parent['name'],
                'slug'             => Str::slug($parent['name']),
                'description'      => null,
                'parent_module_id' => null,
            ]);

            foreach ($parent['children'] as $child) {
                $module = Module::create([
                    'name'             => $child['name'],
                    'slug'             => Str::slug($child['name']),
                    'description'      => null,
                    'parent_module_id' => $parentModule->id,
                ]);

                $module_permissions = collect($child['permissions'])->map(function ($permission_name) use ($module) {
                    $permission = CustomPermission::where('name', $permission_name)->first();

                    if ($permission) {
                        return [
                            'permission_id' => $permission->id,
                            'module_id'     => $module->id,
                        ];
                    }
                });

                DB::table('module_permissions')->insert($module_permissions->toArray());

                $role_module_permissions = collect($module_permissions)->map(function ($module_permission) use ($adminRole, $module) {
                    return [
                        'role_id'       => $adminRole->id,
                        'permission_id' => $module_permission['permission_id'],
                        'module_id'     => $module_permission['module_id'],
                    ];
                });

                DB::table('role_module_permissions')->insert($role_module_permissions->toArray());
            }
        }

        $users = User::all();

        foreach ($users as $key => $user) {
            $user->assignRole($adminRole);
        }
    }
}
