<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Seed the tasks table.
     */
    public function run(): void
    {
        $projects = Project::with('users')->get();
        $admin = User::where('role', 'admin')->first();

        foreach ($projects as $project) {
            $incharges = $project->users->filter(fn($u) => $u->pivot->role === 'incharge');
            $members = $project->users->filter(fn($u) => $u->pivot->role === 'member');
            
            if ($incharges->isEmpty() || $members->isEmpty()) {
                continue;
            }

            $primaryIncharge = $incharges->first();
            $tasks = $this->getTasksForProject($project->name);

            foreach ($tasks as $index => $taskData) {
                $task = Task::create([
                    'project_id' => $project->id,
                    'title' => $taskData['title'],
                    'description' => $taskData['description'],
                    'status' => $taskData['status'],
                    'priority' => $taskData['priority'],
                    'progress' => $taskData['progress'],
                    'due_date' => $this->generateDueDate($taskData['status'], $project),
                    'created_by' => $primaryIncharge->id,
                    'assigned_by' => $primaryIncharge->id,
                ]);

                // Assign task to random members
                $assigneeCount = min(rand(1, 2), $members->count());
                $assignees = $members->random($assigneeCount);
                
                foreach ($assignees as $assignee) {
                    $task->assignees()->attach($assignee->id, [
                        'assigned_by' => $primaryIncharge->id,
                    ]);
                }

                // Add comments to some tasks
                if (rand(0, 1) === 1 && $taskData['status'] !== 'pending') {
                    $this->addCommentsToTask($task, $assignees, $incharges);
                }
            }
        }

        $this->command->info('Tasks seeded successfully! Created tasks with assignments and comments.');
    }

    /**
     * Get tasks based on project name.
     */
    private function getTasksForProject(string $projectName): array
    {
        $taskSets = [
            'Website Redesign' => [
                ['title' => 'Create wireframes for homepage', 'description' => 'Design low-fidelity wireframes for the new homepage layout including hero section, features, and footer.', 'status' => 'completed', 'priority' => 'high', 'progress' => 100],
                ['title' => 'Design mobile navigation', 'description' => 'Create responsive mobile navigation with hamburger menu and smooth animations.', 'status' => 'completed', 'priority' => 'high', 'progress' => 100],
                ['title' => 'Implement new color scheme', 'description' => 'Apply the approved brand colors across all pages and components.', 'status' => 'in_progress', 'priority' => 'medium', 'progress' => 65],
                ['title' => 'Optimize images for web', 'description' => 'Compress and convert all images to WebP format for better performance.', 'status' => 'in_progress', 'priority' => 'medium', 'progress' => 40],
                ['title' => 'Build contact form component', 'description' => 'Create a reusable contact form with validation and reCAPTCHA integration.', 'status' => 'pending', 'priority' => 'low', 'progress' => 0],
                ['title' => 'Set up CMS integration', 'description' => 'Integrate headless CMS for content management by marketing team.', 'status' => 'pending', 'priority' => 'high', 'progress' => 0],
                ['title' => 'Cross-browser testing', 'description' => 'Test website on Chrome, Firefox, Safari, and Edge browsers.', 'status' => 'pending', 'priority' => 'medium', 'progress' => 0],
            ],
            'Mobile App Development' => [
                ['title' => 'Set up React Native project', 'description' => 'Initialize the project with TypeScript, navigation, and state management.', 'status' => 'completed', 'priority' => 'urgent', 'progress' => 100],
                ['title' => 'Implement authentication flow', 'description' => 'Create login, registration, and password reset screens with API integration.', 'status' => 'in_progress', 'priority' => 'high', 'progress' => 75],
                ['title' => 'Design app icon and splash screen', 'description' => 'Create app icon for both platforms and animated splash screen.', 'status' => 'in_progress', 'priority' => 'medium', 'progress' => 50],
                ['title' => 'Build push notification system', 'description' => 'Integrate Firebase Cloud Messaging for push notifications.', 'status' => 'pending', 'priority' => 'high', 'progress' => 0],
                ['title' => 'Create offline data sync', 'description' => 'Implement local storage and background sync for offline functionality.', 'status' => 'pending', 'priority' => 'medium', 'progress' => 0],
                ['title' => 'App Store preparation', 'description' => 'Prepare screenshots, descriptions, and metadata for store submission.', 'status' => 'pending', 'priority' => 'low', 'progress' => 0],
            ],
            'E-commerce Platform' => [
                ['title' => 'Set up product database schema', 'description' => 'Design and implement the database structure for products, categories, and variants.', 'status' => 'completed', 'priority' => 'urgent', 'progress' => 100],
                ['title' => 'Build product listing page', 'description' => 'Create filterable and sortable product listing with pagination.', 'status' => 'completed', 'priority' => 'high', 'progress' => 100],
                ['title' => 'Implement shopping cart', 'description' => 'Build persistent shopping cart with quantity updates and item removal.', 'status' => 'completed', 'priority' => 'urgent', 'progress' => 100],
                ['title' => 'Integrate Stripe payment', 'description' => 'Set up Stripe payment processing with card and digital wallet support.', 'status' => 'in_progress', 'priority' => 'urgent', 'progress' => 80],
                ['title' => 'Create order management system', 'description' => 'Build admin panel for order processing, status updates, and refunds.', 'status' => 'in_progress', 'priority' => 'high', 'progress' => 45],
                ['title' => 'Build inventory tracking', 'description' => 'Implement stock management with low inventory alerts.', 'status' => 'under_review', 'priority' => 'medium', 'progress' => 90],
                ['title' => 'Set up email notifications', 'description' => 'Create transactional emails for order confirmation, shipping, and delivery.', 'status' => 'pending', 'priority' => 'medium', 'progress' => 0],
                ['title' => 'Implement product reviews', 'description' => 'Build review and rating system with moderation tools.', 'status' => 'pending', 'priority' => 'low', 'progress' => 0],
            ],
            'API Integration Hub' => [
                ['title' => 'Design API architecture', 'description' => 'Create architectural documentation for the integration hub.', 'status' => 'completed', 'priority' => 'high', 'progress' => 100],
                ['title' => 'Build authentication middleware', 'description' => 'Implement OAuth2 and API key authentication for third-party access.', 'status' => 'in_progress', 'priority' => 'high', 'progress' => 60],
                ['title' => 'Create webhook system', 'description' => 'Build webhook delivery system with retry logic and logging.', 'status' => 'pending', 'priority' => 'medium', 'progress' => 0],
                ['title' => 'Integrate shipping APIs', 'description' => 'Connect with FedEx, UPS, and USPS shipping APIs.', 'status' => 'pending', 'priority' => 'high', 'progress' => 0],
            ],
            'Data Analytics Dashboard' => [
                ['title' => 'Set up data pipeline', 'description' => 'Create ETL pipeline for data aggregation from multiple sources.', 'status' => 'in_progress', 'priority' => 'urgent', 'progress' => 70],
                ['title' => 'Design dashboard layouts', 'description' => 'Create mockups for executive, sales, and operations dashboards.', 'status' => 'in_progress', 'priority' => 'high', 'progress' => 55],
                ['title' => 'Build chart components', 'description' => 'Develop reusable chart components using Chart.js or D3.', 'status' => 'pending', 'priority' => 'high', 'progress' => 0],
                ['title' => 'Implement real-time updates', 'description' => 'Add WebSocket support for live data streaming.', 'status' => 'pending', 'priority' => 'medium', 'progress' => 0],
                ['title' => 'Create export functionality', 'description' => 'Allow users to export reports as PDF and Excel files.', 'status' => 'pending', 'priority' => 'low', 'progress' => 0],
            ],
            'Customer Portal' => [
                ['title' => 'Build user dashboard', 'description' => 'Create personalized dashboard with account overview and quick actions.', 'status' => 'completed', 'priority' => 'high', 'progress' => 100],
                ['title' => 'Implement ticket system', 'description' => 'Build support ticket creation and tracking functionality.', 'status' => 'completed', 'priority' => 'high', 'progress' => 100],
                ['title' => 'Create document library', 'description' => 'Build secure document upload and sharing feature.', 'status' => 'completed', 'priority' => 'medium', 'progress' => 100],
                ['title' => 'Add notification preferences', 'description' => 'Let users customize their email and in-app notification settings.', 'status' => 'completed', 'priority' => 'low', 'progress' => 100],
                ['title' => 'QA testing and bug fixes', 'description' => 'Complete testing cycle and resolve all reported issues.', 'status' => 'completed', 'priority' => 'urgent', 'progress' => 100],
            ],
            'Security Audit & Compliance' => [
                ['title' => 'Conduct vulnerability scan', 'description' => 'Run automated security scans on all production systems.', 'status' => 'in_progress', 'priority' => 'urgent', 'progress' => 80],
                ['title' => 'Review access controls', 'description' => 'Audit user permissions and implement principle of least privilege.', 'status' => 'in_progress', 'priority' => 'high', 'progress' => 40],
                ['title' => 'Update security policies', 'description' => 'Revise and document security policies and procedures.', 'status' => 'pending', 'priority' => 'medium', 'progress' => 0],
                ['title' => 'Implement 2FA', 'description' => 'Enable two-factor authentication for all admin accounts.', 'status' => 'pending', 'priority' => 'high', 'progress' => 0],
                ['title' => 'Security training', 'description' => 'Conduct security awareness training for all team members.', 'status' => 'pending', 'priority' => 'medium', 'progress' => 0],
            ],
        ];

        return $taskSets[$projectName] ?? $this->getGenericTasks();
    }

    /**
     * Get generic tasks if project name doesn't match.
     */
    private function getGenericTasks(): array
    {
        return [
            ['title' => 'Project planning and setup', 'description' => 'Initial project setup and planning phase.', 'status' => 'completed', 'priority' => 'high', 'progress' => 100],
            ['title' => 'Requirements gathering', 'description' => 'Collect and document all project requirements.', 'status' => 'in_progress', 'priority' => 'high', 'progress' => 60],
            ['title' => 'Development phase 1', 'description' => 'First phase of development work.', 'status' => 'pending', 'priority' => 'medium', 'progress' => 0],
            ['title' => 'Testing and QA', 'description' => 'Quality assurance and testing.', 'status' => 'pending', 'priority' => 'medium', 'progress' => 0],
            ['title' => 'Deployment', 'description' => 'Deploy to production environment.', 'status' => 'pending', 'priority' => 'low', 'progress' => 0],
        ];
    }

    /**
     * Generate due date based on task status and project dates.
     */
    private function generateDueDate(string $status, Project $project): \DateTime
    {
        return match ($status) {
            'completed' => now()->subDays(rand(5, 20)),
            'in_progress', 'under_review' => now()->addDays(rand(3, 14)),
            default => now()->addDays(rand(14, 30)),
        };
    }

    /**
     * Add comments to a task.
     */
    private function addCommentsToTask(Task $task, $assignees, $incharges): void
    {
        $comments = [
            'Making good progress on this task. Should be completed on schedule.',
            'I have a question about the requirements. Can we discuss?',
            'Updated the implementation based on feedback.',
            'This is taking longer than expected. May need additional resources.',
            'Completed the first milestone. Moving to the next phase.',
            'Found a blocker. Need help from the team.',
            'Great work so far! Keep it up.',
            'Please review when you get a chance.',
            'Added some improvements to the original design.',
            'Testing is complete. Ready for review.',
        ];

        $commentCount = rand(1, 3);
        $allUsers = $assignees->merge($incharges);

        for ($i = 0; $i < $commentCount; $i++) {
            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $allUsers->random()->id,
                'content' => $comments[array_rand($comments)],
                'created_at' => now()->subDays(rand(0, 10))->subHours(rand(0, 23)),
            ]);
        }
    }
}

