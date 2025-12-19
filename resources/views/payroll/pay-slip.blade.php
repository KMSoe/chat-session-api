<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>{{ $pay_period . ' Pay slip for ' . $payroll->employee?->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"> --}}

    <style>
    @font-face {
        font-family: 'Metropolis';
        src: url('./font/metropolis/Metropolis-Regular.woff2');
    }

    @font-face {
        font-family: 'Metropolis-Medium';
        src: url('./font/metropolis/Metropolis-Medium.woff2');
    }

    @font-face {
        font-family: 'Metropolis-SemiBold';
        src: url('./font/metropolis/Metropolis-SemiBold.woff2');
    }

    @font-face {
        font-family: 'Metropolis-Bold';
        src: url('./font/metropolis/Metropolis-Bold.woff2');
    }

    .metropolis-normal {
    font-family: 'Metropolis';
    font-weight: 400;
    }

    .metropolis-medium {
    font-family: 'Metropolis-Medium';
    font-weight: 500;
    }
    .metropolis-semibold {
    font-family: 'Metropolis-SemiBold';
    font-weight: 600;
    }
    .metropolis-bold {
    font-family: 'Metropolis-Bold';
    font-weight: 700;
    }
    </style>
</head>

<body style="margin: 0; padding: 0; font-family: Metropolis, sans-serif;" class="metropolis-normal">

    <div
        style="max-width: 800px; margin: 0 auto; background-color: white; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); font-size: 14px;">

        <!-- Header using table layout instead of flexbox -->
        <div style="background-color: white; padding: 0; border-bottom: 1px solid #e5e5e5;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="vertical-align: top; width: 60%; padding: 12px 12px 12px 0;">
                        @if ($logo)
                            <img src="{{ $logo }}" alt="{{ $company_name }}"
                                style="width: 120px; margin-bottom: 8px;" />
                        @endif
                        @foreach ($company_info_items as $item)
                            @if (!empty($item['label']) && $item['label'] != 'logo')
                                @if (!empty($item['label']) && $item['label'] != 'logo' && $item['label'] == 'Company Name')
                                    <h1 style="font-size: 28px; margin-bottom: 0;" class="metropolis-bold">{{ $item['value'] }}</h1>
                                @else
                                    <p
                                        style="color: #374151; font-size: 14px; font-weight: 600; text-align: left; margin: 4px 0;">
                                        {{ $item['label'] }}{{ $item['value'] ? ':' : '' }}
                                        {{ $item['value'] ?? '' }}
                                    </p>
                                @endif
                            @endif
                        @endforeach
                    </td>
                    <td style="text-align: right; vertical-align: top; width: 40%; padding: 12px 0 12px 12px;">
                        <h2 style="font-size: 18px; font-weight: 600; color: #374151; margin: 0 0 4px 0;">
                            Payslip For the Month
                        </h2>
                        <h3 style="font-size: 20px; font-weight: bold; color: #374151; margin: 0;" class="metropolis-semibold">{{ $pay_period }}
                        </h3>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Employee Summary Section -->
        <div style="">
            <div style="margin-bottom: 16px;" class="metropolis-normal">
                <h3 style="font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px; text-align: left;">
                    EMPLOYEE SUMMARY
                </h3>
                <div style="text-align: left;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 112px; color: #6b7280; font-size: 12px; padding: 0; vertical-align: top;">
                                Employee Name</td>
                            <td style="color: #374151; font-size: 12px; padding: 0;">:
                                {{ $payroll->employee?->name }}</td>
                        </tr>
                    </table>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 112px; color: #6b7280; font-size: 12px; padding: 0; vertical-align: top;">
                                Pay Period</td>
                            <td style="color: #374151; font-size: 12px; padding: 0;">:
                                {{ $pay_period }}</td>
                        </tr>
                    </table>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 112px; color: #6b7280; font-size: 12px; padding: 0; vertical-align: top;">
                                Pay Date</td>
                            <td style="color: #374151; font-size: 12px; padding: 0;">:
                                {{ $pay_date }}</td>
                        </tr>
                    </table>
                    @foreach ($employee_info_items as $item)
                        @if (!empty($item['value']))
                            <div style="margin-bottom: 4px;">
                                @if (!empty($item['label']))
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td
                                                style="width: 112px; color: #6b7280; font-size: 12px; padding: 0; vertical-align: top;">
                                                {{ $item['label'] }}</td>
                                            <td style="color: #374151; font-size: 12px; padding: 0;">:
                                                {{ $item['value'] }}</td>
                                        </tr>
                                    </table>
                                @else
                                    <span style="color: #374151; font-size: 12px;">{{ $item['value'] }}</span>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Earnings and Deductions using table layout instead of grid -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;" class="metropolis-normal">
                <tr>
                    <td style="width: 48%; vertical-align: top; padding-right: 8px;">
                        <!-- Earnings -->
                        <div style="background-color: #f9fafb; border-radius: 8px; padding: 12px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr style="border-bottom: 1px solid #d1d5db;">
                                    <td style="font-weight: 600; color: #374151; font-size: 12px; padding: 0 0 4px 0;">
                                        EARNINGS</td>
                                    <td
                                        style="font-weight: 600; color: #374151; font-size: 12px; text-align: right; padding: 0 0 4px 0;">
                                        AMOUNT</td>
                                </tr>
                                @foreach ($default_earnings as $item)
                                    <tr>
                                        <td style="color: #4b5563; font-size: 12px; padding: 5px 0;">
                                            {{ $item->name }} :
                                        </td>
                                        <td
                                            style="font-weight: 500; font-size: 12px; text-align: right; padding: 5px 0;">
                                            {{ $payroll->currency?->code . ' ' . number_format($item->amount, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                                @foreach ($template_earnings as $item)
                                    <tr>
                                        <td style="color: #4b5563; font-size: 12px; padding: 5px 0;">
                                            {{ $item->name }} :</td>
                                        <td
                                            style="font-weight: 500; font-size: 12px; text-align: right; padding: 5px 0;">

                                            {{ $payroll->currency?->code . ' ' . number_format($item->amount, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr style="border-top: 1px solid #d1d5db;">
                                    <td style="font-weight: 600; font-size: 12px; padding: 4px 0;">Gross Earnings</td>
                                    <td style="font-weight: 600; font-size: 12px; text-align: right; padding: 4px 0;">
                                        {{ $payroll->currency?->code . ' ' . number_format($gross_salary, 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                    <td style="width: 4%;"></td>
                    <td style="width: 48%; vertical-align: top; padding-left: 8px;">
                        <!-- Deductions -->
                        <div style="background-color: #f9fafb; border-radius: 8px; padding: 12px;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr style="border-bottom: 1px solid #d1d5db;">
                                    <td style="font-weight: 600; color: #374151; font-size: 12px; padding: 0 0 4px 0;">
                                        DEDUCTIONS</td>
                                    <td
                                        style="font-weight: 600; color: #374151; font-size: 12px; text-align: right; padding: 0 0 4px 0;">
                                        AMOUNT</td>
                                </tr>
                                @foreach ($template_deductions as $item)
                                    <tr>
                                        <td style="color: #4b5563; font-size: 12px; padding: 5px 0;">
                                            {{ $item->name }}</td>
                                        <td
                                            style="font-weight: 500; font-size: 12px; text-align: right; padding: 5px 0;">
                                            :
                                            {{ $payroll->currency?->code . ' ' . number_format($item->amount, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr style="border-top: 1px solid #d1d5db;">
                                    <td style="font-weight: 600; font-size: 12px; padding: 4px 0;">Total Deductions</td>
                                    <td style="font-weight: 600; font-size: 12px; text-align: right; padding: 4px 0;">
                                        {{ $payroll->currency?->code . ' ' . number_format($total_deductions, 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Total Net Payable using table instead of flexbox -->
            <div
                style="background-color: #ecfdf5; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="vertical-align: middle; text-align: left;">
                            <h4 style="font-size: 14px; font-weight: 600; color: #14532d; margin: 0 0 4px 0;">
                                TOTAL NET PAYABLE
                            </h4>
                            <p style="font-size: 12px; color: #059669; margin: 0;" class="metropolis-normal">
                                Gross Earnings - Total Deductions
                            </p>
                        </td>
                        <td
                            style="font-size: 18px; font-weight: bold; color: #15803d; text-align: right; vertical-align: middle;" class="metropolis-bold">
                            {{ $payroll->currency?->code . ' ' . number_format($net_pay, 2) }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Amount in Words -->
            <div style="margin-bottom: 16px; text-align: right;">
                <p style="color: #4b5563; font-size: 12px; margin: 0;" class="metropolis-normal">
                    <span style="font-weight: 500;">Amount In Words :</span>
                    {{ $net_pay_in_words . ' ' . $payroll->currency?->code }}
                </p>
            </div>

            <!-- Leave Summary -->
            @if ($payroll_slip_template->enable_leave_section)
                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                    <!-- Leave Summary Header using table instead of flexbox -->
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
                        <tr>
                            <td>
                                <h4 style="font-size: 14px; font-weight: 600; color: #374151; margin: 0;">
                                    LEAVE SUMMARY - {{ $pay_period }}
                                </h4>
                            </td>
                        </tr>
                    </table>

                    <div style="overflow-x: auto;" class="metropolis-normal">
                        <table
                            style="width: 100%; font-size: 12px; border-collapse: collapse; border: 1px solid #d1d5db;">
                            <thead>
                                <tr style="border-bottom: 1px solid #d1d5db;">
                                    <th
                                        style="text-align: center; padding: 8px 12px; color: #374151; font-weight: 600; border-right: 1px solid #e5e7eb;">
                                        Carried Forward
                                    </th>
                                    <th
                                        style="text-align: center; padding: 8px 12px; color: #374151; font-weight: 600; border-right: 1px solid #e5e7eb;">
                                        Entitled
                                    </th>
                                    <th
                                        style="text-align: center; padding: 8px 12px; color: #374151; font-weight: 600; border-right: 1px solid #e5e7eb;">
                                        Taken
                                    </th>
                                    <th
                                        style="text-align: center; padding: 8px 12px; color: #374151; font-weight: 600; border-right: 1px solid #e5e7eb;">
                                        Adjusted
                                    </th>
                                    <th
                                        style="text-align: center; padding: 8px 12px; color: #374151; font-weight: 600;">
                                        Balance
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom: 1px solid #e5e7eb;">
                                    <td
                                        style="padding: 8px 12px; text-align: center; color: #111827; font-weight: 600; border-right: 1px solid #e5e7eb;">
                                        {{ $leave_summary->total_carried_forward ?? 0 }}</td>
                                    <td
                                        style="padding: 8px 12px; text-align: center; color: #111827; font-weight: 600; border-right: 1px solid #e5e7eb;">
                                        {{ $leave_summary->total_entitled ?? 0 }}</td>
                                    <td
                                        style="padding: 8px 12px; text-align: center; color: #111827; font-weight: 600; border-right: 1px solid #e5e7eb;">
                                        {{ $leave_summary->total_taken ?? 0 }}</td>
                                    <td
                                        style="padding: 8px 12px; text-align: center; color: #111827; font-weight: 600; border-right: 1px solid #e5e7eb;">
                                        {{ $leave_summary->total_adjusted ?? 0 }}</td>
                                    <td
                                        style="padding: 8px 12px; text-align: center; color: #111827; font-weight: 600;">
                                        {{ $leave_summary->total_balance ?? 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Leave Policy Note -->
                    <div style="margin-top: 12px; font-size: 12px; color: #6b7280; text-align: center;" class="metropolis-normal">
                        <p style="margin: 0;">
                            * Leave balances are calculated as of {{ $pay_date }} | Unused annual
                            leave may be carried forward as per company policy
                        </p>
                    </div>
                </div>
            @endif

            <!-- Footer -->
            <div
                style="text-align: left; color: #6b7280; font-size: 12px; border-top: 1px solid #e5e7eb; padding-top: 12px;" class="metropolis-normal">
                <p style="margin: 0;">-- This is a system-generated document. --</p>
            </div>
        </div>
    </div>

</body>
