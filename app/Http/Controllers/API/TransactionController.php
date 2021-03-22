<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Models\Product;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Shipment;
use App\Models\Transaction;
use App\Models\User;


interface PaymentGateway
{
    public function request_token($request);
    public function checkout_finish(Request $request);
    public function approve_transaction();
    public function abort_transaction();
    public function refund_transaction();
    public function notification_transaction(Request $request);
}

class MidtransPayment implements PaymentGateway
{
    const EXPIRY_ORDER = [
        "unit" => "day",
        "duration" => 1
    ];

    const ENABLED_PAYMENT = array(
        "bca_va",
        // "gopay",
        // "indomaret"
    );

    public function request_token($request)
    {

        $orders = $request->input('orders');

        try { // MENYIAPKAN DETAIL PRODUK SESUAI INPUT PEMESAN
            error_log("*** Menyiapkan detail produk order");
            Log::debug("*** Menyiapkan detail produk order");

            // START QUERYING PRODUCTS
            foreach ($orders[0]["order_data"] as $key => $value) {
                $product["items"][] = Product::find($value["product_id"]);  // GET PRODUCT FROM DATABASE BASED ON INPUT JSON
                $product["quantity"][] = $value["quantity"]; // SET PRODUCT QUANTITY ORDERS

                Log::info("*** Query produk id " . $value["product_id"] . " berhasil");
                error_log("*** Query produk id " . $value["product_id"] . " berhasil");
            }
        } catch (\Throwable $th) {
            Log::error($th);
            error_log("*** Query produk gagal");
        }

        //MENGAMBIL DETAIL ADMIN
        $admin = Admin::findOrFail($orders[0]["order_from"]);
        $customer = User::findOrFail($orders[0]["customer_id"]);

        error_log("*** Menyiapkan midtrans standard array order details");
        foreach ($product["items"] as $key => $value) {
            // MENYIAPKAN ARRAY ORDER DETAIL UNTUK KEMUDIAN DI PROSES OLEH MIDTRANS
            $item_order_detials[] = array(
                'id' => $value->id,
                'price' => $value->price,
                'quantity' => $product['quantity'][$key],
                'name' => $value->name
            );
        } // END OF MENYIAPKAN ARRAY ORDER DETAIL UNTUK KEMUDIAN DI PROSES OLEH MIDTRANS

        $time = time(); //GET CURRENT TIME
        $params = array(
            'transaction_details' => array(
                'order_id' => uniqid("IYN"),
                'gross_amount' => 1,
            ),

            "enabled_payments" => self::ENABLED_PAYMENT,

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

            'customer_details' => array( //DATA CUSTOMER MASIH STATIS
                'first_name' => $customer->name,
                // 'last_name' => '1',
                'email' => $customer->email,
                'phone' => '08111222333',
                'shipping_address' => array(
                    'first_name'    => $customer->name,
                    // 'last_name'     => "1",
                    'email'         => $customer->email,
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
                'unit'       => self::EXPIRY_ORDER["unit"],
                'duration'   => self::EXPIRY_ORDER["duration"]
            ),

            'item_details' => $item_order_detials
        );

        try { //Return snap token to snap.js if success

            $snapToken = \Midtrans\Snap::getSnapToken($params);
            error_log("*** Midtrans snap token has been sent to snap.js!");
            return $snapToken;
        } catch (\Throwable $err) {
            return $err;
            error_log("*** Getting Midtrans SNAP TOKEN Failed ***");
            Log::info($err);
        }
    }

    public function checkout_finish($request)
    {
        $orders = $request->input('order_data');

        // $orders = json_decode($orders, true); << WARNING: Enable This Line If Json Data Stringified
        $orders = $orders['orders'];

        // GET TRANSACTIONS INFO FROM MIDTRANS
        $result = $request->input('result_data');
        // $result = ((array) json_decode($result)); << WARNING: Enable This Line If Json Data Stringified

        // CHECK REQUIRED DATA
        if (!empty($orders) || $orders != null || !empty($result) || $result != null) {
            error_log("*** Orders Found!");
            Log::info("*** Orders Found!");
        } else {
            error_log("*** Required data is not exists");
            return response()->json([
                "message" => "Required data is not exists",
                "status" => 400
            ]);
        }

        // TRY TO GET VIRTUAL NUMBER/Nomer Rek BANK
        try {
            $va_number = ((array) $result['va_numbers'][0]);
        } catch (\Throwable $th) {
            $va_number = [
                'va_number' => '',
                'bank' => ''
            ];
        }

        //DO CREATE NEW ORDER
        try {
            //code...
            DB::transaction(function () use ($result, $orders, $va_number) {

                $newOrder = new Order;
                $newOrder->id = $result['order_id'];
                $newOrder->order_from = intval($orders[0]["order_from"]); //DATA STATIS
                $newOrder->customer_id = intval($orders[0]["customer_id"]); //DATA STATIS
                $newOrder->status = $result['transaction_status'];
                $newOrder->save();

                if ($newOrder) {

                    //Create New Order Details (expect an array)
                    foreach ($orders[0]["order_data"] as $key => $value) {
                        
                        $orderDetails = OrderDetail::create([
                            'order_id' => $result['order_id'],
                            'product_id' => $value["product_id"],
                            'order_quantity' => $value["quantity"]
                        ]);

                        if ($orderDetails) {
                            Log::info("*** Order details " . $key . " created successfully");
                            error_log("*** Order details " . $key . " created successfully");
                        }
                    }

                    // GET USER INFORMATION & USE IT IN SHIPMENT DATA
                    $customer = DB::table('users')->where('id',  $orders[0]['customer_id'])->first();

                    // Create New Shipment 
                    $shipment = Shipment::create([ //DATA PENGIRIMAN MASIH STATIS
                        'order_id' => $result['order_id'],
                        'first_name'    => $customer->name,
                        // 'last_name'     => "1",
                        'email'         => $customer->email,
                        'phone'         => '08111222333',
                        'address'       => "Bakerstreet 221B.",
                        'city'          => "Jakarta",
                        'postal_code'   => "51162",
                        'country_code'  => 'IDN'
                    ]);
                    if ($shipment) {
                        error_log("*** Shipment created successfully");
                        Log::info("*** Shipment created successfully");
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
                        error_log("*** Transaction created successfully");
                        // Create new Payment
                        $newTransaction->payment()->create([
                            'transaction_id' => $newTransaction->id,
                            'payment_type' => $result['payment_type'],
                            'va_number' => $va_number['va_number'],
                            'bank' => $va_number['bank'],
                            'gross_amount' => $result['gross_amount']
                        ]);
                    }
                }
            });

            return  response()->json([
                "message" => "Menyimpan detail order baru success",
                "status" => 200
            ]);
        
        } catch (\Throwable $th) {
            
            Log::error($th);
            return  response()->json([
              "message" => "Menyimpan detail order baru gagal",
              "status" => 401
            ]);
        }
    }

    public function notification_transaction($request)
    {
        $notif = new \Midtrans\Notification();

        $transaction = $notif->transaction_status;
        $transaction_id = $notif->transaction_id;
        $status_code = $notif->status_code;
        $fraud = $notif->fraud_status;

        error_log("*** Notification from Order ID >> $notif->order_id ****");

        error_log("Order ID = $notif->order_id: " . "transaction status = $transaction, fraud staus = $fraud" .
            " Trans ID = $transaction_id" . " Status code = $status_code" . " Status Message = $notif->status_message");

        // TRANSAKSI SUKSES
        if ($transaction == 'settlement') {
            if ($fraud == 'challenge') {
                // TODO Set payment status in merchant's database to 'challenge'
                Transaction::where('id', strval($transaction_id))->update([
                    'status_code' => $status_code,
                    'transaction_status' => $transaction,
                    'status_message' => 'Transaksi berhasil'
                ]);

                return response()->json(
                    [
                        'status_code' => $status_code,
                        'transaction_status' => $transaction,
                        'status_message' => 'Transaksi berhasil'
                    ],
                    200
                );

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

                return response()->json(
                    [
                        'status_code' => $status_code,
                        'transaction_status' => $transaction,
                        'status_message' => 'Transaksi berhasil'
                    ],
                    200
                );

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
        } else if ($transaction == 'cancel') { // TRANSAKSI CANCEL
            if ($fraud == 'challenge') {
                // TODO Set payment status in merchant's database to 'failure'
                Transaction::where('id', strval($transaction_id))->update([
                    'status_code' => $status_code,
                    'transaction_status' => $transaction,
                    'status_message' => 'Transaksi gagal'
                ]);
                return response()->json(
                    [
                        'status_code' => $status_code,
                        'transaction_status' => $transaction,
                        'status_message' => 'gagal'
                    ],
                    $status_code
                );
            } else if ($fraud == 'accept') {
                // TODO Set payment status in merchant's database to 'failure'
                Transaction::where('id', strval($transaction_id))->update([
                    'status_code' => $status_code,
                    'transaction_status' => $transaction,
                    'status_message' => 'Transaksi gagal'
                ]);
                return response()->json(
                    [
                        'status_code' => $status_code,
                        'transaction_status' => $transaction,
                        'status_message' => 'Transaksi gagal'
                    ],
                    $status_code
                );
            }
        } else if ($transaction == 'deny') { // TRANSAKSI DESY
            // TODO Set payment status in merchant's database to 'failure'
            Transaction::where('id', strval($transaction_id))->update([
                'status_code' => $status_code,
                'transaction_status' => $transaction,
                'status_message' => 'Transaksi gagal'
            ]);
            return response()->json(
                [
                    'status_code' => $status_code,
                    'transaction_status' => $transaction,
                    'status_message' => 'Transaksi gagal'
                ],
                $status_code
            );
        } else if ($transaction == 'expire') { // TRANSAKSI EXPIRE
            if ($fraud == 'challenge') {
                // TODO Set payment status in merchant's database to 'failure'
                Transaction::where('id', strval($transaction_id))->update([
                    'status_code' => $status_code,
                    'transaction_status' => $transaction,
                    'status_message' => 'Transaksi expired'
                ]);
                return response()->json(
                    [
                        'status_code' => $status_code,
                        'transaction_status' => $transaction,
                        'status_message' => 'Transaksi expired'
                    ],
                    $status_code
                );
            } else if ($fraud == 'accept') {
                // TODO Set payment status in merchant's database to 'failure'
                Transaction::where('id', strval($transaction_id))->update([
                    'status_code' => $status_code,
                    'transaction_status' => $transaction,
                    'status_message' => 'Transaksi expired'
                ]);
                return response()->json(
                    [
                        'status_code' => $status_code,
                        'transaction_status' => $transaction,
                        'status_message' => 'Transaksi expired'
                    ],
                    $status_code
                );
            }
        }
    }

    public function approve_transaction()
    {
    }
    public function abort_transaction()
    {
    }
    public function refund_transaction()
    {
    }
}


class TransactionController extends Controller
{

    public function __construct()
    {
        $this->_midtrans_init();
    }

    public function token(Request $request)
    {
        error_log(sprintf($this->colorFormat['green'], "INFO: Starting Request Midtrans SNAP TOKEN from snap.js"));
        try {
            //code...
            $midtrans = new MidtransPayment;
            $midtransToken = $midtrans->request_token($request);

            Log::info("Midtrans SNAP TOKEN >> " . $midtransToken);

            return response()->json(
                ["token" => $midtransToken],
                200
            );

        } catch (\Throwable $th) {

            error_log(sprintf($this->colorFormat['red'], "ERROR: Getting Midtrans SNAP TOKEN FAILED"));
            Log::info($th);

            return ["token" => 'invalid-token'];
        }
    }

    public function finish(Request $request)
    {   

        // Log::debug($request); die;
        try {
            //code...
            error_log(sprintf($this->colorFormat['green'], "INFO: Saving order into database"));
            $midtrans = new MidtransPayment;
            $checkoutFinish = $midtrans->checkout_finish($request);

            return $checkoutFinish;

        } catch (\Throwable $th) {
            error_log(sprintf($this->colorFormat['red'], "ERROR: Failed saving order into database"));
            Log::error($th);

            return "Failed saving order into database";
        }
    }

    public function notification(Request $request)
    {
        try {

            error_log(sprintf($this->colorFormat['green'], "INFO: New Notification"));
            $midtrans = new MidtransPayment;
            $notif = $midtrans->notification_transaction($request);

            error_log($notif);
            return;

        } catch (\Throwable $th) {

            error_log(sprintf($this->colorFormat['red'], "INFO: Failed Listening new Notification"));
            Log::error($th);
        }
    }
    
    public function status($orderId) {
        $status =  \Midtrans\Transaction::status($orderId);
        if(!empty($status)) {
            return ResponseFormatter::success($status, 200, "Success, transaction is found");
        }
        return ResponseFormatter::error($status);
        // return ResponseFormatter::success($status);
    }
}
