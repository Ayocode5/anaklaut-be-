@extends('layouts.adminBase')

@section('content')

@if ($products->count() > 0)
@foreach ($products as $product)
<ul>
    <div class="container-fluid">
        <li style="margin-left: -20px;">
            @foreach ($product->product_galleries as $gallery)
            <div class="card border-left-primary mb-3 shadow" style="width: 100%;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-2 col-sm-5 col-md-3">
                            <div class="card">
                                <div class="card-body px-0 py-0">
                                    {{-- <div class="col-2"> --}}
                                    <img class="border border-primary rounded" style="height: 154px; width: 100%;" class=""
                                        src="{{ $gallery->image }}" alt="Card image cap">
                                    {{-- </div> --}}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-10 col-sm-5 col-md-9">
                            <div class="card">
                                <div class="card-body">
                                    {{-- <div class="col-10"> --}}
                                    <h5 class="card-title">{{ $product->name }}</h5>
                                    <p class="card-text">Stok barang: {{ $product->stock }}</p>
                                    <a class="btn btn-circle btn-primary"
                                        href="{{ route('product.edit', Crypt::encrypt($product->id)) }}"><i
                                            class="fas fa-pen"></i></a>

                                    <button type="button" class="btn btn-circle btn-info" data-toggle="modal"
                                        data-target="#exampleModalCenter"
                                        data-remote="{{ route('product.detail', $product->id) }}">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                    <a class="btn btn-circle btn-danger"
                                        href="{{ route('product.destroy', $product->id) }}"><i
                                            class="fas fa-trash"></i></a>
                                    {{-- </div> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </li>
    </div>
</ul>
@endforeach
@else
<p>Gak ada produk nih</p>
@endif
<!-- Large modal -->

<!-- Modal for Product Detail -->
<div class="modal fade modalProductDetail" id="exampleModalCenter" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('after-script')
<script>
    $('.modalProductDetail').on('show.bs.modal', function (event) {

        var button = $(event.relatedTarget) // Button that triggered the modal
        var link = button.data('remote') // Extract info from data-* attributes

        var modal = $(this)
        modal.find('.modal-title').text('Details Product')

        $.ajax({
            type: 'get',
            url: link,
            success: (response) => {
                modal.find('.modal-body').html(response)
            }
        })

    })

</script>
@endpush

@endsection
