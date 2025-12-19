<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <table class="table table-responsive">
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Project ID</th>
                <th scope="col">Project Name</th>
                <th scope="col">Stage</th>
                <th scope="col">Project Lead</th>
                <th scope="col">Project Manager</th>
                <th scope="col">Employees</th>
                <th scope="col">Time Spent</th>
                <th scope="col">Start Date</th>
                <th scope="col">Launch Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($progress_checker_list as $index=>$value)

                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $value->project_code }}</td>
                    <td>{{ $value->project_name }}</td>
                    <td>
                        @forelse($value->taskCategories as $task_categories)
                            {{ $task_categories->name }}
                            @if (!$loop->last)
                                ,
                            @endif
                        @empty
                            No Stages
                        @endforelse
                    </td>
                    <td>
                        {{ $value->team_leader_code . ' ' . $value->team_leader_name }}
                    </td>
                    <td>
                        {{ $value->project_manager_code . ' ' . $value->project_manager_name }}
                    </td>

                    <td>
                        @forelse($value->employeeProjects as $employee)
                            {{ $employee->name }}
                            @if (!$loop->last)
                                ,
                            @endif
                        @empty
                            No Employee
                        @endforelse
                    </td>
                    <td>
                        {{ $value->time_spent }}
                    </td>
                    <td>
                        {{ $value->start_date }}
                    </td>

                    <td>
                        {{ $value->launch_date }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">
                        No records exist!.
                    </td>
                </tr>
            @endforelse

        </tbody>
    </table>

</body>

</html>
