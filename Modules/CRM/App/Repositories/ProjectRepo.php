<?php

namespace Modules\CRM\App\Repositories;

use App\Http\Services\CustomValuesService;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\CRM\App\Http\Resources\ProjectResource;
use Modules\CRM\App\Models\Project;
use Modules\CRM\App\Models\ProjectApplicableTo;
use Modules\CRM\App\Models\ProjectStatus;
use Modules\CRM\App\Models\ProjectTaskStatus;
use Modules\CRM\App\Models\StatusSetting;
use Modules\CRM\App\Models\Task;
use Modules\CRM\App\Models\TaskTimeLog;
use Modules\Employee\App\Models\Employee;

class ProjectRepo
{
    protected $customValuesService;
    protected $customValuesModule = 'project';

    public function __construct(CustomValuesService $customValuesService)
    {
        $this->customValuesService = $customValuesService;
    }

    /**
     * Get All Projects.
     */
    public function getAll()
    {
        return Project::with('customValues.attribute', 'events', 'taskStatuses.tasks', 'applicableTos')->get();
    }

    /**
     * Get Projects With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        $projects = Project::query();

        if ($employee) {
            $projects->where(function ($query) use ($employee) {
                $query->where('owner_id', $employee->id)
                    ->orWhere(function ($q) use ($employee) {
                        $q->whereHas('applicableTos', function ($at) {
                            $at->where('visibility', 'workspace');
                        })
                        ->forEmployee($employee);
                    })
                    ->orWhere('visibility', 'public');
            });
        }

        // Filter by search
        if(isset($request['search'])) 
        {
            $projects->where(function ($query) use ($request) {
                $query->where('project_code', 'like', '%' . $request['search'] . '%')
                        ->orWhere('name', 'like', '%' . $request['search'] . '%');
                });
        }

        // Filer By Owner
        if (isset($request['owner_id']) && $request['owner_id'] != null && $request['owner_id'] != '') {
           $owners = explode(',', $request['owner_id']);
           $projects->whereIn('owner_id', $owners);
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $projects->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $projects->orderBy('created_at', 'DESC');
        }

        // Handle export
        if (isset($request['export'])) {
            $items = isset($request['only_this_page']) && $request['only_this_page'] == 1
                ? $projects->skip(($request['page'] - 1) * $perPage)->take($perPage)->get()
                : $projects->get();

            return ProjectResource::collection($items);
        }

        $projects = $projects->with('customValues.attribute', 'events', 'taskStatuses.tasks', 'applicableTos')->paginate($perPage);

        $data = $projects->getCollection()->map(function ($item) {
            return new ProjectResource($item);
        });

        return $projects->setCollection($data);
    }

    /**
     * Get Project with id
     */
    public function get($id)
    {
        return Project::with('customValues.attribute', 'events', 'taskStatuses.tasks', 'applicableTos')->findOrFail($id);
    }

    /**
     * Create Project
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::guard('api')->user()->id;
        $project = Project::create($data);

        $this->createInitialProjectStatus($project);
        $this->createInitialTaskStatus($project->id);
        
        if (isset($data['applicable_to']) && is_array($data['applicable_to']) && count($data['applicable_to']) > 0) {
            $this->createApplicableTo($project, $data);
        }

        $module = Module::whereRaw('BINARY `name` = ?', [$this->customValuesModule])->first();

        if ($module) {
            $attributeNames = $module->attributes()
                ->where('status', 1)
                ->pluck('name')
                ->toArray();

            $customData = [];

            foreach ($attributeNames as $name) {
                if (is_array($data)) {
                    if (array_key_exists($name, $data)) {
                        $customData[$name] = $data[$name];
                    }
                } else {
                    if ($data->has($name)) {
                        $customData[$name] = $data->input($name);
                    }
                }
            }

            if (isset($customData) && ! empty($customData)) {
                $this->customValuesService->save($customData, $project->id, $this->customValuesModule);
            }
        }

        return $project->load('customValues.attribute', 'events', 'taskStatuses.tasks', 'applicableTos');
    }

    private function createInitialProjectStatus($project)
    {
        $statuses =  StatusSetting::where('type', 'project')->get();

        foreach ($statuses as $status) {
            ProjectStatus::create([
                'project_id' => $project->id,
                'name' => $status->name,
                'color' => $status->color,
                'sort_order' => $status->sort_order,
                'category' => $status->category,
                'system_created' => true
            ]);
        }

        $settingDefault = StatusSetting::where('type', 'project')->where('is_default', true)->first();
        $project_status_id = ProjectStatus::where('project_id', $project->id)->where('name', $settingDefault->name)->first()->id;

        $project->update(['project_status_id' => $project_status_id]);
    }

    private function createInitialTaskStatus($projectId)
    {
        $statuses =  StatusSetting::where('type', 'task')->get();

        foreach ($statuses as $status) {
            ProjectTaskStatus::create([
                'project_id' => $projectId,
                'name' => $status->name,
                'color' => $status->color,
                'sort_order' => $status->sort_order,
                'category' => $status->category,
                'system_created' => true
            ]);
        }
    }

    public function createApplicableTo(Project $project, array $data)
    {
        $applicableTo = [];
        foreach ($data['applicable_to'] as $applicableToItem) {
            foreach ($applicableToItem['ids'] as $targetId) {
                $applicableTo[] = [
                    'project_id' => $project->id,
                    'scope'      => $applicableToItem['scope'],
                    'target_id'  => $targetId,
                ];
            }
        }

        ProjectApplicableTo::insert($applicableTo);
    }
    
    /**
     * Update Project
     */
    public function update($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id;
        $project = Project::findOrFail($id);
        $project->update($data);

        $module = Module::whereRaw('BINARY `name` = ?', [$this->customValuesModule])->first();

        if ($module) {
            $attributeNames = $module->attributes()
                ->where('status', 1)
                ->pluck('name')
                ->toArray();

            $customData = [];

            foreach ($attributeNames as $name) {
                if (is_array($data)) {
                    if (array_key_exists($name, $data)) {
                        $customData[$name] = $data[$name];
                    }
                } else {
                    if ($data->has($name)) {
                        $customData[$name] = $data->input($name);
                    }
                }
            }

            if (isset($customData) && ! empty($customData)) {
                $this->customValuesService->save($customData, $project->id, $this->customValuesModule);
            }
        }

        return $project->load('customValues.attribute', 'events', 'taskStatuses.tasks', 'applicableTos');
    }

