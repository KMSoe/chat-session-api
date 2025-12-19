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
                <th scope="col">uniqeID</th>
                <th scope="col">employeeID</th>
                <th scope="col">name</th>
                <th scope="col">preferredName</th>
                <th scope="col">email</th>
                <th scope="col">Work Email</th>
                <th scope="col">mobile</th>
                <th scope="col">joinedDate</th>
                <th scope="col">years</th>
                <th scope="col">department</th>
                <th scope="col">designation</th>
                <th scope="col">reportTo</th>
                <th scope="col">Currency</th>
                <th scope="col">Salary</th>
                <th scope="col">dob</th>
                <th scope="col">age</th>
                <th scope="col">gender</th>
                <th scope="col">race</th>
                <th scope="col">religion</th>
                <th scope="col">nationality</th>
                <th scope="col">nationalityID</th>
                <th scope="col">passportNo</th>
                <th scope="col">device</th>
                <th scope="col">bank</th>
                <th scope="col">bankAccountNo</th>
                <th scope="col">status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $index=>$value)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $value->id }}</td>
                <td>{{ $value->code }}</td>
                <td>{{ $value->name }}</td>
                <td>{{ $value->preferred_name }}</td>
                <td>{{ $value->email }}</td>
                <td>{{ $value->work_email }}</td>
                <td>{{ $value->mobile }}</td>
                <td>{{ $value->joined_date }}</td>
                <td>{{ $value->year_w_vo }}</td>
                <td>{{ $value->department }}</td>
                <td>{{ $value->designation }}</td>
                <td>{{ $value->reportTo->name ?? "" }}</td>
                <td>{{ $value->currency }}</td>
                <td>{{ $value->salary }}</td>
                <td>{{ $value->dob }}</td>
                <td>{{ $value->age }}</td>
                <td>{{ $value->gender }}</td>
                <td>{{ $value->race }}</td>
                <td>{{ $value->religion }}</td>
                <td>{{ $value->country }}</td>
                <td>{{ $value->nationality_id }}</td>
                <td>{{ $value->passport_no }}</td>
                <td>{{ $value->device }}</td>
                <td>{{ $value->bank }}</td>
                <td>{{ $value->bank_account_no }}</td>
                <td>{{ $value->status }}</td>
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