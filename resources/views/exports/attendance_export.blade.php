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
                <th scope="col">Department</th>
                <th scope="col">Clock in 1</th>
                <th scope="col">CLock Out 1</th>
                <th scope="col">Location</th>
                <th scope="col">Clock in 2</th>
                <th scope="col">CLock Out 2</th>
                <th scope="col">Location</th>
                <th scope="col">Clock in 3</th>
                <th scope="col">CLock Out 3</th>
                <th scope="col">Location</th>

                <th scope="col">Working Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index=>$value)
            <tr>
                <td>{{ $value->date }}</td>
                <td>{{ $value->employee_name }}</td>
                <td>{{ $value->department_name }}</td>
                <td>{{ $value->first_checkin_time }}</td>
                <td>{{ $value->first_checkout_time }}</td>
                <td>{{ $value->first_location_latitude }}, {{ $value->first_location_longitude}}</td>
                <td>{{ $value->second_checkin_time }}</td>
                <td>{{ $value->second_checkout_time }}</td>
                <td>{{ $value->second_location_latitude }}, {{ $value->second_location_longitude}}</td>
                <td>{{ $value->third_checkin_time }}</td>
                <td>{{ $value->third_checkout_time }}</td>
                <td>{{ $value->third_location_latitude }}, {{ $value->third_location_longitude}}</td>
                <td>{{ $value->working_status }}</td>
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
