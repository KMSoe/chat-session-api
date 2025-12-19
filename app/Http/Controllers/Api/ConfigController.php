<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class ConfigController extends Controller
{
    public function getPaymentStatus()  {
        $data = config('config_json.payment_status');
        return response()->json(['success' => true, 'data' => $data], Response::HTTP_OK);
    }

    public function getOrderStatus()  {
        $data = config('config_json.order_status');
        return response()->json(['success' => true, 'data' => $data], Response::HTTP_OK);
    }

    public function getPurchaseOrderStatus()  {
        $data = config('config_json.purchase_order_status');
        return response()->json(['success' => true, 'data' => $data], Response::HTTP_OK);
    }

    public function getPurchaseReceivedStatus()  {
        $data = config('config_json.purchase_received_status');
        return response()->json(['success' => true, 'data' => $data], Response::HTTP_OK);
    }

    public function getPaymentMethod()  {
        $data = config('config_json.payment_method');
        return response()->json(['success' => true, 'data' => $data], Response::HTTP_OK);
    }

    public function getSaleReturnStatus()  {
        $data = config('config_json.sale_return_status');
        return response()->json(['success' => true, 'data' => $data], Response::HTTP_OK);
    }

    public function getPromotionTypes()  {
        $data = config('config_json.promotion_types');
        return response()->json(['success' => true, 'data' => $data], Response::HTTP_OK);
    }
    public function getPromotionStatus()  {
        $data = config('config_json.promotion_status');
        return response()->json(['success' => true, 'data' => $data], Response::HTTP_OK);
    }

    public function getQualityStatus()  {
        $data = config('sale.quality_status');
        return response()->json(['success' => true, 'data' => $data], Response::HTTP_OK);
    }
}