    /**
     * Delete Project
     */
    public function delete($id)
    {
        $project = Project::findOrFail($id);

        if($project->tasks && $project->tasks->count() > 0) {
            throw new \Exception("Cannot delete project with tasks assigned. Please delete the tasks first.");
        }

        $project->delete();
    }

    /**
     * Bulk Delete
     */
    function bulkDelete(array $ids) {
        $projects = Project::whereIn('id', $ids)->get();
        foreach ($projects as $key => $project) {
            if($project->tasks && $project->tasks->count() > 0) {
                throw new \Exception("Cannot delete project '".$project->name."' with tasks assigned. Please delete the tasks first.");
            }
            $project->delete();
        }
    }

    /**
     * Duplicate Project
     */
    public function duplicate($id, array $data)
    {
        $originalProject = Project::with(['customValues', 'taskStatuses.tasks.customValues'])->findOrFail($id);
        $data['created_by'] = auth()->user()->id;
        $data['name'] = $data['name'] ?? 'Copy of ' . $originalProject->name;
        $data['project_code'] = $data['project_code'] ?? null;
        $data['visibility'] = $data['visibility'] ?? 'public';
        $newProject = Project::create($originalProject->toArray() + $data);

        foreach ($originalProject->customValues as $customValue) {
            $newCustomValue = $customValue->replicate();
            $newCustomValue->record_id = $newProject->id;
            $newCustomValue->save();
        }

        if($originalProject->applicableTos && $originalProject->applicableTos->count() > 0) {
            $this->createApplicableTo($newProject, ['applicable_to' => $originalProject->applicableTos->map(function($item) {
                return [
                    'scope' => $item->scope,
                    'ids' => [$item->target_id],
                ];
            })->toArray()]);
        }

        $originalProjectStatuses = $originalProject->projectStatuses()->get();

        if($originalProjectStatuses && $originalProjectStatuses->count() > 0) {
            foreach ($originalProjectStatuses as $originalStatus) {
                $newStatus = ProjectStatus::create([
                    'project_id' => $newProject->id,
                    'name' => $originalStatus->name,
                    'color' => $originalStatus->color,
                    'sort_order' => $originalStatus->sort_order,
                ]);
            }
        } else {
            $this->createInitialProjectStatus($newProject);
        }

        if(isset($data['task_duplicate']) && $data['task_duplicate'] == true) {
            foreach ($originalProject->taskStatuses as $originalStatus) {
                $newStatus = ProjectTaskStatus::create([
                    'project_id' => $newProject->id,
                    'name' => $originalStatus->name,
                    'color' => $originalStatus->color,
                    'sort_order' => $originalStatus->sort_order,
                ]);

                foreach ($originalStatus->tasks as $originalTask) {
                    $newTask = $originalTask->replicate();
                    $newTask->project_id = $newProject->id;
                    $newTask->status_id = $newStatus->id;
                    $newTask->created_by = auth()->user()->id;
                    $newTask->updated_by = null;
                    $newTask->completed_at = null;
                    $newTask->save();

                    foreach ($originalTask->customValues as $customValue) {
                        $newCustomValue = $customValue->replicate();
                        $newCustomValue->target_id = $newTask->id;
                        $newCustomValue->save();
                    }

                    if($originalTask->applicableTos && $originalTask->applicableTos->count() > 0) {
                        foreach ($originalTask->applicableTos as $applicableTo) {
                            $newApplicableTo = $applicableTo->replicate();
                            $newApplicableTo->task_id = $newTask->id;
                            $newApplicableTo->save();
                        }
                    }
                }
            }
        } else {
            $this->createInitialTaskStatus($newProject->id);
        }

        return $newProject;
    }

