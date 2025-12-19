<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>


<body>
    <h4 class="text-center mb-3">ADW Opening Balance Report</h4>

    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>No</th>
                <th>Employee</th>
                <th>Effective Date</th>
                <th>Reference Period</th>
                <th>Total Wages in Period</th>
                <th>Total Worked Days</th>
                <th>Average Daily Rate (ADW)</th>
                <th>Adjustment Reason</th>
                <th>Created On</th>
                <th>Created By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index=>$item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->employee?->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->effective_date)->format('d-M-Y') }}</td>
                    <td>{{ $item->reference_period }}</td>
                    <td>{{ number_format($item->total_wages_in_period, 2) }}</td>
                    <td>{{ $item->total_worked_days_in_period }}</td>
                    <td>{{ $item->average_daily_rate_adw }}</td>
                    <td>{{ $item->adjustment_reason }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d-M-Y h:i A') }}</td>
                    <td>{{ $item->createdBy?->name }}</td>
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
