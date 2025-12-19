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
                <th scope="col">Designation</th>
                <th scope="col">Payroll Month</th>
                <th scope="col">Pay Date</th>
                <th scope="col">Currency</th>
                <th scope="col">Basic Salary</th>
                <th scope="col">Overtime Hours</th>
                <th scope="col">Overtime Pay</th>
                @foreach ($all_earnings as $component)
                    <th scope="col">{{ $component->name }}</th>
                @endforeach
                <th scope="col">Gross Salary</th>
                <th scope="col">Unpaid Leave Deduction</th>
                <th scope="col">Absent Deduction</th>
                @foreach ($all_deductions as $component)
                    <th scope="col">{{ $component->name }}</th>
                @endforeach
                <th scope="col">Total Deduction</th>
                <th scope="col">Net Pay</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index=>$item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->employee?->employee_code }}</td>
                    <td>{{ $item->employee?->name }}</td>
                    <td>
                        {{ collect($item->employee?->departments)->pluck('name')->implode(', ') }}
                    </td>
                    <td>
                        {{ collect($item->employee?->designations)->pluck('name')->implode(', ') }}
                    </td>
                    <td>{{ $item->payroll_month ? Carbon\Carbon::parse($item->payroll_month)->format('F Y') : '-' }}
                    </td>
                    <td>{{ $item->pay_date ?? '-' }}</td>
                    <td>{{ $item->currency?->code }}</td>
                    <td>{{ number_format($item->basic_salary, 2) }}</td>
                    <td>{{ $item->overtimeSummary?->total_hours ?? 0 }}</td>
                    <td>{{ number_format($item->overtimeSummary?->amount ?? 0, 2) }}</td>
                    @foreach ($all_earnings as $component)
                        @php
                            $employeeComponent = collect($item->earnings)->firstWhere(
                                'payroll_component_id',
                                $component->id,
                            );
                        @endphp

                        <td>
                            {{ $employeeComponent ? number_format($employeeComponent->amount, 2) : 'N/A' }}
                        </td>
                    @endforeach
                    <td>{{ number_format($item->gross_salary, 2) }}</td>
                    <td>{{ number_format($item->unpaidLeaveSummary?->amount ?? 0, 2) }}</td>
                    <td>{{ number_format($item->absentSummary?->amount ?? 0, 2) }}</td>
                    @foreach ($all_deductions as $component)
                        @php
                            $employeeComponent = collect($item->deductions)->firstWhere(
                                'payroll_component_id',
                                $component->id,
                            );
                        @endphp

                        <td>
                            {{ $employeeComponent ? number_format($employeeComponent->amount, 2) : 'N/A' }}
                        </td>
                    @endforeach
                    <td>{{ number_format($item->total_deductions, 2) }}</td>
                    <td>{{ number_format($item->net_pay, 2) }}</td>
                    <td>{{ $item->status }}</td>
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
