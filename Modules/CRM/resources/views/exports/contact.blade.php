<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <table class="table table-responsive">
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Code</th>
                <th scope="col">First Name</th>
                <th scope="col">Last Name</th>
                <th scope="col">Email</th>
                <th scope="col">Work Phone Dial Code</th>
                <th scope="col">Work Phone Number</th>
                <th scope="col">Mobile Phone Dial Code</th>
                <th scope="col">Mobile Phone Number</th>
                <th scope="col">Is Customer</th>
                <th scope="col">Is Primary</th>
                <th scope="col">Status</th>
                <th scope="col">Company Name</th>
                <th scope="col">Created By</th>
                <th scope="col">Last Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contacts as $index=>$item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->contact_code }}</td>
                <td>{{ $item->first_name }}</td>
                <td>{{ $item->last_name }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->work_phone_dial_code }}</td>
                <td>{{ $item->work_phone_number }}</td>
                <td>{{ $item->mobile_phone_dial_code }}</td>
                <td>{{ $item->mobile_phone_number }}</td>
                <td>{{ $item->is_customer ? 'yes' : 'no' }}</td>
                <td>{{ $item->is_primary ? 'yes' : 'no' }}</td>
                <td>{{ $item->status ? 'active' : 'inactive' }}</td>
                <td>{{ $item->company?->name }}</td>
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