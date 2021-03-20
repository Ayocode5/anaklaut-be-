<html>
<title>Checkout</title>

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-OPf5-6qjuxV6DrHf">
    </script>

</head>

<body>
    <form id="payment-form" method="post" action="{{ route('api.payment.finish') }}">
        @csrf
        <input type="hidden" name="result_type" id="result-type" value=""></div>
        <input type="hidden" name="result_data" id="result-data" value=""></div>
        <input type="hidden" name="order_data" id="order_data" value=""></div>

        <label for="barang">Id barang</label>
        <input type="number" name="product_id" id="product_id">
        <label for="barang">Quantity barang</label>
        <input type="number" name="quantity" id="quantity">
    </form>

    <button id="pay-button">Pay!</button>

    <script src="https://code.jquery.com/jquery-3.5.1.js"
        integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
    <script type="text/javascript">
        $('#pay-button').click(function (event) {
            event.preventDefault();
            // $(this).attr("disabled", "disabled");

            var product_id = $('#product_id').val();
            var quantity = $('#quantity').val();
            // console.log(idBarang);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: "{{ route('api.payment.token') }}",
                type: 'POST',
                data: {
                    "orders": [ //ORDER DIKELOMPOKKAN BERDASARKAN ADMIN ID
                        { // ORDER DARI ADMIN 1
                            "order_from": 1,
                            "customer_id": 1,
                            "order_data": [{ // DETAIL PRODUK dari ADMIN ID 1
                                    "product_id": 3,
                                    "quantity": 1,
                                },
                                { // DETAIL PRODUK 2 dari ADMIN ID 1
                                    "product_id": 1,
                                    "quantity": 5,
                                }
                            ]
                        },
                        // { // ORDER DARI ADMIN 2
                        //     "order_from": 2,
                        //     "customer_id": 1,
                        //     "order_data": {
                        //         {
                        //             "product_id": 3,
                        //             "quantity": 1,
                        //         },
                        //         {
                        //             "product_id": 1,
                        //             "quantity": 5,
                        //         }
                        //     }
                        // }

                    ]
                },
                cache: false,
                success: function (data) {

                    //location = data;
                    console.log('token = ' + data["token"]);

                    var resultType = document.getElementById('result-type');
                    var resultData = document.getElementById('result-data');

                    function changeResult(type, data) {
                        $("#result-type").val(type);
                        $("#result-data").val(JSON.stringify(data));
                        $("#order_data").val(JSON.stringify({
                            "orders": [ //ORDER DIKELOMPOKKAN BERDASARKAN ADMIN ID
                                { // ORDER DARI ADMIN 1
                                   "order_from": 1,
                                    "customer_id": 1,
                                    "order_data": [{ // DETAIL PRODUK dari ADMIN ID 1
                                            "product_id": 3,
                                            "quantity": 1,
                                        }, 
                                        { // DETAIL PRODUK 2 dari ADMIN ID 1
                                            "product_id": 1,
                                            "quantity": 5,
                                        }
                                    ]
                                },
                                // { // ORDER DARI ADMIN 2
                                //     "order_from": 2,
                                //     "customer_id": 1,
                                //     "order_data": {
                                //         {
                                //             "product_id": 3,
                                //             "quantity": 1,
                                //         },
                                //         {
                                //             "product_id": 1,
                                //             "quantity": 5,
                                //         }
                                //     }
                                // }

                            ]
                        }, ))

                        //resultType.innerHTML = type;
                        //resultData.innerHTML = JSON.stringify(data);
                    }

                    snap.pay(data["token"], {

                        onSuccess: function (result) {
                            changeResult('success', result);
                            console.log(result.status_message);
                            console.log(result);
                            $("#payment-form").submit();
                        },
                        
                        onPending: function (result) {
                            changeResult('pending', result);
                            console.log(result.status_message);
                            $("#payment-form").submit();
                        },
                        onError: function (result) {
                            changeResult('error', result);
                            console.log(result.status_message);
                            $("#payment-form").submit();
                        }
                    });
                }
            });
        });

    </script>


</body>

</html>