    public function updateStatusOrders(array $data)
    {
        $type         = $data['type'];
        $statusOrders = $data['status_orders'];

        if ($type === 'project') {
            $projectId = $data['project_id'];

            foreach ($statusOrders as $statusOrder) {
                ProjectStatus::where('project_id', $projectId)
                            ->where('id', $statusOrder['id'])
                            ->update(['sort_order' => $statusOrder['sort_order']]);
            }

            return;
        }

        if ($type === 'task') {
            $projectId = $data['project_id'];

            foreach ($statusOrders as $statusOrder) {
                ProjectTaskStatus::where('project_id', $projectId)
                            ->where('id', $statusOrder['id'])
                            ->update(['sort_order' => $statusOrder['sort_order']]);
            }

            return;
        }

        foreach ($statusOrders as $statusOrder) {
            StatusSetting::where('id', $statusOrder['id'])
                ->update(['sort_order' => $statusOrder['sort_order']]);
        }
    }

    /**
     * Get project board data (Trello-like view)
     */
    public function getProjectBoard($projectId, $data)
    {
        $perPage   = (int) ($data['per_page'] ?? 20);
        $employee  = Employee::where('user_id', auth()->user()->id)->select('id')->first();
        $todayDate = now()->toDateString();

        $query = Project::query()
                    ->select([
                        'id',
                        'name',
                        'owner_id',
                    ])
                    ->whereKey($projectId);

        $query->with([
            'taskStatuses' => function ($statusQuery) use ($data) {
                $statusQuery->select(['id', 'project_id', 'name', 'category', 'color', 'sort_order'])
                    ->orderBy('sort_order', 'asc');

                if (!empty($data['status'])) {
                    $statuses = explode(',', str_replace(', ', ',', $data['status']));
                    $statusQuery->whereIn('name', $statuses);
                }
            },
            'taskStatuses.tasks' => function ($taskQuery) use ($data, $employee, $todayDate) {
                $taskQuery->select([
                        'id',
                        'title',
                        'project_id',
                        'status_id',
                        'sort_order',
                        'due_date',
                        'is_active',
                    ])
                    ->orderBy('sort_order', 'asc');

                if (isset($data['search']) && $data['search'] != null && $data['search'] != '') {
                    $taskQuery->where('title', 'like', '%' . $data['search'] . '%');
                }

                if (!empty($data['due_date_from'])) {
                    $taskQuery->whereDate('due_date', '>=', $data['due_date_from']);
                }

                if (!empty($data['due_date_to'])) {
                    $taskQuery->whereDate('due_date', '<=', $data['due_date_to']);
                }

                $taskQuery->with([
                    'applicableTos:id,task_id,scope,target_id',
                ]);
            },
        ]);

        $projects = $query->paginate($perPage);
        $collection = $projects->getCollection();

        $taskIds = $collection
            ->flatMap(fn ($project) => $project->relationLoaded('taskStatuses')
                ? $project->taskStatuses->flatMap(fn ($status) => $status->relationLoaded('tasks')
                    ? $status->tasks->pluck('id')
                    : collect())
                : collect())
            ->unique()
            ->values();

        if ($taskIds->isEmpty()) {
            return $projects;
        }

        $applicableTaskIds = [];
        if ($employee) {
            $applicableTaskIds = Task::query()
                ->select('id')
                ->whereIn('id', $taskIds)
                ->forEmployee($employee)
                ->pluck('id')
                ->toArray();
        } else {
            $applicableTaskIds = $taskIds->toArray();
        }

        $latestLogsQuery = TaskTimeLog::query()
            ->select('task_id', 'status', 'log_date', 'id')
            ->whereIn('task_id', $taskIds)
            ->whereDate('log_date', $todayDate)
            ->when($employee, fn ($q) => $q->where('employee_id', $employee->id))
            ->orderByDesc('log_date')
            ->orderByDesc('id')
            ->get();

        $latestLogs = $latestLogsQuery
            ->unique('task_id')
            ->keyBy('task_id');

        $collection->each(function ($project) use ($latestLogs, $applicableTaskIds, $employee) {
            if (!$project->relationLoaded('taskStatuses')) {
                return;
            }

            $project->taskStatuses->each(function ($status) use ($latestLogs, $applicableTaskIds, $employee) {
                if (!$status->relationLoaded('tasks')) {
                    return;
                }

                $status->tasks->each(function ($task) use ($latestLogs, $applicableTaskIds, $employee) {
                    $isApplicable = $employee ? in_array($task->id, $applicableTaskIds, true) : true;
                    $task->setAttribute('is_applicable_to_user', $isApplicable);
                    $task->setAttribute('latest_time_log_status', $latestLogs->get($task->id)?->status ?? 'stopped');
                });
            });
        });

        return $projects;
    }
}


