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
                <th scope="col">Employee Code</th>
                <th scope="col">Employee Name</th>
                <th scope="col">Currency</th>
                <th scope="col">Internet Incentive</th>
                <th scope="col">No absent Incentive</th>
                <th scope="col">No lateness Incentive</th>
                <th scope="col">Work Quality Rewoard Tier 1</th>
                <th scope="col">Work Quality Rewoard Tier 2</th>               
                <th scope="col">Other reward</th>               
                <th scope="col">Allowance</th>               
            </tr>
        </thead>
        <tbody>
            @forelse($employee_incentives as $value)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $value->employee_code }}</td>
                <td>{{ $value->employee_name }}</td>
                <td>{{ $value->currency_name }}</td>
                <td>{{ $value->internet_incentive }}</td>
                <td>{{ $value->no_absent_incentive }}</td>
                <td>{{ $value->no_lateness_incentive }}</td>
                <td>{{ $value->work_quality_rewards_tier_1 }}</td>
                <td>{{ $value->work_quality_rewards_tier_2 }}</td>
                <td>{{ $value->other_rewards }}</td>
                <td>{{ $value->allowance }}</td>
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