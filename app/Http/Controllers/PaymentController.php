<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends ApiController
{
    /// add to .env file
    /// ZIBAL_IR_API_KEY=zibal
    /// ZIBAL_IR_CALLBACK_URL=http://localhost/laravel_api_project/public/payment/verify

    public function send(Request $request)
    {
        ///validation
        $validator = Validator::make($request->all(),[

            'user_id'=> 'required',
            'order_items'=> 'required',
            'order_items.*.product_id'=> 'required|integer',
            'order_items.*.quantity'=> 'required|integer',
            'request_from'=> 'required',
        ]);
        if ($validator->fails())
        {
            return $this->errorResponse($validator->messages(),422);
        }
        ////////to calculate the price
        $totalAmount=0;
        $deliveryAmount=0;
        foreach ($request->order_items as $orderItem)
        {
            $product=Product::findOrFail($orderItem['product_id']);
            if ($product->quantity < $orderItem['quantity'])
            {
                return $this->errorResponse('The product quantity is incorrect', 422);
            }
            $totalAmount +=$product->price * $orderItem['quantity'];
            $deliveryAmount += $product-> deliver_amount;
        }
        $payingAmount=$totalAmount+$deliveryAmount;
        $amounts=
            [
                'totalAmount'=>$totalAmount,
                'deliveryAmount'=>$deliveryAmount,
                'payingAmount'=>$totalAmount,
            ];



        $merchant = env('ZIBAL_IR_API_KEY');
        $amount = $payingAmount;
        $mobile = "شماره موبایل";
        $description = "توضیحات";
        $callbackUrl = env('ZIBAL_IR_CALLBACK_URL');
        $result = $this->sendRequest($merchant, $amount, $callbackUrl, $mobile, $description);

        $result = json_decode($result);


        //dd($result);
        if ( $result->result == 100) {
            OrderController::create($request,$amounts,$result->trackId);
            $go = "https://gateway.zibal.ir/start/$result->trackId";
            return $this->successResponse([
                'url' => $go
            ]);
        } else {
            return $this->errorResponse('تراکنش با خطا مواجه شد', 422);
        }

    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trackId' => 'required',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        $merchant = env('ZIBAL_IR_API_KEY');
        $trackId = $request->trackId;
        $result = json_decode($this->verifyRequest($merchant, $trackId));
        return response()->json($result);
        if (isset($result->status)) {
            if ($result->status == 1) {
                if(Transaction::where('refNumber', $result->refNumber)->exists()){
                    return $this->errorResponse('این تراکنش قبلا توی سیستم ثبت شده است' , 422);
                }
                OrderController::update('token', $result->refNumber);
                return $this->successResponse('تراکنش با موفقیت انجام شد' , 200);
            } else {
                return $this->errorResponse('تراکنش با خطا مواجه شد' , 422);
            }
        } else {
            if ($request->status == 0) {
                return $this->errorResponse('تراکنش با خطا مواجه شد' , 422);
            }
        }
    }
    public function sendRequest($merchant, $amount, $callbackUrl, $mobile = null, $description = null)
    {
        return $this->curl_post('https://gateway.zibal.ir/v1/request', [
            'merchant'     => $merchant,
            'amount'       => $amount,
            'callbackUrl'  => $callbackUrl,
            'mobile'       => $mobile,
            'description'  => $description,
        ]);
    }

    function verifyRequest($merchant, $trackId)
    {
        return $this->curl_post('https://gateway.zibal.ir/verify', [
            'merchant' => $merchant,
            'trackId'  => $trackId,
        ]);
    }

    public function curl_post($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }
}
