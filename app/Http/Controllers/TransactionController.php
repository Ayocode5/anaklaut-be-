<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


interface Transactions {
    public function findTransactionDetailsByAdminId($id);
}

// BUSINESS LOGIC
class TransactionDetails implements Transactions {

    public function findTransactionDetailsByAdminId($id)
    {
         // GET DATA ORDERS BY ADMIN ID
         $data = Order::with(['transaction', 'order_details'])->where('order_from', $id)->orderBy("created_at", "DESC")->take(20)->get();
         
         if (count($data) > 0) {
 
             foreach ($data as $key => $value) {
                 // AMBIL DATA DARI RELASI "transaction" dan "order_details"
                 $transactions[] = $data[$key]->transaction; // relasi "transaction"
                 $order_details[] = $data[$key]->order_details; // relasi "order_details"
             }
 
             foreach ($order_details as $key => $value) {
                 foreach ($value as $key2 => $value2) {
                     if($value2->product_id != null) {
                         // Mengambil semua produk berdasarkan yg ada pada RECORD ORDER_DETAILS
                         // Lalu setiap RECORD ORDER_DETAILS dikelompokkan berdasarkan order_id 
                         // (Order id sebagai array key) yang dimiliki ORDER_DETAILS
                         $transactionDetails[strval($order_details[$key][$key2]->order_id)]["detail_products"][] = Product::with(['product_galleries'])->where('id', $value2->product_id)->get();
                         $transactionDetails[strval($order_details[$key][$key2]->order_id)]["transaction_data"][] = $transactions[$key];
                         $transactionDetails[strval($order_details[$key][$key2]->order_id)]["order_quantities"][] = $value2->order_quantity;
                     }
                 }
             }
             
             return $transactionDetails;
 
         } else {
             return $transactionDetails = [];
         }
 
    }
}

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin')->only('index');
        $this->_midtrans_init(); //initialize midtrans payment gateway
    }

    public function index()
    {
        $ownerId = Auth::guard('admin')->user()->id;
       
        //Mengambil Detail Transaksi
        $transaction = new TransactionDetails;
        $products = $transaction->findTransactionDetailsByAdminId($ownerId);
        
        //Mengambil key setiap array yg mana key-key tersebut digunakan untuk key index 
        $order_keys = array_keys($products);

        return view('pages.admin.transactions.index', compact(['products','order_keys']));
    }

    // FUNGSI UNTUK UJI COBA
    public function checkout()
    {
        return view('pages.checkout');
    }

    public function status($orderId) {
        try {
            //code...
            $status =  \Midtrans\Transaction::status($orderId);
            return view('pages.admin.transactions.status', compact('status'));

        } catch (\Throwable $th) {
            return abort(403, $th->getMessage());
        }
    }

    public function refund($orderId) {
        $params = array(
            'refund_key' => 'order1-ref1',
            'amount' => 50000,
            'reason' => 'Item out of stock'
        );
        $status =  \Midtrans\Transaction::refund($orderId, $params);
        dd($status);
    }

    public function approve($orderId) {
        $status =  \Midtrans\Transaction::approve($orderId);
        dd($status);
    }




    //API
    public function token(Request $request)
    {
    
        $orders = $request->input('orders');

        foreach ($orders as $key => $value) {
            //value order_from dijadikan array key untuk value dari order_data
            $order_in[$value['order_from']][] = $value['order_data'];
            try {
                // GET PRODUCT FROM DATABASE BASED ON INPUT JSON
                $product["items"][] = Product::findOrFail(intval($value['order_data']['product_id']));
                // SET PRODUCT QUANTITY ORDERS
                $product["quantity"][] = $value['order_data']['quantity'];
                Log::info("Query produk berhasil"); error_log("Query produk berhasil");
            } catch (\Throwable $th) {
                // throw $th;
            }
        }
        
        //BARANG HASIL INPUT
        Log::info("BARANG PESANAN MASUK");
        Log::info($order_in);
        
        //GET KEYS OF ARRAY
        Log::info("PESAN DARI ADMIN ID ...");
        $orderFrom = array_keys($order_in);
        Log::info($orderFrom);

        //GET ADMIN BY IT'S ID
        $admin = Admin::findOrFail($orderFrom[0]);

        // MENYIAPKAN ARRAY ORDER DETAIL UNTUK KEMUDIAN DI PROSES OLEH MIDTRANS
        error_log("Menyiapkan data order");
        foreach ($product["items"] as $key => $value) {
            $item_order_detials[] = array(
                'id' => $value->id,
                'price' => $value->price,
                'quantity' => $product['quantity'][$key],
                'name' => $value->name
            );
        }

        Log::info("ITEM ORDER DETAILS");
        Log::info($item_order_detials);


        error_log('masuk ke snap token dri ajax');

        $time = time();
        $params = array(
            'transaction_details' => array(
                'order_id' => uniqid("IYN"),
                'gross_amount' => 1,
            ),

            "enabled_payments" => array(
                "bca_va",
                "gopay",
                "indomaret"
            ),

            "bca_va" => array(
                "va_number" => strval($admin->rek_num),
                "free_text" => [
                    "inquiry" => [
                        [
                            "en" => "text in English",
                            "id" => "text in Bahasa Indonesia"
                        ]
                    ],
                    "payment" => [
                        [
                            "en" => "text in English",
                            "id" => "text in Bahasa Indonesia"
                        ]
                    ]
                ]
            ),

            'customer_details' => array( //DATA MASIH STATIS
                'first_name' => 'customer',
                'last_name' => '1',
                'email' => 'customer@example.com',
                'phone' => '08111222333',
                'shipping_address' => array(
                    'first_name'    => "customer",
                    'last_name'     => "1",
                    'email'         => 'customer@example.com',
                    'phone'         => '08111222333',
                    'address'       => "Bakerstreet 221B.",
                    'city'          => "Jakarta",
                    'postal_code'   => "51162",
                    'country_code'  => 'IDN'

                ),
                "billing_address" => array(
                    "first_name"    => "customer",
                    "last_name"     => "1",
                    'email'         => 'customer@example.com',
                    'phone'         => '08111222333',
                    'address'       => "Bakerstreet 221B.",
                    'city'          => "Jakarta",
                    'postal_code'   => "51162",
                    'country_code'  => 'IDN'
                ),
            ),

            "credit_card" => array(
                "secure" => true
            ),

            'expiry' => array(
                'start_time' => date("Y-m-d H:i:s O", $time),
                'unit'       => 'day',
                'duration'   => 1
            ),

            'item_details' => $item_order_detials
        );

        try { //Return snap token if success
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            error_log("Midtrans Snap Token Has Been Sent!");
            return $snapToken;
        } catch (\Throwable $err) {
            return $err;
            Log::info($err);
        }
    }

    public function finish(Request $request)
    {
        // dd($request);
        $orders = $request->input('order_data');
        $orders = json_decode($orders, true);
        $orders = $orders['orders'];
        if(!empty($orders) || $orders != null) {
            error_log("=================");
            error_log("= Orders Found! =");
            error_log("=================");
            Log::info("Orders Found!");
        }

        // TO ARRAY JSON result_data
        $result = $request->input('result_data');
        $result = ((array) json_decode($result));

        // TRY TO GET VIRTUAL NUMBER/Nomer Rek BANK
        try {
            $va_number = ((array) $result['va_numbers'][0]);
        } catch (\Throwable $th) {
            $va_number = [
                'va_number' => '',
                'bank' => ''
            ];
        }

        //Create new Order
        $newOrder = new Order;
        $newOrder->id = $result['order_id'];
        $newOrder->order_from = intval($orders[0]["order_from"]); //DATA STATIS
        $newOrder->customer_id = intval($orders[0]["customer_id"]); //DATA STATIS
        $newOrder->status = $result['transaction_status'];
        

        if (true) {

            //Create New Order Details (expect an array)
            for($i=0; $i < count($orders); $i++) {

                $orderDetails =  OrderDetail::create([
                    'product_id' => $orders[$i]["order_data"]['product_id'],
                    'order_quantity' => $orders[$i]["order_data"]['quantity'],
                    'order_id' => $result['order_id']
                 ]);
        
                //  dd($orderDetails);

                 if($orderDetails) {
                     error_log("Order details created successfully");
                 }
            }
            // foreach ($orders as $key => $value) {
            // }

            // Create New Shipment 
            $shipment = Shipment::create([ //DATA PENGIRIMAN MASIH STATIS
                'order_id' => $result['order_id'],
                'first_name'    => "customer",
                'last_name'     => "1",
                'email'         => 'customer@example.com',
                'phone'         => '08111222333',
                'address'       => "Bakerstreet 221B.",
                'city'          => "Jakarta",
                'postal_code'   => "51162",
                'country_code'  => 'IDN'
            ]);
            if($shipment) {
                error_log("Shipment created successfully");
            }

            //Create New Transaction
            $newTransaction = new Transaction;
            $newTransaction->id = $result['transaction_id'];
            $newTransaction->order_id = $result['order_id'];
            $newTransaction->status_code = $result['status_code'];
            $newTransaction->status_message = $result['status_message'];
            $newTransaction->transaction_time = $result['transaction_time'];
            $newTransaction->transaction_status = $result['transaction_status'];
            $newTransaction->fraud_status = $result['fraud_status'];
            $newTransaction->pdf_url = $result['pdf_url'];
            $newTransaction->save();

            if ($newTransaction) {
                error_log("Transaction created successfully");
                // Create new Payment
                $newTransaction->payment()->create([
                    'transaction_id' => $newTransaction->id,
                    'payment_type' => $result['payment_type'],
                    'va_number' => $va_number['va_number'],
                    'bank' => $va_number['bank'],
                    'gross_amount' => $result['gross_amount']
                ]);
            }
        } $newOrder->save();


        return "order berhasil";
    }

    public function notification(Request $request)
    {

        $notif = new \Midtrans\Notification();

        $transaction = $notif->transaction_status;
        $transaction_id = $notif->transaction_id;
        $status_code = $notif->status_code;
        $fraud = $notif->fraud_status;

        error_log("order $notif->order_id");

        // error_log("Order ID = $notif->order_id: " . "transaction status = $transaction, fraud staus = $fraud" . 
        // " Trans ID = $transaction_id" . " Status code = $status_code" . " Status Message = $notif->status_message")

        if ($transaction == 'settlement') {
            if ($fraud == 'challenge') {
                // TODO Set payment status in merchant's database to 'challenge'
                Transaction::where('id', strval($transaction_id))->update([
                    'status_code' => $status_code,
                    'transaction_status' => $transaction,
                    'status_message' => 'Transaksi berhasil'
                ]);
                
                // Get Product ID and then decrease the quantity of the Product
                $orders = OrderDetail::with('product')->where("order_id", strval($notif->order_id))->get();
                Log::info($orders);

                try {
                    foreach ($orders as $key => $value) {
                        Product::where('id', $value->product_id)->decrement('stock', $value->order_quantity);
                    }
                } catch (\Throwable $th) {
                    throw $th;
                    Log::info('ERROR: decrementing failed');
                }

            } else if ($fraud == 'accept') {
                // TODO Set payment status in merchant's database to 'success'
                Transaction::where('id', strval($transaction_id))->update([
                    'status_code' => $status_code,
                    'transaction_status' => $transaction,
                    'status_message' => 'Transaksi berhasil'
                ]);

                // Get Product ID and then decrease the quantity of the Product
                $orders = OrderDetail::with('product')->where("order_id", strval($notif->order_id))->get();
                Log::info($orders);

                try {
                    foreach ($orders as $key => $value) {
                        Product::where('id', $value->product_id)->decrement('stock', $value->order_quantity);
                    }
                } catch (\Throwable $th) {
                    throw $th;
                    Log::info('ERROR: decrementing failed');
                }


            }
            
        } else if ($transaction == 'cancel') {
            if ($fraud == 'challenge') {
                // TODO Set payment status in merchant's database to 'failure'
                Transaction::where('id', strval($transaction_id))->update([
                    'status_code' => $status_code,
                    'transaction_status' => $transaction,
                    'status_message' => 'Transaksi gagal'
                ]);
            } else if ($fraud == 'accept') {
                // TODO Set payment status in merchant's database to 'failure'
                Transaction::where('id', strval($transaction_id))->update([
                    'status_code' => $status_code,
                    'transaction_status' => $transaction,
                    'status_message' => 'Transaksi gagal'
                ]);
            }
        } else if ($transaction == 'deny') {
            // TODO Set payment status in merchant's database to 'failure'
            Transaction::where('id', strval($transaction_id))->update([
                'status_code' => $status_code,
                'transaction_status' => $transaction,
                'status_message' => 'Transaksi gagal'
            ]);
        } else if ($transaction == 'expire') {
            if ($fraud == 'challenge') {
                // TODO Set payment status in merchant's database to 'failure'
                Transaction::where('id', strval($transaction_id))->update([
                    'status_code' => $status_code,
                    'transaction_status' => $transaction,
                    'status_message' => 'Transaksi gagal'
                ]);
            } else if ($fraud == 'accept') {
                // TODO Set payment status in merchant's database to 'failure'
                Transaction::where('id', strval($transaction_id))->update([
                    'status_code' => $status_code,
                    'transaction_status' => $transaction,
                    'status_message' => 'Transaksi gagal'
                ]);
            }
        }
    }
}
