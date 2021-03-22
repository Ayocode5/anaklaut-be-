@extends('layouts.adminBase')

@section('content')
<div class="container-fluid">
    <div class="mt-4 ml-4 mr-4 mb-4">
    
        <div class="card">
            <div class="card-header font-weight-bold text-primary">
                Tambah Produk
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('product.new') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="formGroupExampleInput">Nama Produk</label>
                        <input value="{{ old('name') }}" type="text" class="@error('name') is-invalid @enderror form-control form-control-user" id="name" name="name" placeholder="Ikan Tuna">
                        @error('name')
                        <div class="invalid-feedback">
                            Tentukan nama produk!   
                        </div>
                        @enderror
                    </div>
                    

                    <div class="form-group">
                        <label for="formGroupExampleInput2">Tipe Produk</label>
                        <select id="type" name="type" class="@error('type') is-invalid @enderror custom-select custom-select-md mb-3">
                            @foreach ($productTypes as $productType)
                                <option value="{{$productType}}">{{$productType}}</option>
                            @endforeach
                          </select>
                    </div>
                    <div class="form-group">
                        <label for="formGroupExampleInput2">Berat (KG)</label>
                        <input id="weight" name="weight" type="number" class="@error('weight') is-invalid @enderror form-control" value="{{ old('weight') }}" placeholder="10">
                        @error('weight')
                        <div class="invalid-feedback">
                            Tentukan berat barang!   
                        </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="formGroupExampleInput2">Deskripsi Produk</label>
                        <input value="{{ old('description') }}" id="description" name="description" type="text" class="@error('description') is-invalid @enderror form-control" placeholder="Ikan tuna x adalah ....">
                        @error('description')
                        <div class="invalid-feedback">
                            Belum memberikan deskripsi!   
                        </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="formGroupExampleInput2">Harga/KG</label>
                        <input value="{{ old('price') }}" id="price" name="price" type="number" class="@error('price') is-invalid @enderror form-control" placeholder="5000">
                        @error('price')
                        <div class="invalid-feedback">
                            Tentukan harga barangmu!   
                        </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="formGroupExampleInput2">Harga Grosir</label>
                        <input value="{{ old('grosir_price') }}" id="grosir_price" name="grosir_price" type="number" class="@error('grosir_price') is-invalid @enderror form-control" placeholder="4700">
                        @error('grosir_price')
                        <div class="invalid-feedback">
                            Tentukan harga grosir!   
                        </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="formGroupExampleInput2">Minimal Grosir</label>
                        <input value="{{ old('grosir_min') }}" id="grosir_min" name="grosir_min" type="number" class="@error('grosir_min') is-invalid @enderror form-control" placeholder="12">
                        @error('grosir_min')
                        <div class="invalid-feedback">
                            Tentukan minimal grosir!   
                        </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="formGroupExampleInput2">Stok</label>
                        <input value="{{ old('stock') }}" id="stock" name="stock" type="number" class="@error('stock') is-invalid @enderror form-control" placeholder="20">
                        @error('stock')
                        <div class="invalid-feedback">
                            Tentukan Stok!   
                        </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="photo">Gambar Produk</label>
                        <input value="{{ old('photo') }}" type="file" name="photo" accept="image/*" class="@error('photo') is-invalid @enderror form-control-file">
                        @error('photo')
                        <div class="invalid-feedback">
                            Pilih foto untuk barangmu!   
                        </div>
                        @enderror
                    </div>
                    <button class="btn btn-primary" type="submit">Tambah Produk</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
