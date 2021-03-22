<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:admin')->except('logout');
    }

    public function dashboard()
    {
        return view('pages.dashboard');
    }

    public function getDashboardInfo()
    {
        $admin = Auth::guard('admin')->user()->id;

        $data = Order::with(['transaction' => function ($q) {
            $q->with('payment')->where('status_code', '=', 200);
        }])->where('order_from', $admin)->orderBy('created_at')->get();

        $transaction = Order::with(['transaction'])->where("order_from", $admin)->orderBy("created_at", "DESC")->get();

        // dd($data);
        $x = $transaction->map(function($item, $key){
            return $item->transaction;
        });
        
        if (count($data) > 0) {

            try {
                $income = 0;
                foreach ($data as $key => $value) {
                    if ($value->transaction != null) {

                        //hitung income
                        $income += $value->transaction->payment->gross_amount;
                        // Produk yang suskes diorder disimpan ke array $success_ordered
                        $success_ordered[] = $value->order_details;
                    } 
                }
                
            // dd($transactions);
            } catch (\Throwable $th) {

                error_log("ERROR: Qalculating total amount failed, it seems like there's no transaction with status_code 200");
                Log::info("ERROR: Qalculating total amount failed, it seems like there's no transaction with status_code 200");

                $income = 0;
                $success_ordered = [];
            }
            // END OF CALCULATE TOTAL AMOUNT OF MONEY
            
            // GET PRODUCT WHICH IS SUCCESFULLY ORDERED
            try {
                foreach ($success_ordered as $key => $value) {
                    foreach ($value as $key => $value2) {
                       $products["product"][] = Product::findOrFail($value2->product_id);
                       $products["order_id"][] = $value2->order_id;
                       $products["order_quantity"][] = $value2->order_quantity;
                    }
                } 

            } catch (\Throwable $th) {
                //throw $th;
                $products["product"] = array();
                $products["order_id"] = array();
                $products["order_quantity"] = array();
                error_log("ERROR: Querying product failed, Product does not exists");
                Log::info("ERROR: Querying product failed, Product does not exists");
            }
            // END OF GET PRODUCT
    

            return response()->json([
                "income" => $income,//
                "transactions"  => $x->all(),
                "orders_id" => $products["order_id"],
                "products" => $products["product"],
                "orders_quantity" => $products["order_quantity"],
            ]);
            
        } else {
            return response()->json([
                "status" => 500,
                "error" => "Internal server error"
            ]);
        }
    }
}
