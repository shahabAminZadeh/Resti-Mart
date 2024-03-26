<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Orderitems;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends ApiController
{
    public static function create($request , $amounts , $trackId)
    {
        DB::beginTransaction();
        $order=Order::create([
            'user_id'=>$request->user_id,
            'total_amount'=>$amounts['totalAmount'],
            'deliver_amount'=>$amounts['deliveryAmount'],
            'paying_amount'=>$amounts['payingAmount'],
        ]);
        foreach ($request->order_items as $orderItem)
        {
            $product=Product::findOrFail($orderItem['product_id']);
            Orderitems::create([
                'order_id'=>$order->id,
                'product_id'=>$product->id,
                'price'=>$product->price,
                'quantity'=>$orderItem['quantity'],
                'sub_total'=>($product->price * $orderItem['quantity'])
            ]);
        }
        Transaction::create([
            'user_id'=>$request->user_id,
            'order_id'=>$order->id,
            'amount'=>$amounts['payingAmount'],
            'token'=> $trackId,
            'request_from'=>$request->request_from,
        ]);
        DB::commit();
    }
    public static function update($trackId,$refNumber)
    {
        DB::beginTransaction();
        $transactions=Transaction::where('token',$trackId)->firstOrFail();
        $transactions->update([
            'status'=>1,
            'trans_id'=> $refNumber
        ]);
        //dd($transactions);
        $order=Order::findOrFail($transactions->order_id);
        $order->update([
            'status'=>1,
            'payment_status'=>1
        ]);

        foreach (Orderitems::where('order_id',$order->id)->get() as $item)
        {
            $product=Product::find($item->product_id);
            $product->update([
                'quantity' => ($product->quantity -  $item->quantity)
            ]);
        }
        DB::commit();
    }

}
