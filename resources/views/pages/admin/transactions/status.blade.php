@extends('layouts.adminBase')

@section('content')
{{-- {{ dd($status) }} --}}
<div class="container-fluid">

    <div class="card shadow">
        <h5 class="card-header">Detail Transaksi</h5>
        <div class="card-body">
            <div class="card border-left-primary">
                <div class="card-body">
                    <h5 class="card-title">ID Transaksi: {{ $status->transaction_id }}</h5>
                    <h5 class="card-title">Order Id: {{ $status->order_id }}</h5>
                </div>
            </div>
            <div class="card border-left-info mt-2 mb-2">
                <div class="card-body">
                    <p class="card-text">Waktu Pesan: {{ $status->transaction_time }}</p>
                    <p class="card-text">Pesan: {{ $status->status_message }}</p>
                    <p class="card-text">Kurensi: {{ $status->currency }} {{ $status->gross_amount }}</p>
                    <p class="card-text">Rekening: {{ Str::upper($status->va_numbers[0]->bank) }}
                        {{ $status->va_numbers[0]->va_number }}</p>
                </div>
            </div>

            <a class="btn btn-info">{{ $status->transaction_status }}</a>
            <a href="" class="btn btn-primary">Back</a>
        </div>
    </div>
</div>
@endsection
