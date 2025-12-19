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
                <th scope="col">No</th>
                <th scope="col">Applied At</th>
                <th scope="col">Employee Name</th>
                <th scope="col">Leave Type</th>
                <th scope="col">From</th>
                <th scope="col">To</th>
                <th scope="col">Full/Half Day</th>
                <th scope="col">Total</th>
                <th scope="col">Reason</th>
                <th scope="col">Status</th>
                <th scope="col">Remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaves as $index=>$value)
    
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $value->applied_at }}</td>
                <td>{{ $value->employee ? $value->employee->preferred_name : '' }}</td>
                <td>{{ $value->type ? $value->type->name : "" }}</td>
                <td>{{ $value->from_date }}</td>
                <td>{{ $value->to_date }}</td>
                <td>{{ $value->full_half_day }}</td>
                <td>{{ $value->total_days }}</td>
                <td>{{ $value->reason }}</td>
                <td>{{ $value->status }}</td>
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