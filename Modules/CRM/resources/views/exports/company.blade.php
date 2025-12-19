<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Company</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <table class="table table-responsive">
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Code</th>
                <th scope="col">Name</th>
                <th scope="col">Domain</th>
                <th scope="col">Email</th>
                <th scope="col">Phone Dial Code</th>
                <th scope="col">Phone Number</th>
                <th scope="col">Tax</th>
                <th scope="col">Currency</th>
                <th scope="col">Billing Country</th>
                <th scope="col">Billing State</th>
                <th scope="col">Billing District</th>
                <th scope="col">Billing Zip Code</th>
                <th scope="col">Billing Address Line 1</th>
                <th scope="col">Billing Address Line 2</th>
                <th scope="col">Billing Phone Dial Code</th>
                <th scope="col">Billing Phone Number</th>
                <th scope="col">Shipping Same As Billing</th>
                <th scope="col">Shipping Country</th>
                <th scope="col">Shipping State</th>
                <th scope="col">Shipping District</th>
                <th scope="col">Shipping Zip Code</th>
                <th scope="col">Shipping Address Line 1</th>
                <th scope="col">Shipping Address Line 2</th>
                <th scope="col">Shipping Phone Dial Code</th>
                <th scope="col">Shipping Phone Number</th>
                <th scope="col">Status</th>
                <th scope="col">Created By</th>
                <th scope="col">Last Updated</th>
            </tr>
        </thead>
        <tbody>
            @forelse($companies as $index=>$item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->company_code }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->domain }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->phone_dial_code }}</td>
                <td>{{ $item->phone_number }}</td>
                <td>{{ $item->tax?->name }}</td>
                <td>{{ $item->currency?->name }}</td>
                <td>{{ $item->billingCountry?->name }}</td>
                <td>{{ $item->billingState?->name }}</td>
                <td>{{ $item->billing_district }}</td>
                <td>{{ $item->billing_zip_code }}</td>
                <td>{{ $item->billing_address_line_1 }}</td>
                <td>{{ $item->billing_address_line_2 }}</td>
                <td>{{ $item->billing_phone_dial_code }}</td>
                <td>{{ $item->billing_phone_number }}</td>
                <td>{{ $item->shipping_same_as_billing ? 'yes' : 'no' }}</td>
                <td>{{ $item->shippingCountry?->name }}</td>
                <td>{{ $item->shippingState?->name }}</td>
                <td>{{ $item->shipping_district }}</td>
                <td>{{ $item->shipping_zip_code }}</td>
                <td>{{ $item->shipping_address_line_1 }}</td>
                <td>{{ $item->shipping_address_line_2 }}</td>
                <td>{{ $item->shipping_phone_dial_code }}</td>
                <td>{{ $item->shipping_phone_number }}</td>
                <td>{{ $item->status ? 'active' : 'inactive' }}</td>
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