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
            $q->where('status_code', '=', 200);
        }])->where('order_from', $admin)->get();

        $transaction = Order::with(['transaction'])->where("order_from", $admin)->orderBy("created_at", "DESC")->get();

        if (count($data) > 0) {

            // CALCULATE TOTAL AMOUNT OF MONEY
            try {
                foreach ($data as $key => $value) {
                    if ($value->transaction != null) {
                        $income[] = $value->transaction->payment->gross_amount;
                        // Produk yang suskes diorder disimpan ke array $success_ordered
                        $success_ordered[] = $value->order_details;
                    } 
                }
                
                $collection = collect($income);
                $income = $collection->pipe(function ($collection) {
                    return $collection->sum();
                });
            } catch (\Throwable $th) {
                error_log("ERROR: Qalculating total amount failed, it seems like there's no transaction with status_code 200");
                Log::info("ERROR: Qalculating total amount failed, it seems like there's no transaction with status_code 200");
                $income = 0;
            }
            // END OF CALCULATE TOTAL AMOUNT OF MONEY
            
            // GET PRODUCT WHICH IS SUCCESS ORDERED
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
                error_log("ERROR: Querying product failed, Product does not exists");
                Log::info("ERROR: Querying product failed, Product does not exists");
            }
            // END OF GET PRODUCT
    
            // GET ALL TRANSACTION BASED ON ADMIN ID
            try {
                //code...
                foreach ($transaction as $key => $value) {
                    $transactions[] = $value->transaction;
                }
            } catch (\Throwable $th) {
                //throw $th;
                error_log("Querying transaction failed it seems the records are empty");
                Log::info("Querying transaction failed it seems the records are empty");
                $transactions = array(); //empty array given
                
            }
            //END OF GET ALL TRANSACTION BASED ON ADMIN ID

            return response()->json([
                "status" => 200,
                "income" => $income,//
                "transactions"  => $transactions,
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
