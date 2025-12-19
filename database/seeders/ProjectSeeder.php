<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Seed the projects table.
     */
    public function run(): void
    {
        // Get users by role
        $admin = User::where('role', 'admin')->first();
        $superadmin = User::where('role', 'superadmin')->first();
        $incharges = User::where('role', 'incharge')->get();
        $users = User::where('role', 'user')->get();

        // Project 1: Website Redesign
        $project1 = Project::create([
            'name' => 'Website Redesign',
            'description' => 'Complete overhaul of the company website with modern design, improved UX, and mobile responsiveness. Includes new branding guidelines and integration with CMS.',
            'status' => 'active',
            'start_date' => now()->subDays(30),
            'end_date' => now()->addDays(60),
            'created_by' => $admin->id,
        ]);
        // Assign incharges and members
        $project1->users()->attach($incharges[0]->id, ['role' => 'incharge']);
        $project1->users()->attach($incharges[1]->id, ['role' => 'incharge']);
        $project1->users()->attach($users[0]->id, ['role' => 'member']);
        $project1->users()->attach($users[1]->id, ['role' => 'member']);
        $project1->users()->attach($users[2]->id, ['role' => 'member']);

        // Project 2: Mobile App Development
        $project2 = Project::create([
            'name' => 'Mobile App Development',
            'description' => 'Development of a cross-platform mobile application for iOS and Android. Features include user authentication, push notifications, and offline support.',
            'status' => 'active',
            'start_date' => now()->subDays(15),
            'end_date' => now()->addDays(90),
            'created_by' => $superadmin->id,
        ]);
        $project2->users()->attach($incharges[1]->id, ['role' => 'incharge']);
        $project2->users()->attach($users[3]->id, ['role' => 'member']);
        $project2->users()->attach($users[4]->id, ['role' => 'member']);
        $project2->users()->attach($users[5]->id, ['role' => 'member']);

        // Project 3: E-commerce Platform
        $project3 = Project::create([
            'name' => 'E-commerce Platform',
            'description' => 'Building a full-featured e-commerce platform with product management, shopping cart, payment integration, and order tracking functionality.',
            'status' => 'active',
            'start_date' => now()->subDays(45),
            'end_date' => now()->addDays(30),
            'created_by' => $admin->id,
        ]);
        $project3->users()->attach($incharges[0]->id, ['role' => 'incharge']);
        $project3->users()->attach($incharges[2]->id, ['role' => 'incharge']);
        $project3->users()->attach($users[0]->id, ['role' => 'member']);
        $project3->users()->attach($users[2]->id, ['role' => 'member']);
        $project3->users()->attach($users[4]->id, ['role' => 'member']);
        $project3->users()->attach($users[6]->id, ['role' => 'member']);

        // Project 4: API Integration
        $project4 = Project::create([
            'name' => 'API Integration Hub',
            'description' => 'Creating a centralized API integration hub to connect with third-party services including payment gateways, shipping providers, and CRM systems.',
            'status' => 'on_hold',
            'start_date' => now()->subDays(60),
            'end_date' => now()->addDays(15),
            'created_by' => $admin->id,
        ]);
        $project4->users()->attach($incharges[2]->id, ['role' => 'incharge']);
        $project4->users()->attach($users[1]->id, ['role' => 'member']);
        $project4->users()->attach($users[3]->id, ['role' => 'member']);

        // Project 5: Data Analytics Dashboard
        $project5 = Project::create([
            'name' => 'Data Analytics Dashboard',
            'description' => 'Building an interactive analytics dashboard with real-time data visualization, custom reports, and KPI tracking for business intelligence.',
            'status' => 'active',
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(45),
            'created_by' => $superadmin->id,
        ]);
        $project5->users()->attach($incharges[0]->id, ['role' => 'incharge']);
        $project5->users()->attach($users[5]->id, ['role' => 'member']);
        $project5->users()->attach($users[6]->id, ['role' => 'member']);

        // Project 6: Customer Portal (Completed)
        $project6 = Project::create([
            'name' => 'Customer Portal',
            'description' => 'A self-service customer portal for account management, support tickets, and document sharing. Successfully delivered ahead of schedule.',
            'status' => 'completed',
            'start_date' => now()->subDays(90),
            'end_date' => now()->subDays(10),
            'created_by' => $admin->id,
        ]);
        $project6->users()->attach($incharges[1]->id, ['role' => 'incharge']);
        $project6->users()->attach($users[0]->id, ['role' => 'member']);
        $project6->users()->attach($users[1]->id, ['role' => 'member']);

        // Project 7: Security Audit
        $project7 = Project::create([
            'name' => 'Security Audit & Compliance',
            'description' => 'Comprehensive security audit of all systems, implementation of security best practices, and ensuring compliance with industry standards.',
            'status' => 'active',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(25),
            'created_by' => $superadmin->id,
        ]);
        $project7->users()->attach($incharges[2]->id, ['role' => 'incharge']);
        $project7->users()->attach($users[2]->id, ['role' => 'member']);
        $project7->users()->attach($users[4]->id, ['role' => 'member']);

        $this->command->info('Projects seeded successfully! Created 7 projects with team assignments.');
    }
}

