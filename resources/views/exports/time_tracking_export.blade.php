<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <table class="table table-responsive">
        <thead>
            <tr>
                <th scope="col">Date</th>
                <th scope="col">Employee</th>

                <th scope="col">Start Of Work</th>
                <th scope="col">Last Active At</th>
                <th scope="col">Total Hours</th>
                <th scope="col">Active</th>
                <th scope="col">verified Deducted</th>
                <th scope="col">verified Active</th>

                <th scope="col">Downtime</th>
                <th scope="col">Lateness</th>
                <th scope="col">OverTime</th>
                <th scope="col">Productive</th>
                <th scope="col">Unproductive</th>
                <th scope="col">Neutral</th>
                <th scope="col">Remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index=>$value)
            <tr>
                <td>{{ $value->date }}</td>
                <td>{{ $value->employee_name }}</td>
                
                <td>{{ $value->start_of_work }}</td>
                <td>{{ Carbon\Carbon::parse($value->last_active_at)->format('H:i:s') }}</td>
                <td>{{ $value->total_time }}</td>
                <td>{{ $value->active }}</td>
                <td>{{ $value->verified_deducted }}</td>
                <td>{{ $value->verified_active }}</td>

                <td>{{ $value->down_time }}</td>
                <td>{{ $value->lateness }}</td>
                <td>{{ $value->over_time }}</td>
                <td>{{ $value->productive }}</td>
                <td>{{ $value->unproductive }}</td>
                <td>{{ $value->neutral }}</td>
                <td>{{ $value->remark }}</td>
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
