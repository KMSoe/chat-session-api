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
                <th scope="col">Employee Code</th>
                <th scope="col">Employee Name</th>
                <th scope="col">Department</th>
                @foreach ($payroll_components as $payroll_component)
                    <th scope="col">{{ $payroll_component->name }}</th>
                @endforeach
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
                    @foreach ($payroll_components as $component)
                        @php
                            $employeeComponent = collect($item->payrollComponents)->firstWhere(
                                'payroll_component_id',
                                $component->id,
                            );
                        @endphp

                        <td>
                            {{ $employeeComponent ? $employeeComponent->amount : 'N/A' }}
                        </td>
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
