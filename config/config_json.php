<?php

return [
    'payment_status' => [
        [
            'id' => 1,
            'name' => 'Pending Payment',   
        ],
        [
            'id' => 2,
            'name' => 'Failed',            
        ],
        [
            'id' => 3,
            'name' => 'Processing',        
        ],
        [
            'id' => 4,
            'name' => 'On Hold',           
        ],
        [
            'id' => 5,
            'name' => 'Completed',         
        ],
        [
            'id' => 6,
            'name' => 'Cancelled',         
        ],
        [
            'id' => 7,
            'name' => 'Refunded'           
        ]
    ],
    'order_status' => [
        [
            'id' => 1,
            'name' => 'Draft'
        ],
        [
            'id' => 2,
            'name' => 'Confirmed'
        ],
        [
            'id' => 3,
            'name' => 'Cancelled'
        ],
        [
            'id' => 5,
            'name' => 'Closed'
        ],
    ],

    'purchase_received_status' => [
        [
            'id' => 1,
            'name' => 'In Transit'
        ],
        [
            'id' => 2,
            'name' => 'Received'
        ],
        [
            'id' => 3,
            'name' => 'Partially Received'
        ],
        [
            'id' => 4,
            'name' => 'Pending'
        ],
        [
            'id' => 5,
            'name' => 'Shipped'
        ],
        [
            'id' => 6,
            'name' => 'Delivered'
        ],
    ],

    'payment_method' => [
        [
            'id' => 1,
            'name' => "Apple Pay"
        ],

        [
            'id' => 2,
            'name' => "Alipay"
        ],

        [
            'id' => 3,
            'name' => "Google Pay"
        ],

        [
            'id' => 4,
            'name' => "Payme"
        ],
        
        [
            'id' => 5,
            'name' => "WeChat Pay"
        ],
    ],

    'purchase_order_status' => [
        [
            'id' => 1,
            'name' => 'Draft'
        ],
        [
            'id' => 2,
            'name' => 'Pending'
        ],
        [
            'id' => 3,
            'name' => 'Approved'
        ],
        [
            'id' => 4,
            'name' => 'Partially Received' 
        ],
        [
            'id' => 5,
            'name' => 'Closed'
        ],
        [
            'id' => 6,
            'name' => 'Cancelled'
        ],

    ],

    'sale_return_status' => [
        [ 
        "id" => 1,
        'name' => 'Requested'
        ],
        [ 
        "id" => 2,
        'name' => 'Pending'
        ],
        [ 
        "id" => 3,
        'name' => 'Confirmed'
        ],
        [ 
        "id" => 4,
        'name' => 'Received'
        ],
        [ 
        "id" => 5,
        'name' => 'Restocked'
        ],
        [ 
        "id" => 6,
        'name' => 'Refunded'
        ],
        [ 
        "id" => 7,
        'name' => 'Rejected'
        ],
        [ 
        "id" => 8,
        'name' => 'Closed'
        ],
        [ 
        "id" => 9,
        'name' => 'Cancelled'
        ]
    ],

    "quality_status" => [
        [
            "id" => 1,
            "name" => "Good"
        ],
        [
            "id" => 2,
            "name" => "Minor Damage"
        ],
        [
            "id" => 3,
            "name" => "Major Damage"
        ],
        [
            "id" => 4,
            "name" => "Defective"
        ],
        [
            "id" => 5,
            "name" => "Needs Inspection"
        ],
        [
            "id" => 6,
            "name" => "Refurbished"
        ],
        [
            "id" => 7,
            "name" => "Scrap"
        ],
    ],

    "promotion_status" => [
        [
            "id" => 1,
            "name" => "Draft"
        ],
        [
            "id" => 2,
            "name" => "Paused"
        ],
        [
            "id" => 3,
            "name" => "Cancelled"
        ],
    ],
    "promotion_types" => [
        [
            "id" => 1,
            "name" => "Flash Sale"
        ],
        [
            "id" => 2,
            "name" => "Coupon Code"
        ],
        [
            "id" => 3,
            "name" => "Free Gift"
        ],
        [
            "id" => 4,
            "name" => "Free Shipping"
        ],
        [
            "id" => 5,
            "name" => "BOGO Offer"
        ],
        [
            "id" => 6,
            "name" => "Holiday Sale"
        ],
        [
            "id" => 7,
            "name" => "Referral Program"
        ],
        [
            "id" => 8,
            "name" => "Abandoned Cart Reminder"
        ],
    ],

    "order_source" => [
        [
            "id" => 1,
            "name" => "ERP"
        ],
        [
            "id" => 2,
            "name" => "Ecommerce"
        ],
        [
            "id" => 3,
            "name" => "POS"
        ],
    ],

    "stock_transfer_status" => [
        [
            "id" => 0,
            "name" => "Pending"
        ],
        [
            "id" => 1,
            "name" => "Received"
        ],
        [
            "id" => 2,
            "name" => "Confirmed"
        ],
        [
            "id" => 3,
            "name" => "Closed"
        ],
        [
            "id" => 4,
            "name" => "Shipped"
        ],
        [
            "id" => 5,
            "name" => "Cancelled"
        ],
        [
            "id" => 6,
            "name" => "Returned"
        ],
    ],

    "stock_adjustment_status" => [
        [
            "id" => 0,
            "name" => "Pending"
        ],
        [
            "id" => 1,
            "name" => "Confirmed"
        ],
        [
            "id" => 2,
            "name" => "Closed"
        ],
        [
            "id" => 3,
            "name" => "Cancelled"
        ],
        [
            "id" => 4,
            "name" => "Reversed"
        ],
    ]

];
