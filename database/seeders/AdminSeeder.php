<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Suliman',
            'phone' => '0947858738',
            'password' => bcrypt('12341234'), // كلمة المرور
            'role' => 'admin', // تحديد المستخدم كأدمن
        ]);
    }
}
