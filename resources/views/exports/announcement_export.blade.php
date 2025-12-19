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
                <th scope="col">Title</th>
                <th scope="col">Content</th>
                <th scope="col">Type</th>
                <th scope="col">Tags</th>
                <th scope="col">Posted At</th>
                <th scope="col">Expired At</th>
                <th scope="col">Posted By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($announcements as $index=>$value)
                <?php
                    $tag_names = $value->tags->pluck('name')->toArray();

                    $tags = implode(",", $tag_names ? $tag_names : []);
                ?>

                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $value->id }}</td>
                    <td>{{ $value->title }}</td>
                    {{-- <td>{!! $value->content !!}</td> --}}
                    <td>{{ $value->content }}</td>
                    <td>{{ $value->type }}</td>
                    <td>{{ $tags }}</td>
                    <td>{{ $value->posted_at ? date('d-m-Y', strtotime($value->posted_at)) : "" }}</td>
                    <td>{{ $value->expired_at ? date('d-m-Y', strtotime($value->expired_at)) : "" }}</td>
                    <td>{{ $value->posted_user ? $value->posted_user->name : "" }}</td>
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
