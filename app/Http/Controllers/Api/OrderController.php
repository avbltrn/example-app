<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;

use Illuminate\Support\Facades\Validator;
use Auth;
use DB;
class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'=>'required|exists:products,id',
            'quantity'=>'required|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors'=>$validator->errors()
            ],422);
        }

        $availableStock = DB::table('products')->where('id', $request->product_id)->value('available_stock');
        //$updatedStock = $availableStock - $request->quantity;

        if ($request->quantity > $availableStock){
            return response()->json([
                'message' => 'Failed to order this product due to unavailability of the stock',
            ],400);
        } else {
            $order = Order::create([
                'product_id'=>$request->product_id,
                'quantity'=>$request->quantity
            ]);

            //Product::where('id', $request->product_id)->update(['available_stock' => $updatedStock]);
            Product::where('id', $request->product_id)->decrement('available_stock', $request->quantity);
            
            return response()->json([
                'message' => 'You have successfully ordered this product.'
            ]);
        }
    }
}
