@extends('layouts.adminBase')

@section('content')
<div class="container-fluid">
    {{-- GRID 1 --}}
    <div class="row">
        <div class="col-lg-9">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                        class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
            </div>

            <!-- Content Row -->
            <div class="row">
                <!-- Earnings (Monthly) Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Pendapatan</div>
                                    <div class="mb-0 font-weight-bold text-gray-800">
                                        <div class="income">0</div>
                                    </div>
                                </div>
                                {{-- <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div> --}}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Pesanan Belum Dibayar</div>
                                    <div class="transaction_pending h5 mb-0 font-weight-bold text-gray-800">0</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Pesanan Sukses
                                    </div>
                                    <div class="row no-gutters align-items-center">
                                        <div class="col-auto">
                                            <div
                                                class="transaction_success h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                                0</div>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Failed Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Pesanan Gagal
                                    </div>
                                    <div class="transaction_failed h5 mb-0 font-weight-bold text-gray-800">0</div>
                                </div>
                                <div class="col-auto mt-3">
                                    <i class="fas fa-times fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Content Row -->
        </div>
        <div class="col-lg-3">
            <div class="card shadow mb-4" style="height: 88%">
                <!-- Card Header - Accordion -->
                <a href="#collapseCardExample" class="d-block card-header py-3" data-toggle="collapse" role="button"
                    aria-expanded="true" aria-controls="collapseCardExample">
                    <h6 class="m-0 font-weight-bold text-primary">Deskripsi Toko</h6>
                </a>
                <!-- Card Content - Collapse -->
                <div class="collapse show" id="collapseCardExample">
                    <div class="card-body">
                        @if (!empty(Auth::guard('admin')->user()->shop_description))
                        {{ Auth::guard('admin')->user()->shop_description }}
                        @else
                        Tokomu belum ada deskripsi nih...
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- GRID 2 --}}
    <div class="row">
        <div class="col-xl-9">
            {{-- Grafik pendapatan --}}
            <div class="row">
                <!-- Area Chart -->
                <div class="col-xl-12 col-lg-8">
                    <div class="card shadow mb-4">
                        <!-- Card Header - Dropdown -->
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Pendapatan Tahun 2021</h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                    aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Dropdown Header:</div>
                                    <a class="dropdown-item" href="#">Action</a>
                                    <a class="dropdown-item" href="#">Another action</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#">Something else here</a>
                                </div>
                            </div>
                        </div>
                        <!-- Card Body -->
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="myAreaChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pie chart -->
        <div class="col-lg-3">
            <!-- Deskripsi Toko -->

            <!-- PIE CHART -->
            <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Kinerja Toko</h6>
                    <div class="dropdown no-arrow">
                    </div>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="myPieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> Direct
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Social
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> Referral
                        </span>
                    </div>
                </div>
            </div>
            <!-- END OF PIE CHART -->
        </div>
    </div>

    {{-- GRID 3 --}}
    <div class="row">
        <div class="col-lg-12">
            <!-- DataTales Example -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pembelian Terbaru</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Barang</th>
                                    <th>Order ID</th>
                                    <th>Harga/Kg</th>
                                    <th>Total beli(Kg)</th>
                                    <th>Pembeli</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Barang</th>
                                    <th>Order ID</th>
                                    <th>Harga/Kg</th>
                                    <th>Total beli(Kg)</th>
                                    <th>Pembeli</th>
                                </tr>
                            </tfoot>
                            <tbody class="product-success">

                                {{-- <tr>
                                    <td>1</td>
                                    <td>System Architect</td>
                                    <td>Edinburgh</td>
                                    <td>61</td>
                                    <td>2011/04/25</td>
                                    <td>$320,800</td>
                                </tr> --}}

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after-script')
<script>
    function getDashboardInfo() {
        $.ajax({
            url: "{{ route('get.dashboard.info') }}",
            type: "GET",
            async: false,
            success: function (result) {

                // console.log(result.transactions)

                transactionInfo(result.transactions)

                if(result.products.length > 0) {
                    showProductSuccess(result.products, result.orders_id, result.orders_quantity)
                }

                $(".income").text("Rp. " + result.income)
                setTimeout(() => getDashboardInfo(), 10000)
            },
        })
    }
    getDashboardInfo();

    //Hitung jumlah transaksi
    function transactionInfo(params) {
        var transactionSuccess = 0;
        var transactionPending = 0;
        var transactionFailed = 0;

        params.forEach(transaction => {
            if (transaction.status_code == 200) {
                transactionSuccess++
            } else if (transaction.status_code == 201) {
                transactionPending++
            } else if (transaction.status_code == 407) {
                transactionFailed++
            }
            // console.log(transaction)
        });

        $(".transaction_success").text(transactionSuccess)
        $(".transaction_pending").text(transactionPending)
        $(".transaction_failed").text(transactionFailed)

    }

    function showProductSuccess(products, orderId, orderQuantity) {


        $(".product-success").html('');
        products.forEach((element, index) => {
            $(".product-success").append(`
                <tr>
                    <td>#</td>
                    <td>${element.name}</td>
                    <td>${orderId[index]}</td>
                    <td>Rp. ${rupiah(element.price)}</td>
                    <td>${orderQuantity[index]}</td>
                    <td>iyan@wkwk.co.id</td>
                </tr>`)
        });
    }

    function rupiah(number) {
        var bilangan = number;

        var number_string = bilangan.toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{1,3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;

        return rupiah;
    }

</script>
@endpush
