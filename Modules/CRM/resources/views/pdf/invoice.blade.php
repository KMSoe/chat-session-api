<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation {{ $invoice->quotation_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 40px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu',
                'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
            background-color: #ffffff;
            color: #464E5F;
            padding: 40px;
            font-size: 9pt;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }

        .logo {
            flex: 1;
        }

        .title {
            flex: 1;
            text-align: right;
        }

        .quotation-title {
            font-size: 28pt;
            font-weight: bold;
            color: #464E5F;
            margin: 0;
            line-height: 1.2;
        }

        .quotation-number {
            text-align: right;
            font-size: 10pt;
            font-weight: 600;
            color: #464E5F;
            margin: 4px 0 8px 0;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 20px;
        }

        .info-left {
            flex: 1;
            display: flex;
            gap: 20px;
        }

        .bill-to, .ship-to {
            flex: 1;
        }

        .info-right {
            flex: 1;
            max-width: 45%;
        }

        .section-label {
            font-size: 8pt;
            color: #7E8299;
            margin-bottom: 4px;
            font-weight: normal;
        }

        .company-name {
            font-size: 10pt;
            color: #464E5F;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .contact-text {
            font-size: 8pt;
            color: #7E8299;
            margin-bottom: 2px;
            line-height: 1.3;
            white-space: pre-line;
        }

        .meta-table {
            width: 100%;
            margin-top: 8px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
        }

        .meta-label {
            font-size: 8pt;
            color: #464E5F;
            font-weight: 600;
            flex: 1;
        }

        .meta-value {
            font-size: 8pt;
            color: #464E5F;
            text-align: right;
            flex: 1;
            word-break: break-word;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 10px;
        }

        .items-table thead th {
            background-color: #464e5f;
            color: #ffffff;
            font-size: 9pt;
            font-weight: 600;
            padding: 12px 10px;
            text-align: center;
        }

        .items-table thead th:first-child {
            text-align: left;
            padding-left: 20px;
        }

        .items-table thead th:nth-child(2) {
            text-align: left;
            padding-left: 20px;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .items-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .items-table tbody td {
            font-size: 9pt;
            color: #464E5F;
            padding: 12px 10px;
            vertical-align: top;
            text-align: center;
        }

        .items-table tbody td:first-child {
            text-align: left;
            padding-left: 20px;
        }

        .items-table tbody td:nth-child(2) {
            text-align: left;
            padding-left: 20px;
        }

        .items-table tbody td:last-child {
            text-align: right;
            padding-right: 20px;
        }

        .item-title {
            font-size: 9pt;
            color: #464E5F;
            margin-bottom: 4px;
        }

        .item-description {
            font-size: 8pt;
            color: #7E8299;
            line-height: 1.4;
            white-space: pre-line;
        }

        .divider {
            border: none;
            border-top: 1px solid #E5E7EB;
            margin: 10px 0;
        }

        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .totals-content {
            width: 30%;
            min-width: 250px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
        }

        .total-label {
            font-size: 9pt;
            color: #7E8299;
            text-align: right;
            padding-right: 20px;
            flex: 1;
        }

        .total-value {
            font-size: 9pt;
            color: #7E8299;
            text-align: right;
            width: 100px;
        }

        .total-row.bold .total-label,
        .total-row.bold .total-value {
            font-weight: bold;
        }

        .notes-section {
            margin-top: 30px;
            margin-bottom: 20px;
        }

        .notes-title {
            font-size: 10pt;
            color: #464E5F;
            margin-bottom: 8px;
        }

        .notes-content {
            font-size: 8pt;
            color: #464E5F;
            line-height: 1.5;
            white-space: pre-line;
        }

        .signatures-section {
            display: flex;
            gap: 60px;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .signature-column {
            flex: 1;
        }

        .signature-title {
            font-size: 8pt;
            font-weight: bold;
            color: #464E5F;
            margin-bottom: 15px;
        }

        .signature-block {
            margin-bottom: 20px;
        }

        .signature-image-container {
            width: 200px;
            height: 70px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .signature-image {
            max-width: 200px;
            max-height: 70px;
            object-fit: contain;
        }

        .signature-line {
            width: 200px;
            border-top: 1px solid #E5E7EB;
            margin: 5px 0 8px 0;
        }

        .signature-label {
            font-size: 8pt;
            color: #464E5F;
            margin-bottom: 3px;
        }

        .signature-date {
            font-size: 7pt;
            color: #7E8299;
        }

        .footer {
            position: fixed;
            bottom: 30px;
            left: 40px;
            right: 40px;
        }

        .footer-divider {
            border: none;
            border-top: 1px dashed #7E8299;
            margin-bottom: 10px;
        }

        .footer-info {
            text-align: center;
            font-size: 8pt;
            color: #464E5F;
        }

        .footer-info span {
            margin: 0 12px;
        }

        /* Prevent page breaks inside important sections */
        .no-page-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <svg width="140" height="29" viewBox="0 0 160 33" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_17678_62966)">
                    <path d="M0.0874023 8.97852H3.34226L10.2683 21.1312L17.1871 8.97852H20.2553L11.4525 24.084C11.3391 24.3368 11.16 24.5536 10.9343 24.7112C10.7086 24.8688 10.4449 24.9612 10.1714 24.9785C9.89425 24.9682 9.62547 24.8797 9.39532 24.7229C9.16517 24.5661 8.98279 24.3474 8.86871 24.0912L0.0874023 8.97852Z" fill="#00DB92" />
                    <path d="M22.0391 24.9785V8.97852H24.709V24.9785H22.0391Z" fill="#00DB92" />
                    <path d="M27.5008 24.9785V22.1822H38.9449C40.3803 22.1822 41.1698 21.3458 41.1698 20.0912C41.1698 18.7276 40.3731 18.0258 38.9449 18.0258H31.9328C29.0619 18.0258 27.2676 16.1094 27.2676 13.4694C27.2676 10.8912 28.9291 8.97852 31.9722 8.97852H42.9354V11.7676H31.9758C30.7557 11.7676 30.0452 12.4949 30.0452 13.7276C30.0228 13.9875 30.0567 14.2493 30.1447 14.4945C30.2327 14.7397 30.3725 14.9624 30.5544 15.1471C30.7363 15.3317 30.9559 15.4739 31.1977 15.5635C31.4395 15.6531 31.6977 15.688 31.9543 15.6658H38.9449C42.0347 15.6658 43.6926 17.1894 43.6926 20.2876C43.6926 22.9712 42.1387 24.9749 38.9449 24.9749L27.5008 24.9785Z" fill="#00DB92" />
                    <path d="M46.1328 24.9785V8.97852H48.8027V24.9785H46.1328Z" fill="#00DB92" />
                    <path d="M51.5732 24.9785V8.97852H62.7481C65.7339 8.97852 67.7076 10.3858 67.7076 13.1131C67.7076 15.1385 66.7207 16.1603 65.5867 16.524C66.9719 16.9894 67.9803 18.2658 67.9803 20.1312C67.9803 23.0403 66.0461 24.9712 63.0639 24.9712L51.5732 24.9785ZM56.1738 18.0294V15.6658H62.7697C64.2841 15.6658 64.9982 15.1167 64.9982 13.7312C64.9982 12.0367 63.7171 11.7712 61.7433 11.7712H54.2432V22.2003H61.973C63.8857 22.2003 65.2709 21.6512 65.2709 20.0876C65.2709 18.7676 64.4527 18.0222 62.9599 18.0222L56.1738 18.0294Z" fill="#00DB92" />
                    <path d="M71.645 28.5V9.5H84.9152C88.4608 9.5 90.8046 11.1711 90.8046 14.4098C90.8046 16.815 89.6327 18.0284 88.286 18.4602C89.931 19.013 91.1284 20.5286 91.1284 22.7439C91.1284 26.1984 88.8315 28.4914 85.2902 28.4914L71.645 28.5ZM77.1082 20.248V17.4411H84.9408C86.7391 17.4411 87.5872 16.7891 87.5872 15.1439C87.5872 13.1316 86.0658 12.8164 83.722 12.8164H74.8155V25.2009H83.9947C86.2661 25.2009 87.911 24.5489 87.911 22.692C87.911 21.1245 86.9394 20.2393 85.1666 20.2393L77.1082 20.248Z" fill="#00DB92" />
                    <path d="M93.9111 28.5V9.5H97.0774V25.175H109.278V28.5H93.9111Z" fill="#00DB92" />
                    <path d="M123.779 11.8566L120.909 10.1729L122.017 8.71838C122.135 8.53317 122.18 8.30999 122.143 8.09293C122.108 7.86817 121.99 7.66532 121.813 7.52566C121.642 7.3905 121.426 7.32676 121.21 7.34747C120.993 7.36585 120.791 7.46572 120.643 7.62747L119.391 9.27475L115.978 7.27111C115.909 7.23201 115.831 7.21195 115.752 7.21293C115.674 7.2119 115.597 7.23199 115.529 7.27111L112.099 9.29656L110.832 7.62747C110.687 7.45504 110.482 7.34568 110.259 7.32199C110.037 7.29831 109.814 7.3621 109.637 7.5002C109.46 7.63987 109.342 7.84272 109.307 8.06747C109.274 8.29418 109.329 8.52495 109.461 8.71111L110.581 10.1838L107.746 11.8602C107.677 11.9005 107.62 11.9581 107.581 12.0275C107.541 12.0973 107.52 12.1762 107.52 12.2566V21.3947C107.52 21.4751 107.541 21.554 107.58 21.6236C107.62 21.6932 107.677 21.7509 107.746 21.7911L115.544 26.362C115.611 26.4022 115.688 26.4236 115.766 26.4238C115.846 26.4235 115.923 26.4022 115.992 26.362L123.794 21.7911C123.863 21.7509 123.921 21.6933 123.963 21.6238C124.001 21.5539 124.021 21.4749 124.02 21.3947V12.2529C124.019 12.1709 123.997 12.0907 123.955 12.0202C123.912 11.9506 123.852 11.894 123.779 11.8566ZM123.212 19.8202L115.773 23.4566L108.338 19.8202V18.5475L115.773 22.3329L123.205 18.5475L123.212 19.8202ZM123.212 16.402L115.773 20.0602L108.341 16.402V15.1293L115.773 18.9147L123.205 15.1293L123.212 16.402Z" fill="#FFAE00" />
                </g>
                <defs>
                    <clipPath id="clip0_17678_62966">
                        <rect width="159" height="32" fill="white" transform="translate(0.445312 0.978516)" />
                    </clipPath>
                </defs>
            </svg>
        </div>
        <div class="title">
            <h1 class="quotation-title">QUOTATION</h1>
        </div>
    </div>

    <div class="quotation-number">{{ $invoice->quotation_number }}</div>

    <!-- Bill To / Ship To & Meta Info -->
    <div class="info-section">
        <div class="info-left">
            <div class="bill-to">
                <div class="section-label">Bill To</div>
                <div class="company-name">{{ $invoice->company?->name ?? 'N/A' }}</div>
                <div class="contact-text">{{ $invoice->contact ? $invoice->contact->first_name . ' ' . $invoice->contact->last_name : 'N/A' }}</div>
                <div class="contact-text">{{ $invoice->company?->billing_address ?? '---' }}</div>
            </div>
            <div class="ship-to">
                <div class="section-label">Ship To</div>
                <div class="company-name">{{ $invoice->company?->name ?? 'N/A' }}</div>
                <div class="contact-text">{{ $invoice->contact ? $invoice->contact->first_name . ' ' . $invoice->contact->last_name : 'N/A' }}</div>
                <div class="contact-text">{{ $invoice->company?->shipping_address ?? '---' }}</div>
            </div>
        </div>
        <div class="info-right">
            <div class="meta-table">
                <div class="meta-row">
                    <div class="meta-label">Reference No :</div>
                    <div class="meta-value">{{ $invoice->reference_number ?? 'N/A' }}</div>
                </div>
                <div class="meta-row">
                    <div class="meta-label">Quotation Date :</div>
                    <div class="meta-value">{{ $invoice->quotation_date->format('Y-m-d') }}</div>
                </div>
                <div class="meta-row">
                    <div class="meta-label">Expire Date :</div>
                    <div class="meta-value">{{ $invoice->expiry_date?->format('Y-m-d') ?? 'N/A' }}</div>
                </div>
                <div class="meta-row">
                    <div class="meta-label">Sale Person :</div>
                    <div class="meta-value">{{ $invoice->salePerson?->name ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Item & Description</th>
                <th style="width: 10%;">Qty</th>
                <th style="width: 10%;">Rate</th>
                @if($invoice->discount_level === 'item')
                <th style="width: 10%;">Discount</th>
                @endif
                @if($invoice->taxation_level === 'item')
                <th style="width: 15%;">Tax</th>
                @endif
                <th style="width: 15%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <div class="item-title">{{ $item->item?->title ?? 'N/A' }}</div>
                    @if($item->description)
                    <div class="item-description">{{ $item->description }}</div>
                    @endif
                </td>
                <td>{{ number_format($item->quantity, 2) }}</td>
                <td>{{ number_format($item->rate, 2) }}</td>
                @if($invoice->discount_level === 'item')
                <td>
                    @if($item->discount_value > 0)
                        {{ $item->discount_type === 'percentage' ? number_format($item->discount_value, 2) . '%' : number_format($item->discount_value, 2) }}
                    @else
                        0.00
                    @endif
                </td>
                @endif
                @if($invoice->taxation_level === 'item')
                <td>{{ $item->tax ? $item->tax->name . ' [' . number_format($item->tax->rate, 2) . '%]' : 'N/A' }}</td>
                @endif
                <td>{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals Section -->
    <hr class="divider" />

    <div class="totals-section">
        <div class="totals-content">
            <div class="total-row">
                <div class="total-label">Sub Total</div>
                <div class="total-value">{{ number_format($invoice->subtotal, 2) }}</div>
            </div>

            @if($invoice->discount_value > 0)
            <div class="total-row">
                <div class="total-label">
                    Discount
                    @if($invoice->discount_type === 'percentage')
                        ({{ number_format($invoice->discount_value, 2) }}%)
                    @endif
                </div>
                <div class="total-value">
                    @if($invoice->discount_type === 'percentage')
                        {{ number_format(($invoice->subtotal * $invoice->discount_value / 100), 2) }}
                    @else
                        {{ number_format($invoice->discount_value, 2) }}
                    @endif
                </div>
            </div>
            @endif

            @if($invoice->taxation_level === 'invoice' && $invoice->tax)
            <div class="total-row">
                <div class="total-label">{{ $invoice->tax->name }} ({{ number_format($invoice->tax->rate, 2) }}%)</div>
                <div class="total-value">{{ number_format($invoice->tax_amount, 2) }}</div>
            </div>
            @elseif($invoice->taxation_level === 'item' && $invoice->tax_amount > 0)
            <div class="total-row">
                <div class="total-label">Total Tax</div>
                <div class="total-value">{{ number_format($invoice->tax_amount, 2) }}</div>
            </div>
            @endif

            <div class="total-row bold">
                <div class="total-label">Total</div>
                <div class="total-value">{{ $invoice->currency?->code ?? 'USD' }} {{ number_format($invoice->grand_total, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Customer Note -->
    @if($invoice->customer_note)
    <div class="notes-section no-page-break">
        <div class="notes-title">Customer Note</div>
        <div class="notes-content">{{ $invoice->customer_note }}</div>
    </div>
    @endif

    <!-- Terms & Conditions -->
    @if($invoice->terms_and_conditions)
    <div class="notes-section no-page-break">
        <div class="notes-title">Terms & Conditions</div>
        <div class="notes-content">{{ $invoice->terms_and_conditions }}</div>
    </div>
    @endif

    <!-- Signatures -->
    @if($invoice->enable_organization_signature || $invoice->enable_customer_signature)
    <div class="signatures-section no-page-break">
        @if($invoice->enable_organization_signature && $invoice->organizationSignatures->count() > 0)
        <div class="signature-column">
            <div class="signature-title">Authorized Signature</div>
            @foreach($invoice->organizationSignatures as $signature)
            <div class="signature-block">
                @if($signature->signatureFile)
                <div class="signature-image-container">
                    <img src="{{ $signature->signatureFile->path }}" alt="{{ $signature->label }}" class="signature-image" />
                </div>
                @endif
                <div class="signature-line"></div>
                <div class="signature-label">{{ $signature->label }}</div>
                @if($signature->signed_at)
                <div class="signature-date">Signed: {{ $signature->signed_at->format('Y-m-d H:i') }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        @if($invoice->enable_customer_signature && $invoice->customerSignatures->count() > 0)
        <div class="signature-column">
            <div class="signature-title">Receiver Signature</div>
            @foreach($invoice->customerSignatures as $signature)
            <div class="signature-block">
                @if($signature->signatureFile)
                <div class="signature-image-container">
                    <img src="{{ $signature->signatureFile->path }}" alt="{{ $signature->label }}" class="signature-image" />
                </div>
                @endif
                <div class="signature-line"></div>
                <div class="signature-label">{{ $signature->label }}</div>
                @if($signature->signed_at)
                <div class="signature-date">Signed: {{ $signature->signed_at->format('Y-m-d H:i') }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    <!-- Footer - Will appear on every page -->
    <div class="footer">
        <hr class="footer-divider" />
        <div class="footer-info">
            @if($invoice->company?->phone)
            <span>Contact : {{ $invoice->company->phone }}</span>
            @endif
            @if($invoice->company?->email)
            <span>Email : {{ $invoice->company->email }}</span>
            @endif
            @if($invoice->company?->website)
            <span>Web : {{ $invoice->company->website }}</span>
            @endif
        </div>
    </div>
</body>
</html>