<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Workspace-level roles ---
        Role::firstOrCreate(['name' => 'workspace_owner', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'workspace_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'workspace_member', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'workspace_guest', 'guard_name' => 'web']);

        // --- Project-level roles (stored in project_members.role — NOT Spatie) ---
        // These roles exist in Spatie only for legacy compatibility.
        // Access control for projects is handled by ProjectPolicy reading project_members.role directly.
        Role::firstOrCreate(['name' => 'project_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'editor',        'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'commenter',     'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'viewer',        'guard_name' => 'web']);
    }
}
