<?php

namespace Modules\CRM\App\Repositories;

use App\Http\Services\CustomValuesService;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Modules\CRM\App\Http\Resources\TaskResource;
use Modules\CRM\App\Models\Project;
use Modules\CRM\App\Models\Task;
use Modules\CRM\App\Models\TaskApplicableTo;
use Modules\Employee\App\Models\Employee;
use Carbon\Carbon;
use Modules\CRM\App\Http\Resources\AssignedTaskResource;
use Modules\CRM\App\Models\TaskTimeLog;
use Modules\CRM\App\Models\TaskTimeLogDetail;
use Modules\Attendance\App\Models\Attendance;
use Modules\Attendance\App\Models\TimeSheet;
use Modules\CRM\App\Http\Resources\DailyReportResource;
use Modules\CRM\App\Http\Resources\InactiveTimeLogResource;
use Modules\CRM\App\Http\Resources\TaskTimeLogResource;
use Modules\CRM\App\Models\InactiveTimeLog;

class TaskRepo
{
    protected $customValuesService;
    protected $customValuesModule = 'task';

    public function __construct(CustomValuesService $customValuesService)
    {
        $this->customValuesService = $customValuesService;
    }

    /**
     * Get All Tasks.
     */
    public function getAll()
    {
        return Task::with('customValues.attribute')->get();
    }

    /**
     * Get Tasks With Pagination.
     */
    public function paginate($request)
    {
        $perPage = $request['per_page'] ?? 20;

        $tasks = Task::query();

        // Filter by search
        if(isset($request['search'])) 
        {
            $tasks->where(function ($query) use ($request) {
                $query->where('task_code', 'like', '%' . $request['search'] . '%')
                        ->orWhere('title', 'like', '%' . $request['search'] . '%');
                });
        }

        // Sort With Columns
        if (isset($request['sort']) && $request['sort'] != null && $request['sort'] != '') {
            $sorts = explode(',', $request['sort']);
            foreach ($sorts as $sortColumn) {
                $sortDirection = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $sortColumn    = ltrim($sortColumn, '-');
                $tasks->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $tasks->orderBy('created_at', 'DESC');
        }

        // Handle export
        if (isset($request['export'])) {
            $items = isset($request['only_this_page']) && $request['only_this_page'] == 1
                ? $tasks->skip(($request['page'] - 1) * $perPage)->take($perPage)->get()
                : $tasks->get();

            return TaskResource::collection($items);
        }

        $tasks = $tasks->with('customValues.attribute')->paginate($perPage);

        $data = $tasks->getCollection()->map(function ($item) {
            return new TaskResource($item);
        });

        return $tasks->setCollection($data);
    }

    /**
     * Get Task with id
     */
    public function get($id)
    {
        $task = Task::query()
            ->select([
                'id',
                'task_code',
                'title',
                'priority',
                'project_id',
                'status_id',
                'description',
                'due_date',
                'sort_order',
                'has_reminder',
                'notification_reminder',
                'notification_custom_minutes',
                'is_active',
                'completed_at',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ])
            ->with([
                'customValues.attribute',
                'project:id,name,project_code',
                'status:id,name,color',
                'applicableTos:task_id,scope,target_id',
                'createdBy:id,name',
                'updatedBy:id,name',
            ])
            ->findOrFail($id);

        $employee = auth()->check()
            ? Employee::where('user_id', auth()->id())->select('id')->first()
            : null;

        $task->setAttribute(
            'is_applicable_to_user',
            $employee
                ? Task::query()->whereKey($task->id)->forEmployee($employee)->exists()
                : true
        );

        return $task;
    }

    /**
     * Create Task
     */
    public function create(array $data)
    {
        $data['created_by'] = Auth::guard('api')->user()->id;
        $data['task_code'] = $this->generateTaskCode($data['project_id']);
        $data['priority'] = $data['priority'] ?? 'low';
        $task = Task::create($data);

        if (isset($data['applicable_to']) && is_array($data['applicable_to']) && count($data['applicable_to']) > 0) {
            $this->createApplicableTo($task, $data);
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
                $this->customValuesService->save($customData, $task->id, $this->customValuesModule);
            }
        }

        $this->createActivityLog($task, [
            'description' => 'Task is newly created',
        ]);

        return $task;
    }

