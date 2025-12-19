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
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Employee Status</th>
                <th scope="col">Activated</th>
                <th scope="col">Role</th>
                <th scope="col">created by</th>
                <th scope="col">created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index=>$value)
            <?php
            $role_names = $value->roles->pluck('name')->toArray();

            $roles = implode(",", $role_names ? $role_names : [])

            ?>
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $value->id }}</td>
                <td>{{ $value->name }}</td>
                <td>{{ $value->email }}</td>
                <td>{{ $value->employee_id > 0 ? 'true' : 'false' }}</td>
                <td>{{ $value->is_activated == 1 ? "Yes" : "No" }}</td>
                <td>{{ $roles }}</td>
                <td>{{ $value->createdBy ? $value->createdBy->name : "" }}</td>
                <td>{{ $value->created_at ? date('d-m-Y', strtotime($value->created_at)) : "" }}</td>

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