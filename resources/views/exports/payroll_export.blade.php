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
                <th scope="col">Payroll No.</th>
                <th scope="col">Process Date</th>
                <th scope="col">Employee ID</th>
                <th scope="col">Employee Name</th>
                <th scope="col">Month</th>
                <th scope="col">Basic Salary</th>
                <th scope="col">Est. in SGD</th>

                <th scope="col">Total Days</th>
                <th scope="col">Official Off</th>
                <th scope="col">Hourly Rate</th>
                <th scope="col">Est. in SGD</th>
                <th scope="col">Regular Working Days</th>
                <th scope="col">Actual Working Days</th>
                <th scope="col">Counted Working Days</th>
                <th scope="col">Counted Working Days Salary</th>
                <th scope="col">Counted Active Times(Hrs)</th>
                <th scope="col">Official 90% Active Hours</th>
                <th scope="col">Official 80% Active Hours</th>
                <th scope="col">Official 70% Active Hours</th>


                <th scope="col">Internet Incentive</th>
                <th scope="col">No absent Incentive</th>
                <th scope="col">No lateness Incentive</th>
                <th scope="col">Work Quality Rewards Tier 1</th>
                <th scope="col">Work Quality Rewards Tier 2</th>
                <th scope="col">Other reward</th>
                <th scope="col">Allowance</th>
                <th scope="col">Total Incentive Rewards</th>

                <th scope="col">OT Days</th>
                <th scope="col">OT Fees</th>
                <th scope="col">Bonus</th>

                <th scope="col">MPF - Employee Voluntary Contribution (HK)</th>
                <th scope="col">SSB - Social Security Fund Income (MM)</th>
                <th scope="col">Income Tax</th>
                <th scope="col">Other Deduction</th>
                <th scope="col">Total Leaves (Day)</th>
                <th scope="col">Unpaid Leave (Day)</th>
                <th scope="col">Unpaid Leave Deduction</th>

                <th scope="col">Gross Payment</th>
                <th scope="col">Total Deduction</th>

                <th scope="col">Salary++ Payment</th>
                <th scope="col">Salary Est. in SGD</th>
                <th scope="col">Final Payment SGD</th>

                <th scope="col">Mobile No.</th>
                <th scope="col">Receipt Bank</th>
                <th scope="col">Receipt No.</th>
                <th scope="col">Account Name</th>
                <th scope="col">Branch Code</th>
                <th scope="col">Bank Account No</th>

                <th scope="col">Status</th>

            </tr>
        </thead>
        <tbody>
            @forelse($payrolls as $payroll)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td> {{ $payroll->code }} </td>
                <td> {{ $payroll->process_date }} </td>
                <td> {{ $payroll->employee_code }} </td>
                <td> {{ $payroll->employee_name }} </td>
                <td> {{ $payroll->target_month_date }} </td>
                <td> {{ number_format($payroll->basic_salary) }} </td>
                <td> {{ number_format($payroll->basic_salary_sgd) }} </td>

                <td> {{ $payroll->total_days }} </td>
                <td> {{ $payroll->office_off_days }} </td>
                <td> {{ number_format($payroll->hourly_rate) }} </td>
                <td> {{ number_format($payroll->hourly_rate_sgd) }} </td>
                <td> {{ $payroll->regular_working_days }} </td>
                <td> {{ $payroll->actual_working_days }} </td>
                <td> {{ $payroll->counted_working_days }} </td>
                <td> {{ number_format($payroll->counted_working_days_salary) }} </td>
                <td> {{ $payroll->counted_active_time_hours }} </td>
                <td> {{ $payroll->official_90_percent_active_hours }} </td>
                <td> {{ $payroll->official_80_percent_active_hours }} </td>
                <td> {{ $payroll->official_70_percent_active_hours }} </td>

                <td>{{ number_format($payroll->internet_incentive) }}</td>
                <td>{{ number_format($payroll->no_absent_incentive) }}</td>
                <td>{{ number_format($payroll->no_lateness_incentive) }}</td>
                <td>{{ number_format($payroll->work_quality_rewards_tier_1 )}}</td>
                <td>{{ number_format($payroll->work_quality_rewards_tier_2) }}</td>
                <td>{{ number_format($payroll->other_rewards) }}</td>
                <td>{{ number_format($payroll->allowance) }}</td>
                <td>{{ number_format($payroll->total_incentive_reward) }} </td>
                <td>{{ $payroll->ot_days }}</td>
                <td>{{ number_format($payroll->ot_fee) }}</td>
                <td>{{ number_format($payroll->bonus) }}</td>

                <td>{{ number_format($payroll->employee_social_security_fund) }}</td>
                <td>{{ number_format($payroll->employee_voluntary_contribution) }}</td>
                <td>{{ number_format($payroll->income_tax) }}</td>
                <td>{{ number_format($payroll->other_deduction) }}</td>
                <td>{{ number_format($payroll->total_leaves) }}</td>
                <td>{{ number_format($payroll->unpaid_leave) }}</td>
                <td>{{ number_format($payroll->unpaid_leave_deduction) }}</td>

                <td>{{ number_format($payroll->gross_payment) }}</td>
                <td>{{ number_format($payroll->total_deduction) }}</td>

                <td>{{ number_format($payroll->salary_payment) }}</td>
                <td>{{ number_format($payroll->salary_payment_sgd) }}</td>
                <td> {{ number_format($payroll->final_payment_sgd) }} </td>

                <td> {{ $payroll->mobile }} </td>
                <td> {{ $payroll->receipt_bank }} </td>
                <td> {{ $payroll->receipt_no }} </td>
                <td> {{ $payroll->bank_account_name }} </td>
                <td> {{ $payroll->receipt_bank_branch }} </td>
                <td> {{ $payroll->bank_account_no }} </td>

                <td> {{ $payroll->is_sent ? "Sent" : "Pending" }} </td>

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