    public function createApplicableTo(Task $task, array $data)
    {
        $applicableTo = [];
        foreach ($data['applicable_to'] as $applicableToItem) {
            foreach ($applicableToItem['ids'] as $targetId) {
                $applicableTo[] = [
                    'task_id'    => $task->id,
                    'scope'      => $applicableToItem['scope'],
                    'target_id'  => $targetId,
                ];
            }
        }

        TaskApplicableTo::insert($applicableTo);
    }

    private function generateTaskCode($projectId)
    {
        $project = Project::findOrFail($projectId);
        
        $lastTask = Task::where('project_id', $projectId)
                    ->orderBy('created_at', 'DESC')
                    ->first();
        
        if (!$lastTask) {
            return $project->project_code . '-0001';
        }

        $lastCode = $lastTask->task_code;

        preg_match('/.*-(\d+)$/', $lastCode, $matches);
        $number = isset($matches[1]) ? (int) $matches[1] : 0;
        $number++;

        return $project->project_code . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Update Task
     */
    public function update($id, array $data)
    {
        $data['updated_by'] = Auth::guard('api')->user()->id;
        $task = Task::findOrFail($id);

        $data['priority'] = $data['priority'] ?? $task->priority;

        if(isset($data['description']) && $task->description !== $data['description']) {
            $this->createActivityLog($task, [
                'description' => 'Description is updated',
            ]);
        }

        if(isset($data['status_id']) && $task->status_id !== $data['status_id']) {
            $this->createActivityLog($task, [
                'description' => 'Status is updated',
            ]);
        }

        if(isset($data['due_date']) && $task->due_date !== $data['due_date'] || isset($data['has_reminder']) && $task->has_reminder !== $data['has_reminder'] || isset($data['notification_reminder']) && $task->notification_reminder !== $data['notification_reminder'] || isset($data['notification_custom_minutes']) && $task->notification_custom_minutes !== $data['notification_custom_minutes']) {
            $this->createActivityLog($task, [
                'description' => 'Due Date/ Time & Reminder is updated',
            ]);
        }

        if (isset($data['applicable_to']) && is_array($data['applicable_to']) && count($data['applicable_to']) > 0) {
            $task->applicableTos()->delete();
            $this->createApplicableTo($task, $data);
        }

        $task->update($data);

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
                $this->customValuesService->save($customData, $task->id, $this->customValuesModule);
            }
        }

