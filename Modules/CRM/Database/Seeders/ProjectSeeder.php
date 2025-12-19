<?php

namespace Modules\CRM\Database\Seeders;

use Google\Service\AIPlatformNotebooks\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\CRM\App\Models\Project;
use Modules\CRM\App\Models\ProjectStatus;
use Modules\CRM\App\Models\ProjectTaskStatus;
use Modules\CRM\App\Models\StatusSetting;
use Modules\CRM\App\Models\Task;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('project_statuses')->truncate();
        DB::table('project_task_statuses')->truncate();
        DB::table('projects')->truncate();
        DB::table('tasks')->truncate();
        DB::table('project_applicable_tos')->truncate();
        DB::table('task_applicable_tos')->truncate();
        DB::table('status_settings')->truncate();

        $statusSettings = [
            ['name' => 'Not Started', 'color' => '#7e7777ff', 'category' => 'to_do', 'type' => 'project', 'is_default' => true],
            ['name' => 'In Progress', 'color' => '#3a6599ff', 'category' => 'in_progress', 'type' => 'project'],
            ['name' => 'Done', 'color' => '#169c16ff', 'category' => 'complete', 'type' => 'project'],
            ['name' => 'To Do', 'color' => '#ff0000ff', 'category' => 'to_do', 'type' => 'task'],
            ['name' => 'In Progress', 'color' => '#ffa500ff', 'category' => 'in_progress', 'type' => 'task'],
            ['name' => 'Done', 'color' => '#00ff00ff', 'category' => 'complete', 'type' => 'task'],
        ];

        foreach ($statusSettings as $setting) {
            StatusSetting::create($setting);
        }

        $projects = [
            [
                'project_code' => 'PRJ-001',
                'name' => 'Website Redesign',
                'description' => 'Redesign the corporate website to improve user experience.',
                'owner_id' => 1,
                'start_date' => '2023-10-01',
                'end_date' => '2024-01-31',
                'visibility' => 'public',
                'is_active' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'project_code' => 'PRJ-002',
                'name' => 'Mobile App Development',
                'description' => 'Develop a mobile application for our services.',
                'owner_id' => 2,
                'start_date' => '2023-11-15',
                'end_date' => '2024-05-15',
                'visibility' => 'private',
                'is_active' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'project_code' => 'PRJ-003',
                'name' => 'Marketing Campaign',
                'description' => 'Launch a new marketing campaign for the holiday season.',
                'owner_id' => 3,
                'start_date' => '2023-12-01',
                'end_date' => '2024-02-28',
                'visibility' => 'public',
                'is_active' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ]
        ];

        foreach ($projects as $projectData) {
            $project = Project::create($projectData);

            $projectStatuses = StatusSetting::where('type', 'project')->get()->toArray();

            foreach ($projectStatuses as $status) {
                unset($status['type'], $status['is_default'], $status['id'], $status['created_at'], $status['updated_at']);
                $status['project_id'] = $project->id;
                ProjectStatus::create($status);
            }

            $settingDefault = StatusSetting::where('type', 'project')->where('is_default', true)->first();
            $project_status_id = ProjectStatus::where('project_id', $project->id)->where('name', $settingDefault->name)->first()->id;

            $project->update(['project_status_id' => $project_status_id]);

            $taskStatuses = StatusSetting::where('type', 'task')->get()->toArray();

            foreach ($taskStatuses as $status) {
                unset($status['type'], $status['is_default'], $status['id'], $status['created_at'], $status['updated_at']);
                $status['project_id'] = $project->id;
                ProjectTaskStatus::create($status);
            }
        }

        $tasks = [
            [
                'task_code' => 'TSK-001',
                'project_id' => 1,
                'title' => 'Design Mockups',
                'description' => 'Create design mockups for the new website layout.',
                'status_id' => 1,
                'due_date' => '2023-10-15',
                'is_active' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'task_code' => 'TSK-002',
                'project_id' => 1,
                'title' => 'Develop Frontend',
                'description' => 'Implement the frontend based on the approved designs.',
                'status_id' => 2,
                'due_date' => '2023-11-30',
                'is_active' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'task_code' => 'TSK-003',
                'project_id' => 2,
                'title' => 'Set Up Backend',
                'description' => 'Establish the backend infrastructure for the mobile app.',
                'status_id' => 1,
                'due_date' => '2023-12-15',
                'is_active' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'task_code' => 'TSK-004',
                'project_id' => 3,
                'title' => 'Create Ad Content',
                'description' => 'Develop content for the holiday marketing ads.',
                'status_id' => 1,
                'due_date' => '2023-12-05',
                'is_active' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'task_code' => 'TSK-005',
                'project_id' => 3,
                'title' => 'Launch Campaign',
                'description' => 'Execute the marketing campaign across all channels.',
                'status_id' => 2,
                'due_date' => '2024-01-10',
                'is_active' => true,
                'created_by' => 1,
                'updated_by' => 1,
            ]
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}
