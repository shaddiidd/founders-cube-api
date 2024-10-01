<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = new User;
        $admin->full_name = 'Admin';
        $admin->email = 'abdullah.shadid49@gmail.com';
        $admin->bio = null;
        $admin->industry = 'Education & E-Learning';
        $admin->password = bcrypt('superuser');
        $admin->user_type = 'admin';
        $admin->active = false;
        $admin->verified = false;
        $admin->special_member = false;
        $admin->phone_number = '+962799999999';
        $admin->url = 'admin.admin';
        $admin->save();
        // $admin = new User;
        // $admin->full_name = 'Sari Awwad';
        // $admin->email = 'sari.awwad@gmail.com';
        // $admin->bio = null;
        // $admin->industry = 'Education & E-Learning';
        // $admin->password = bcrypt('sari1234');
        // $admin->user_type = 'admin';
        // $admin->active = true;
        // $admin->verified = true;
        // $admin->special_member = true;
        // $admin->phone_number = '+962799936277';
        // $admin->url = 'sariawwad.com';
        // $admin->save();
    }
}
