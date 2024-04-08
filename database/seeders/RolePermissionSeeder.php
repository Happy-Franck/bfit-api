<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        Role::create(['name' => 'administrateur']);
        Role::create(['name' => 'coach']);
        Role::create(['name' => 'challenger']);

        //admin
        Permission::create(['name' => 'manage rooms']);
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'manage products']);
        //coach
        Permission::create(['name' => 'manage trainings']);
        Permission::create(['name' => 'manage historique']);
        //challenger
        Permission::create(['name' => 'manage order']);
        Permission::create(['name' => 'manage abrupts']);
        Permission::create(['name' => 'manage comments']);
    }
}
