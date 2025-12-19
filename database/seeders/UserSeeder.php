<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the users table.
     */
    public function run(): void
    {
        // Create superadmin user
        User::create([
            'username' => 'superadmin',
            'first_name' => 'Super',
            'middle_name' => null,
            'last_name' => 'Administrator',
            'email' => 'superadmin@email.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);

        // Create admin user
        User::create([
            'username' => 'admin',
            'first_name' => 'System',
            'middle_name' => null,
            'last_name' => 'Administrator',
            'email' => 'admin@email.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create incharge users
        User::create([
            'username' => 'john.smith',
            'first_name' => 'John',
            'middle_name' => 'Michael',
            'last_name' => 'Smith',
            'email' => 'john@email.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'incharge',
        ]);

        User::create([
            'username' => 'sarah.johnson',
            'first_name' => 'Sarah',
            'middle_name' => null,
            'last_name' => 'Johnson',
            'email' => 'sarah@email.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'incharge',
        ]);

        User::create([
            'username' => 'david.lee',
            'first_name' => 'David',
            'middle_name' => 'Andrew',
            'last_name' => 'Lee',
            'email' => 'david@email.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'incharge',
        ]);

        // Create regular users
        User::create([
            'username' => 'mike.williams',
            'first_name' => 'Mike',
            'middle_name' => 'David',
            'last_name' => 'Williams',
            'email' => 'mike@email.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        User::create([
            'username' => 'emily.brown',
            'first_name' => 'Emily',
            'middle_name' => null,
            'last_name' => 'Brown',
            'email' => 'emily@email.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        User::create([
            'username' => 'james.davis',
            'first_name' => 'James',
            'middle_name' => 'Robert',
            'last_name' => 'Davis',
            'email' => 'james@email.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        User::create([
            'username' => 'lisa.garcia',
            'first_name' => 'Lisa',
            'middle_name' => 'Marie',
            'last_name' => 'Garcia',
            'email' => 'lisa@email.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        User::create([
            'username' => 'robert.martinez',
            'first_name' => 'Robert',
            'middle_name' => null,
            'last_name' => 'Martinez',
            'email' => 'robert@email.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        User::create([
            'username' => 'jennifer.wilson',
            'first_name' => 'Jennifer',
            'middle_name' => 'Anne',
            'last_name' => 'Wilson',
            'email' => 'jennifer@email.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        User::create([
            'username' => 'chris.taylor',
            'first_name' => 'Christopher',
            'middle_name' => null,
            'last_name' => 'Taylor',
            'email' => 'chris@email.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $this->command->info('Users seeded successfully!');
        $this->command->info('  - Superadmin: superadmin@email.com / password');
        $this->command->info('  - Admin: admin@email.com / password');
        $this->command->info('  - Incharges: john@email.com, sarah@email.com, david@email.com / password');
        $this->command->info('  - Users: mike@email.com, emily@email.com, james@email.com, etc. / password');
    }
}

