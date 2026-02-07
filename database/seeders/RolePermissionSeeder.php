<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos por módulo
        $permissions = [
            // Clientes
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            'approve customers',
            
            // Proveedores
            'view suppliers',
            'create suppliers',
            'edit suppliers',
            'delete suppliers',
            'approve suppliers',
            
            // Transportistas
            'view transporters',
            'create transporters',
            'edit transporters',
            'delete transporters',
            'approve transporters',
            
            // Entregas
            'view deliveries',
            'create deliveries',
            'edit deliveries',
            'delete deliveries',
            'approve deliveries',
            'confirm deliveries',
            
            // Órdenes de Venta
            'view sales orders',
            'create sales orders',
            'edit sales orders',
            'delete sales orders',
            'approve sales orders',
            
            // Órdenes de Compra
            'view purchase orders',
            'create purchase orders',
            'edit purchase orders',
            'delete purchase orders',
            'approve purchase orders',
            
            // Pagos
            'view payments',
            'create payments',
            'edit payments',
            'delete payments',
            'approve payments',
            'settle payments',
            
            // Pagos a Transportistas
            'view transporter payments',
            'create transporter payments',
            'edit transporter payments',
            'approve transporter payments',
            'settle transporter payments',
            
            // Reportes
            'view reports',
            'export reports',
            
            // Administración
            'manage users',
            'manage roles',
            'view dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles
        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());

        $supervisorRole = Role::create(['name' => 'Supervisor']);
        $supervisorRole->givePermissionTo([
            'view customers',
            'create customers',
            'edit customers',
            'view suppliers',
            'view transporters',
            'view deliveries',
            'create deliveries',
            'edit deliveries',
            'approve deliveries',
            'view sales orders',
            'create sales orders',
            'edit sales orders',
            'approve sales orders',
            'view purchase orders',
            'view payments',
            'view transporter payments',
            'view reports',
            'view dashboard',
        ]);

        $userRole = Role::create(['name' => 'User']);
        $userRole->givePermissionTo([
            'view customers',
            'view suppliers',
            'view transporters',
            'view deliveries',
            'create deliveries',
            'view sales orders',
            'create sales orders',
            'view purchase orders',
            'view payments',
            'view dashboard',
        ]);

        $cashierRole = Role::create(['name' => 'Cashier']);
        $cashierRole->givePermissionTo([
            'view customers',
            'view payments',
            'create payments',
            'edit payments',
            'view transporter payments',
            'create transporter payments',
            'view reports',
            'view dashboard',
        ]);
    }
}
