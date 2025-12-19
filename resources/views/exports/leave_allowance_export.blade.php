<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <table class="table table-responsive">
        @php
            $leave_types = [];

            if(count($leave_allowances) > 0) {
                $leave_types = collect($leave_allowances[0]->leaveAllowances)->map(function($allowance) {
                    return $allowance['leave_type'];
                });
            }
        @endphp
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Employee Code</th>
                <th scope="col">Employee Name</th>
                <th scope="col">Joined Date</th>
                @foreach($leave_types as $leave_type)
                    <th scope="col">{{ $leave_type }} Allowance</th>
                    <th scope="col">{{ $leave_type }} Taken</th>
                    <th scope="col">{{ $leave_type }} Balacne</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($leave_allowances as $index=>$value)

            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $value->code }}</td>
                <td>{{ $value->preferred_name }}</td>
                <td>{{ $value->joined_date }}</td>
                @foreach($value->leaveAllowances as $allowance)
                    <td>{{ $allowance['allowance'] }}</td>
                    <td>{{ $allowance['taken'] }}</td>
                    <td>{{ $allowance['balance'] ?? 0  }}</td>
                @endforeach
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