        return $task;
    }

    /**
     * Delete Task
     */
    public function delete($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
    }

    /**
     * Bulk Delete
     */
    function bulkDelete(array $ids) {
        $tasks = Task::whereIn('id', $ids)->get();
        foreach ($tasks as $key => $task) {
            $task->delete();
        }
    }

    /**
     * Duplicate Task
     */
    public function duplicate($id, $data)
    {
        $originalTask = Task::with('customValues')->findOrFail($id);
        $newTask = $originalTask->replicate();
        $newTask->created_by = auth()->user()->id;
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

        return $newTask;
    }
    
    public function getActivityLogs($taskId)
    {
        $task = Task::findOrFail($taskId);

        $activityLogs = $task->activityLogs()->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'type' => 'activity',
                'description' => $log->description,
                'action_by' => $log->actionBy?->name,
                'created_at' => $log->created_at,
                'updated_at' => $log->updated_at,
            ];
        });

        $comments = $task->comments()->get()->map(function ($comment) {
            return [
                'id' => $comment->id,
                'type' => 'comment',
                'comment' => $comment->comment,
                'commented_by' => $comment->commentBy?->name,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
            ];
        });

        $timeline = $activityLogs->concat($comments)
            ->sortByDesc('created_at')
            ->values();

        return $timeline;
    }

    public function createActivityLog($task, $data)
    {
        $data['description'] = $data['description'] ?? null;
        $data['action_by'] = auth()->user()->id;

        return $task->activityLogs()->create($data);
    }

    public function updateTaskPosition($taskId, $data)
    {
        $task = Task::findOrFail($taskId);

        $updateData = [
            'updated_by' => auth()->id(),
        ];

        if (isset($data['status_id'])) {
            $updateData['status_id'] = $data['status_id'];
            $this->createActivityLog($task, [
                'description' => 'Status is updated',
            ]);
        }

        if (isset($data['sort_order'])) {
            $updateData['sort_order'] = $data['sort_order'];
        }

        $originalTaskOrder = $task->sort_order;

        $task->update($updateData);

        if (isset($data['status_id']) && $task->wasChanged('status_id')) {
            $this->reorderTasksInStatus($data['status_id'], $taskId, $data['sort_order']);
        }

        if(isset($data['original_status_id']) && $data['original_status_id'] !== $data['status_id']) {
            $this->reorderOriginalTaskInStatus($data['original_status_id'], $originalTaskOrder);
        }

        return $task->fresh();
    }

    private function reorderOriginalTaskInStatus($originalStatusId, $originalTaskOrder)
    {
        Task::where('status_id', $originalStatusId)->where('sort_order', '>', $originalTaskOrder)->decrement('sort_order');
    }

    private function reorderTasksInStatus($statusId, $currentTaskId, $newSortOrder)
    {
        Task::where('status_id', $statusId)->where('id', '!=', $currentTaskId)->where('sort_order', '>=', $newSortOrder)->increment('sort_order');
    }

    public function addComment($taskId, $data)
    {
        $task = Task::findOrFail($taskId);

        $commentData = [
            'comment'   => $data['comment'],
            'commented_by' => auth()->user()->id,
        ];

        $comment = $task->comments()->create($commentData);

        return $comment;
    }

    public function updateComment($taskId, $commentId, $data)
    {
        $task = Task::findOrFail($taskId);

        $comment = $task->comments()->where('id', $commentId)->firstOrFail();

        $comment->update([
            'comment' => $data['comment'],
        ]);

        return $comment;
    }

    public function deleteComment($taskId, $commentId)
    {
        $task = Task::findOrFail($taskId);

        $comment = $task->comments()->where('id', $commentId)->firstOrFail();

        $comment->delete();
    }

    public function getTimerStatus($taskId)
    {
        $employee = $this->getEmployee();

        $log = TaskTimeLog::where('task_id', $taskId)->where('employee_id', $employee->id)->first();

        if (!$log) {
            return [
                'current_action' => 'stop',
                'start_time'     => '00:00:00',
            ];
        }

        $activeDetail = TaskTimeLogDetail::where('task_time_log_id', $log->id)
                        ->whereNull('end_time')
                        ->orderByDesc('start_time')
                        ->first();

        if ($activeDetail) {
            return [
                'current_action' => 'start',
                'start_time'     => Carbon::parse($activeDetail->start_time)->format('d-m-Y H:i:s'),
            ];
        }

        return [
            'current_action' => 'stop',
            'start_time'     => '00:00:00',
        ];
    }

    public function startEndTimer($taskId, $action)
    {
        $task     = Task::findOrFail($taskId);
        $employee = $this->getEmployee();

        Task::query()->whereKey($task->id)->forEmployee($employee)->exists() ?: throw new \Exception('You are not assigned to this task.');

        if ($action === 'start') {
            $this->stopAllRunningTasks($employee, $task->id);

            $today = now()->toDateString();
            
            $log = TaskTimeLog::firstOrCreate(
                [
                    'task_id'     => $task->id,
                    'employee_id' => $employee->id,
                    'log_date'    => $today,
                ],
                [
                    'status'                  => 'stopped',
                    'total_duration_minutes'  => 0,
                ]
            );

            $openDetail = TaskTimeLogDetail::whereHas('taskTimeLog', function($q) use ($employee, $task) {
                $q->where('employee_id', $employee->id)
                ->where('task_id', $task->id);
            })
            ->whereNull('end_time')
            ->first();

            if ($openDetail) {
                return $openDetail->taskTimeLog->fresh();
            }

            TaskTimeLogDetail::create([
                'task_time_log_id' => $log->id,
                'start_time'       => now(),
                'status'           => 'started',
            ]);

            $log->update(['status' => 'started']);

            return $log->fresh();
        }

        if ($action === 'stop') {
            $detail = TaskTimeLogDetail::whereHas('taskTimeLog', function($q) use ($employee, $task) {
                $q->where('employee_id', $employee->id)
                ->where('task_id', $task->id);
            })
            ->whereNull('end_time')
            ->orderByDesc('start_time')
            ->first();

            if (!$detail) {
                throw new \Exception('No active timer found to stop.');
            }

            $startTime = Carbon::parse($detail->start_time);
            $endTime   = now();
            $duration  = $endTime->diffInMinutes($startTime);

            if ($startTime->toDateString() !== $endTime->toDateString()) {
                $this->splitTimerAcrossDays($detail, $startTime, $endTime, $task, $employee);
            } else {
                $detail->update([
                    'end_time'         => $endTime,
                    'duration_minutes' => max($duration, 0),
                    'status'           => 'stopped',
                ]);

                $log = $detail->taskTimeLog;
                $log->update([
                    'status'                 => 'stopped',
                    'total_duration_minutes' => TaskTimeLogDetail::where('task_time_log_id', $log->id)
                                                                ->sum('duration_minutes'),
                ]);
            }

            return $detail->taskTimeLog->fresh();
        }

        throw new \Exception('Invalid action. Use "start" or "stop".');
    }

    protected function stopAllRunningTasks($employee, $excludeTaskId = null)
    {
        $activeDetails = TaskTimeLogDetail::whereHas('taskTimeLog', function($q) use ($employee, $excludeTaskId) {
            $q->where('employee_id', $employee->id);
            if ($excludeTaskId) {
                $q->where('task_id', '!=', $excludeTaskId);
            }
        })
        ->whereNull('end_time')
        ->get();

        foreach ($activeDetails as $detail) {
            $startTime = Carbon::parse($detail->start_time);
            $endTime   = now();
            $duration  = $endTime->diffInMinutes($startTime);

            $task = $detail->taskTimeLog->task;

            if ($startTime->toDateString() !== $endTime->toDateString()) {
                $this->splitTimerAcrossDays($detail, $startTime, $endTime, $task, $employee);
            } else {
                $detail->update([
                    'end_time'         => $endTime,
                    'duration_minutes' => max($duration, 0),
                    'status'           => 'stopped',
                ]);

                $log = $detail->taskTimeLog;
                $log->update([
                    'status'                 => 'stopped',
                    'total_duration_minutes' => TaskTimeLogDetail::where('task_time_log_id', $log->id)->sum('duration_minutes'),
                ]);
            }
        }
    }

    /**
     * Split a timer entry that crosses midnight into multiple daily entries
     */
    protected function splitTimerAcrossDays($originalDetail, $startTime, $endTime, $task, $employee)
    {
        $currentDate = $startTime->copy()->startOfDay();
        $endDate = $endTime->copy()->startOfDay();
        
        $entries = [];

        while ($currentDate->lte($endDate)) {
            $dayStart = $currentDate->equalTo($startTime->copy()->startOfDay()) 
                        ? $startTime->copy() 
                        : $currentDate->copy()->startOfDay();
            
            $dayEnd = $currentDate->equalTo($endDate) 
                    ? $endTime->copy() 
                    : $currentDate->copy()->endOfDay();

            $dayDuration = $dayEnd->diffInMinutes($dayStart);

            if ($dayDuration > 0) {
                $entries[] = [
                    'log_date' => $currentDate->toDateString(),
                    'start'    => $dayStart,
                    'end'      => $dayEnd,
                    'duration' => $dayDuration,
                ];
            }

            $currentDate->addDay();
        }

        $originalDetail->delete();

        foreach ($entries as $entry) {
            $log = TaskTimeLog::firstOrCreate(
                [
                    'task_id'     => $task->id,
                    'employee_id' => $employee->id,
                    'log_date'    => $entry['log_date'],
                ],
                [
                    'status'                 => 'stopped',
                    'total_duration_minutes' => 0,
                ]
            );

            TaskTimeLogDetail::create([
                'task_time_log_id' => $log->id,
                'start_time'       => $entry['start'],
                'end_time'         => $entry['end'],
                'duration_minutes' => $entry['duration'],
                'status'           => 'stopped',
            ]);

            $log->update([
                'status'                 => 'stopped',
                'total_duration_minutes' => TaskTimeLogDetail::where('task_time_log_id', $log->id)
                                                            ->sum('duration_minutes'),
            ]);
        }

        foreach ($entries as $entry) {
            $actualActiveMinutes = TaskTimeLogDetail::whereHas('taskTimeLog', function($q) use ($employee, $entry) {
                $q->where('employee_id', $employee->id)
                ->where('log_date', $entry['log_date']);
            })
            ->whereDate('start_time', $entry['log_date'])
            ->sum('duration_minutes');

            $existingLog = InactiveTimeLog::where('employee_id', $employee->id)
                                        ->where('date', $entry['log_date'])
                                        ->first();

            if ($existingLog) {
                $existingLog->update([
                    'active_minutes' => $actualActiveMinutes,
                    'inactive_minutes' => max($existingLog->total_working_minutes - $actualActiveMinutes, 0),
                ]);
            } else {
                InactiveTimeLog::create([
                    'employee_id'           => $employee->id,
                    'date'                  => $entry['log_date'],
                    'total_working_minutes' => $actualActiveMinutes,
                    'active_minutes'        => $actualActiveMinutes,
                    'inactive_minutes'      => 0,
                ]);
            }
        }
    }

    public function getTimeLogs($taskId)
    {
        $logs = TaskTimeLog::where('task_id', $taskId)->with('employee', 'timeLogDetails')->get();

        return $logs;
    }

    public function getMyAssignedTasks($data)
    {
        $employee = $this->getEmployee();
        $perPage  = $data['per_page'] ?? 20;
        $projects = isset($data['project_ids']) ? explode(',', $data['project_ids']) : null;

        if (!empty($data['start']) && !empty($data['end'])) {
            $startDate = Carbon::parse($data['start'])->startOfDay();
            $endDate = Carbon::parse($data['end'])->endOfDay();
        } elseif (!empty($data['month'])) {
            $monthInput = $data['month'];
            $monthNum = null;
            $year = null;
            if (preg_match('/^(\d{2})-(\d{4})$/', $monthInput, $matches)) {
                // Format: 08-2025
                $monthNum = (int)$matches[1];
                $year = (int)$matches[2];
            } elseif (preg_match('/^(\d{4})-(\d{2})$/', $monthInput, $matches)) {
                // Format: 2025-08
                $year = (int)$matches[1];
                $monthNum = (int)$matches[2];
            }
            if ($monthNum && $year) {
                $date = Carbon::create($year, $monthNum, 1);
                $startDate = $date->copy()->startOfMonth();
                $endDate = $date->copy()->endOfMonth();
            } else {
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
            }
        } else {
            $startDate = null;
            $endDate = null;
        }

        $tasks = Task::query()
            ->select([
                'id',
                'task_code',
                'priority',
                'title',
                'project_id',
                'status_id',
                'description',
                'due_date',
                'sort_order',
                'has_reminder',
                'notification_reminder',
                'notification_custom_minutes',
                'is_active',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
            ])
            ->where(fn ($query) => $query->forEmployee($employee));

        if($startDate !== null && $endDate !== null)
        {
            $tasks->whereBetween('due_date', [$startDate, $endDate]);
        }

        if (isset($data['search']) && $data['search'] !== '') {
            $search = $data['search'];
            $tasks->where(function ($query) use ($search) {
                $query->where('task_code', 'like', "%{$search}%")
                      ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if(isset($projects) && count($projects) > 0 && $projects !== []) 
        {
            $tasks->whereIn('project_id', $projects);
        }

        if (!empty($data['sort'])) {
            $sorts = explode(',', $data['sort']);
            foreach ($sorts as $sortColumn) {
                $direction = Str::startsWith($sortColumn, '-') ? 'DESC' : 'ASC';
                $column    = ltrim($sortColumn, '-');
                $tasks->orderBy($column, $direction);
            }
        } else {
            $tasks->orderBy('created_at', 'DESC');
        }

        $tasks->with([
            'customValues.attribute',
            'project:id,name,project_code',
            'status:id,name,color',
            'applicableTos:task_id,scope,target_id',
            'createdBy:id,name',
            'updatedBy:id,name',
            'timeLogs' => function ($query) use ($employee) {
                $query->select([
                        'id',
                        'task_id',
                        'employee_id',
                        'log_date',
                        'status',
                        'total_duration_minutes',
                    ])
                    ->where('employee_id', $employee->id)
                    ->with(['timeLogDetails' => function ($detailQuery) {
                        $detailQuery->select([
                                'id',
                                'task_time_log_id',
                                'start_time',
                                'end_time',
                                'duration_minutes',
                            ])
                            ->orderByDesc('start_time');
                    }])->orderByDesc('log_date');
            },
        ]);

        $paginator = $tasks->paginate($perPage);

        $paginator->getCollection()->transform(function ($task) {
            $log = $task->timeLogs->first();

            $timerStatus = [
                'current_action' => 'stop',
                'start_time'     => '00:00:00',
            ];

            if ($log) {
                $activeDetail = $log->timeLogDetails->first(fn ($detail) => is_null($detail->end_time));

                if ($activeDetail) {
                    $timerStatus = [
                        'current_action' => 'start',
                        'start_time'     => Carbon::parse($activeDetail->start_time)->format('Y-m-d H:i:s'),
                    ];
                }
            }

            $task->timer_status = $timerStatus;
            $task->total_duration_override = $task->timeLogs->sum('total_duration_minutes');

            return new AssignedTaskResource($task);
        });

        return $paginator;
    }

    private function getEmployee()
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->firstOrFail();
        return $employee;
    }

    public function getTodayTasks($data)
    {
        $employee = $this->getEmployee();
        $now      = Carbon::now();

        $sessionStart = $this->resolveManualSessionStart($employee) ?? $now->copy()->startOfDay();

        $taskIds = TaskTimeLog::query()
                    ->where('employee_id', $employee->id)
                    ->whereHas('timeLogDetails', function ($query) use ($sessionStart) {
                        $query->where(function ($detail) use ($sessionStart) {
                            $detail->where('start_time', '>=', $sessionStart)
                                ->orWhere(function ($overlap) use ($sessionStart) {
                                    $overlap->whereNotNull('end_time')
                                        ->where('end_time', '>=', $sessionStart);
                                });
                        });
                    })
                    ->pluck('task_id')
                    ->unique();

        if ($taskIds->isEmpty()) {
            return DailyReportResource::collection(collect())->additional([
                'session_start' => $sessionStart ? Carbon::parse($sessionStart)->format('Y-m-d H:i:s') : null,
            ]);
        }

        $tasks = Task::query()
            ->select([
                'id',
                'title',
                'project_id',
            ])
            ->whereIn('id', $taskIds)
            ->with([
                'project:id,name',
                'timeLogs' => function ($query) use ($employee, $sessionStart) {
                    $query->select([
                            'id',
                            'task_id',
                            'employee_id',
                            'total_duration_minutes',
                            'log_date',
                            'status',
                        ])
                        ->where('employee_id', $employee->id)
                        ->whereHas('timeLogDetails', function ($detail) use ($sessionStart) {
                            $detail->where(function ($inner) use ($sessionStart) {
                                $inner->where('start_time', '>=', $sessionStart)
                                    ->orWhere(function ($overlap) use ($sessionStart) {
                                        $overlap->whereNotNull('end_time')
                                            ->where('end_time', '>=', $sessionStart);
                                    });
                            });
                        })
                        ->with(['timeLogDetails' => function ($detailQuery) use ($sessionStart) {
                            $detailQuery->where(function ($inner) use ($sessionStart) {
                                $inner->where('start_time', '>=', $sessionStart)
                                    ->orWhere(function ($overlap) use ($sessionStart) {
                                        $overlap->whereNotNull('end_time')
                                            ->where('end_time', '>=', $sessionStart);
                                    });
                            })
                            ->orderBy('start_time');
                        }]);
                },
            ])
            ->orderBy('title')
            ->get()
            ->each(function ($task) use ($sessionStart) {
                $task->setAttribute('session_start', $sessionStart ? Carbon::parse($sessionStart)->format('Y-m-d H:i:s') : null);
            });

        return DailyReportResource::collection($tasks->values())->additional([
            'session_start' => $sessionStart ? Carbon::parse($sessionStart)->format('Y-m-d H:i:s') : null,
        ]);
    }

    protected function resolveManualSessionStart(Employee $employee): ?Carbon
    {
        $activeTimesheet = TimeSheet::query()->where('employee_id', $employee->id)->whereNull('checkout_time')
                            ->orderByDesc('created_at')
                            ->first();

        $currentAttendance = Attendance::query()->where('employee_id', $employee->id)
                            ->whereHas('timesheets', function ($q) use ($activeTimesheet) {
                                $q->where('id', $activeTimesheet->id);
                            })
                            ->first();

        if (!$currentAttendance) {
            return null;
        }

        $sessionStart = null;
        $current = $currentAttendance;

        if ($current) {
            $current->loadMissing(['timesheets' => function ($q) {
                $q->orderBy('checkin_time');
            }]);

            $firstTimesheet = $current->timesheets->first();
            $lastTimesheet = $current->timesheets->last();

            if($firstTimesheet->checkin_time == '00:00:00' && $current->note == 'overnight_created_attendance' && $lastTimesheet->checkin_time == '00:00:00')
            {
                $previousAttendance = Attendance::query()->where('employee_id', $employee->id)->where('date', '<', $current->date)->orderByDesc('date')
                                    ->with(['timesheets' => function ($q) {
                                        $q->orderBy('checkin_time');
                                    }])
                                    ->get()
                                    ->first(function ($attendance) {
                                        $lastTimesheet = $attendance->timesheets->last();
                                        return $lastTimesheet && $lastTimesheet->checkin_time != '00:00:00';
                                    });
                
                if ($previousAttendance) {
                    $lastTimesheet = $previousAttendance->timesheets->last();

                    $sessionStart = Carbon::parse(
                        $previousAttendance->date . ' ' . $lastTimesheet->checkin_time,
                        config('app.timezone')
                    );
                }
            } else {
                $sessionStart = Carbon::parse(
                    $current->date . ' ' . $lastTimesheet->checkin_time,
                    config('app.timezone')
                );
            }
        }

        return $sessionStart;
    }

    public function submitDailyReport(array $data)
    {
        $employee = $this->getEmployee();
        $timezone = config('app.timezone');

        $reports = collect($data['daily_reports'] ?? [])
            ->filter(fn ($report) => !empty($report['date']))
            ->map(function ($report) use ($timezone) {
                $report['__date_carbon'] = Carbon::createFromFormat('d-m-Y', $report['date'], $timezone);
                return $report;
            })
            ->sortBy('__date_carbon')
            ->values();

        if ($reports->isEmpty()) {
            throw new \Exception('No valid daily reports found to submit.');
        }

        DB::transaction(function () use ($reports, $employee, $timezone) {
            foreach ($reports as $report) {
                $reportDateCarbon = $report['__date_carbon']->copy()->startOfDay();
                $reportDateString = $reportDateCarbon->toDateString();
                $tasks            = collect($report['tasks'] ?? []);
                $markedInactive   = $report['marked_inactive'] ?? false;

                $providedTotalMinutes    = isset($report['total_minutes']) ? max((int) $report['total_minutes'], 0) : null;
                $providedActiveMinutes   = isset($report['active_minutes']) ? max((int) $report['active_minutes'], 0) : null;
                $providedInactiveMinutes = isset($report['inactive_minutes']) ? max((int) $report['inactive_minutes'], 0) : null;

                $calculatedActiveMinutes = 0;
                $newEntries = [];

                foreach ($tasks as $taskRow) {
                    if (empty($taskRow['task_id']) || empty($taskRow['start_time']) || empty($taskRow['end_time'])) {
                        continue;
                    }

                    $taskId = (int) $taskRow['task_id'];

                    $start = Carbon::createFromFormat(
                        'd-m-Y H:i:s',
                        "{$report['date']} {$taskRow['start_time']}",
                        $timezone
                    );

                    $end = Carbon::createFromFormat(
                        'd-m-Y H:i:s',
                        "{$report['date']} {$taskRow['end_time']}",
                        $timezone
                    );

                    if ($end->lte($start)) {
                        $end->addDay();
                    }

                    $durationMinutes = isset($taskRow['duration_minutes'])
                        ? max((int) $taskRow['duration_minutes'], 0)
                        : $start->diffInMinutes($end);

                    if ($durationMinutes <= 0) {
                        continue;
                    }

                    $log = TaskTimeLog::firstOrCreate(
                        [
                            'task_id'     => $taskId,
                            'employee_id' => $employee->id,
                            'log_date'    => $start->toDateString(),
                        ],
                        [
                            'status'                 => 'stopped',
                            'total_duration_minutes' => 0,
                        ]
                    );

                    $newEntries[] = [
                        'log'      => $log,
                        'start'    => $start->copy(),
                        'end'      => $end->copy(),
                        'minutes'  => $durationMinutes,
                        'task_id'  => $taskId,
                    ];

                    $calculatedActiveMinutes += $durationMinutes;
                }

                $existingEntries = TaskTimeLogDetail::whereHas('taskTimeLog', function($q) use ($employee, $reportDateString) {
                    $q->where('employee_id', $employee->id)
                    ->where('log_date', $reportDateString);
                })
                ->whereDate('start_time', $reportDateString)
                ->get();

                foreach ($newEntries as $newEntry) {
                    $newStart = $newEntry['start'];
                    $newEnd = $newEntry['end'];

                    foreach ($existingEntries as $existing) {
                        $existingStart = Carbon::parse($existing->start_time);
                        $existingEnd = Carbon::parse($existing->end_time);

                        $overlaps = $newStart->lt($existingEnd) && $newEnd->gt($existingStart);

                        if ($overlaps) {
                            $existing->delete();
                        }
                    }
                }

                $logsById = [];
                foreach ($newEntries as $entry) {
                    $logId = $entry['log']->id;
                    $logsById[$logId]['log'] = $entry['log'];
                    $logsById[$logId]['entries'][] = $entry;
                }

                foreach ($logsById as $payload) {
                    $log = $payload['log'];

                    foreach ($payload['entries'] as $entry) {
                        TaskTimeLogDetail::create([
                            'task_time_log_id' => $log->id,
                            'start_time'       => $entry['start'],
                            'end_time'         => $entry['end'],
                            'duration_minutes' => $entry['minutes'],
                            'status'           => 'stopped',
                        ]);
                    }

                    $log->update([
                        'status'                 => 'stopped',
                        'total_duration_minutes' => TaskTimeLogDetail::where('task_time_log_id', $log->id)
                                                                    ->sum('duration_minutes'),
                    ]);
                }

                if ($markedInactive && $providedTotalMinutes !== null) {
                    $totalMinutes    = $providedTotalMinutes;
                    $activeMinutes   = $providedActiveMinutes ?? $calculatedActiveMinutes;
                    $inactiveMinutes = $providedInactiveMinutes ?? max($totalMinutes - $activeMinutes, 0);
                } else {
                    $activeMinutes   = $providedActiveMinutes ?? $calculatedActiveMinutes;
                    $totalMinutes    = $providedTotalMinutes ?? $activeMinutes;
                    $inactiveMinutes = $providedInactiveMinutes ?? max($totalMinutes - $activeMinutes, 0);
                }

                if ($totalMinutes < $activeMinutes) {
                    $totalMinutes = $activeMinutes;
                    $inactiveMinutes = 0;
                }

                if ($totalMinutes != ($activeMinutes + $inactiveMinutes)) {
                    $inactiveMinutes = $totalMinutes - $activeMinutes;
                }

                $actualActiveMinutes = TaskTimeLogDetail::whereHas('taskTimeLog', function($q) use ($employee, $reportDateString) {
                    $q->where('employee_id', $employee->id)
                    ->where('log_date', $reportDateString);
                })
                ->whereDate('start_time', $reportDateString)
                ->sum('duration_minutes');

                $existingLog = InactiveTimeLog::where('employee_id', $employee->id)
                                            ->where('date', $reportDateString)
                                            ->first();

                if ($existingLog) {
                    $newTotal = $existingLog->total_working_minutes + $totalMinutes;
                    $existingLog->update([
                        'total_working_minutes' => $newTotal,
                        'active_minutes'        => $actualActiveMinutes,
                        'inactive_minutes'      => $newTotal - $actualActiveMinutes,
                    ]);
                } else {
                    InactiveTimeLog::create([
                        'employee_id'           => $employee->id,
                        'date'                  => $reportDateString,
                        'total_working_minutes' => $totalMinutes,
                        'active_minutes'        => $actualActiveMinutes,
                        'inactive_minutes'      => max($totalMinutes - $actualActiveMinutes, 0),
                    ]);
                }
            }
        });
    }

    public function getDailyReports(array $data)
    {
        $employee = $this->getEmployee();
        $reports = TaskTimeLog::with('task', 'employee', 'timeLogDetails')->where('employee_id', $employee->id)->orderBy('log_date', 'desc')->get();

        return TaskTimeLogResource::collection($reports);
    }

    public function getActiveTimeReports(array $data)
    {
        $employee = $this->getEmployee();
        $reports = InactiveTimeLog::with('employee')->where('employee_id', $employee->id)->orderBy('date', 'desc')->get();

        return InactiveTimeLogResource::collection($reports);
    }
}