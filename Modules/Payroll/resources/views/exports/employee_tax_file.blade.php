<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>


<body>
    <h4 class="text-center mb-3">Employee Report</h4>

    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>No</th>
                <th>Employee Code</th>
                <th>Name</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Passport No</th>
                <th>Passport Place of Issue</th>
                <th>Spouse Name</th>
                <th>Spouse ID</th>
                <th>Spouse Passport No</th>
                <th>Spouse Passport Place</th>
                <th>Region Code</th>
                <th>Principal Employer</th>
                <th>Tax Identity</th>
                <th>Other Income</th>
                <th>Same As Address</th>
                <th>Postal Address</th>
                <th>Employer Provides Residence</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index=>$item)

                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->employee_code }}</td>
                    <td>{{ $item->name }}</td>
                    <td>
                        {{ collect($item->departments)->pluck('name')->implode(', ') }}
                    </td>
                    <td>
                        {{ collect($item->designations)->pluck('name')->implode(', ') }}
                    </td>
                    <td>{{ $item->passport_no }}</td>
                    <td>{{ $item->passport_place_of_issue }}</td>
                    <td>{{ $item->spouse_full_name }}</td>
                    <td>{{ $item->spouse_id_card }}</td>
                    <td>{{ $item->spouse_passport_no }}</td>
                    <td>{{ $item->spouse_passport_place_of_issue }}</td>
                    <td>{{ $item->region_code }}</td>
                    <td>{{ $item->principal_employer_name }}</td>
                    <td>{{ $item->tax_identity }}</td>
                    <td>{{ $item->other_income_name }}</td>
                    <td>{{ $item->same_as_address ? 'Yes' : 'No' }}</td>
                    <td>{{ $item->postal_address }}</td>
                    <td>{{ $item->employer_provides_residence ? 'Yes' : 'No' }}</td>
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
