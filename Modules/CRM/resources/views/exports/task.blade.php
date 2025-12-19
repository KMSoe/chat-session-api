<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <table class="table table-responsive">
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Project Name</th>
                <th scope="col">Title</th>
                <th scope="col">Description</th>
                <th scope="col">Due Date</th>
                <th scope="col">Status</th>
                <th scope="col">Is Active</th>
                <th scope="col">Created By</th>
                <th scope="col">Last Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $index=>$item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->project?->name }}</td>
                <td>{{ $item->title }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->due_date }}</td>
                <td>{{ $item->status?->name }}</td>
                <td>{{ $item->is_active ? 'active' : 'inactive' }}</td>
                <th>{{ $item->createdBy?->name }} <br/>
                    {{ $item->created_at === null ? '' : $item->created_at->format('d-m-Y') }}, {{ $item->created_at === null ? '' : $item->created_at->format('H:i:s') }}
                </th>
                <th>{{ $item->updatedBy?->name }} <br/>
                    {{ $item->updated_at === null ? '' : $item->updated_at->format('d-m-Y') }}, {{ $item->updated_at === null ? '' : $item->updated_at->format('H:i:s') }}
                </th>
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