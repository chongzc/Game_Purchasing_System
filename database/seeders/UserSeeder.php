<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'u_name' => 'Admin User',
            'u_email' => 'admin@example.com',
            'u_password' => Hash::make('password123'),
            'u_role' => 'admin',
            'u_birthdate' => '1990-01-01',
        ]);

        // Create developer users
        $developers = [
            [
                'u_name' => 'Naughty Dog Dev',
                'u_email' => 'naughtydog@dev.com',
                'u_birthdate' => '1985-03-15',
            ],
            [
                'u_name' => 'FromSoftware Dev',
                'u_email' => 'fromsoftware@dev.com',
                'u_birthdate' => '1988-07-22',
            ],
            [
                'u_name' => 'Rockstar Dev',
                'u_email' => 'rockstar@dev.com',
                'u_birthdate' => '1982-11-08',
            ]
        ];

        foreach ($developers as $dev) {
            User::create([
                'u_name' => $dev['u_name'],
                'u_email' => $dev['u_email'],
                'u_password' => Hash::make('dev123456'),
                'u_role' => 'developer',
                'u_birthdate' => $dev['u_birthdate'],
            ]);
        }

        // Create regular users
        $users = [
            [
                'u_name' => 'John Doe',
                'u_email' => 'john@example.com',
                'u_birthdate' => '1995-05-20',
            ],
            [
                'u_name' => 'Jane Smith',
                'u_email' => 'jane@example.com',
                'u_birthdate' => '1998-09-12',
            ],
            [
                'u_name' => 'Mike Johnson',
                'u_email' => 'mike@example.com',
                'u_birthdate' => '1992-12-30',
            ]
        ];

        foreach ($users as $user) {
            User::create([
                'u_name' => $user['u_name'],
                'u_email' => $user['u_email'],
                'u_password' => Hash::make('user123456'),
                'u_role' => 'user',
                'u_birthdate' => $user['u_birthdate'],
            ]);
        }
    }
}
