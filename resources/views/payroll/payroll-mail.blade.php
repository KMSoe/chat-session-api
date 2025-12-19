<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="viewport" id="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>{{ Config::get('app.name') }}</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('email_images/icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href=".{{ asset('email_images/icon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('email_images/icon.png') }}">
    <link rel="shortcut icon" href="{{ asset('email_images/icon.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('email_images/icon.png') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Poppins", sans-serif;
        }

        body * {
            font-family: "Poppins", sans-serif;
        }

        table {
            /* max-width: 580px;
            width: 100%; */
        }

        .me-email-name {
            font-size: 16px;
            font-weight: normal;
        }

        p {
            margin-bottom: 0;
            margin-top: 0;
        }

        .blue-div {
            margin-top: -1px !important;
        }
    </style>
    <style media="all and (max-width: 400px)">
        table table td {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        table td {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        .no-padding-table td {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        td img {
            width: 50px !important;
            height: 50px !important;
        }

        .me-email-policy-div a,
        .me-email-footer-copyright,
        .custom-text {
            font-size: 14px !important;
        }

        .custom-text-20 {
            font-size: 16px !important;
        }
    </style>
    <style media="all and (max-width: 400px)">
        table table td {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        table td {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        .no-padding-table td {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        td img {
            width: 50px !important;
            height: 50px !important;
        }

        .me-email-policy-div a,
        .me-email-footer-copyright,
        .custom-text {
            font-size: 14px !important;
        }

        .custom-text-20 {
            font-size: 16px !important;
        }
    </style>
    <div style="max-width: 640px;margin: 0 auto;">
        <div class="body-div"
            style="background: linear-gradient(to bottom, #F3F6F9 312px, #464E5F 312px);margin: 0 auto;margin-top: 2rem;max-width: 640px;">
            <div style="text-align: center;background-color: #F3F6F9;
            padding: 48px 36px 24px 36px;
            text-align: center;">
                <img src="{{ $logo }}" alt="{{ $company_name }}" style="margin: 0px auto;" />
            </div>
            <div style="margin: 0 30px;">
                <table bgColor="white" align="center" valign="middle" border="0"
                    style="background-color: white !important;position: relative;border-radius: 12px;width: 100%;">
                    <tr>
                        <td>
                            <table width="100%" class="no-padding-table" bgColor="white" align="center" valign="middle"
                                border="0" style="width: 100%;">
                                <tr>
                                    <td style="padding:0 36px;padding-top: 26px;text-align: center;">
                                        <img width="80" height="80" src="{{ asset('email_images/approve-date.png') }}"
                                            alt="approve-date" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="no-padding-table" bgColor="white" align="left" valign="middle" border="0"
                                style="width: 100%;">
                                <tr>
                                    <td
                                        style="padding-left:36px;padding-right: 36px;padding-bottom: 22px;padding-top: 12px; text-align: center;">
                                        <p style="font-size: 18px;color: #1B3757;">
                                            <b>Your Salary has been transferred</b></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="no-padding-table" bgColor="white" align="left" valign="middle" border="0" style="width: 100%;">
                                <tr>
                                    <td style="padding:0 36px;">
                                        <p class="custom-text"
                                            style="font-size: 16px; margin: 0px 0px 0px 0px; font-weight: 400; color: #1B3757;">
                                            Hello {{ $payroll->employee?->name }},</span>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 18px;padding-top: 18px;">
                            <table class="no-padding-table" bgColor="white" align="left" valign="middle" border="0" style="width: 100%;">
                                <tr>
                                    <td style="padding:0 36px;">
                                        <p class="custom-text"
                                            style="font-size: 16px; margin: 0px; font-weight: 400; color: #333;">
                                            Your <b>salary payroll</b> for <b>{{ $target_month }}</b> has been transferred.</b>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 16px;">
                            <table class="no-padding-table" bgColor="white" align="left" valign="middle" border="0" style="width: 100%;">
                                <tr>
                                    <td style="padding:0 36px;">
                                        <p style="margin-top: 0px; font-size: 1rem;line-height: normal;
                                    font-weight: normal;
                                    color: #1B3757;"><span style="display:block;margin-bottom: 2px;">Regards,</span><b>
                                                {{ Config::get('app.name') }}</b></p>
                                                <hr style="margin: 32px 0;background-color: #DADBE4;width: 100%;">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div style="background-color: #464E5F;max-width: 640px;margin: 0 auto;padding-top: 2px;">
        <div style="margin-bottom: 12px;
        padding: 32px 36px;">
            <p style=" font-size: 10px;
            font-weight: normal;
            color: #fff;
            text-align: center;
            margin-top: 0;
            margin-bottom: 0;">©{{ now()->year }} {{ $company_name }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
