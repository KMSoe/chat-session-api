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
                <th scope="col">Company</th>
                <th scope="col">Platform</th>
                <th scope="col">Account</th>
                <th scope="col">Password</th>
                <th scope="col">links</th>
                <th scope="col">Tags</th>
                <th scope="col">Assign Users</th>
                <th scope="col">created by</th>
                <th scope="col">created at</th>
            </tr>
        </thead>
        <tbody>
            @forelse($passwords as $index=>$value)

            <?php
            $tag_names = $value->tags->pluck('name')->toArray();

            $tags = implode(",", $tag_names ? $tag_names : []);

            $user_names = $value->assign_users->pluck('name')->toArray();

            $users = implode(",", $user_names ? $user_names : []);
            ?>

            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $value->id }}</td>
                <td>{{ $value->company }}</td>
                <td>{{ $value->platform }}</td>
                <td>{{ $value->account }}</td>
                <td>{{ $value->password }}</td>
                <td>{{ $value->links }}</td>
                <td>{{ $tags }}</td>
                <td>{{ $users }}</td>
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