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
                <th scope="col">Claim Code</th>
                <th scope="col">Employee</th>
                <th scope="col">Claim Type</th>
                <th scope="col">Amount</th>
                <th scope="col">Claim Date</th>
                <th scope="col">Applied At</th>
                <th scope="col">Created By</th>
                <th scope="col">Status</th>
                <th scope="col">Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($claim_forms as $index=>$value)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $value->code }}</td>
                    <td>{{ $value->employee_name }}</td>
                    <td>{{ $value->claim_type_name }} </td>
                    <td>{{ $value->currency_code . ' ' . $value->balance }} </td>
                    <td>{{ $value->claim_date ? Carbon\Carbon::parse($value->claim_date)->format('d/m/Y') : '' }}</td>
                    <td>{{ $value->applied_at ? Carbon\Carbon::parse($value->applied_at)->format('d/m/Y') : '' }} </td>
                    <td>{{ $value->created_by_name }}</td>
                    <td>{{ $value->status }}</td>
                    <td>{{ $value->description }}</td>
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
