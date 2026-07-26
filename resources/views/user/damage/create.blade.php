@extends('user.layouts.user')

@section('title', __('Add Damage'))
@section('content-header', __('Add Damage'))

@section('content')
<div class="row">
    <div class="col-sm-2"></div>
    <div class="col-sm-8">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('user.damages.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="select-product">{{ 'Product'}}</label>
                        <select onchange="selectProduct(this)" class="form-control" name="select-product" id="select-product" required>
                            <option>:: Select product for damage ::</option>
                            @foreach ($products as $product )
                                <option value='{{$product->id."=".$product->name."=".$product->purchase_price."=".$product->sell_price."=".$product->quantity}}'>{{$product->name}}</option>
                            @endforeach

                        </select>
                        <input type="hidden" name="product_id" readonly class="form-control @error('product_id') is-invalid @enderror" id="product_id"
                            placeholder="Product ID" required value="{{ old('product_id') }}">

                        @error('product_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>


                    <div class="form-group">
                        <label for="purchase_price">{{ 'Purchase Price' }}</label>
                        <input type="text" name="purchase_price" readonly class="form-control @error('purchase_price') is-invalid @enderror" id="purchase_price"
                            placeholder="Purchase Price" value="{{ old('purchase_price') }}">
                        @error('purchase_price')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sell_price">Sell Price</label>
                        <div class="custom-file">
                            <input type="text" name="sell_price" readonly class="form-control @error('sell_price') is-invalid @enderror" id="sell_price"
                            placeholder="Sell Price" value="{{ old('sell_price') }}">
                        </div>
                        @error('sell_price')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="stock_qnty">Stock Qnty</label>
                        <input type="text" name="stock_qnty" readonly class="form-control @error('stock_qnty') is-invalid @enderror"
                            id="stock_qnty" placeholder="Stock Qnty" value="{{ old('stock_qnty') }}">
                        @error('stock_qnty')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="damage_qnty">Damage Qnty</label>
                        <input type="text" name="damage_qnty" class="form-control @error('damage_qnty') is-invalid @enderror"
                            id="damage_qnty" placeholder="Damage Qnty" value="{{ old('damage_qnty', '') }}" required>
                        @error('damage_qnty')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="damage_notes">Damage Notes</label>
                        <textarea name="damage_notes" class="form-control @error('damage_notes') is-invalid @enderror"
                            id="damage_notes" placeholder="Damage Notes">{{ old('damage_notes') }}</textarea>
                        @error('damage_notes')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="text-right">
                        <button class="btn btn-primary" type="submit">{{ 'Add Damage' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script>

     function selectProduct($this){
           const $product = $this.value.split('=');
           document.getElementById('product_id').value = $product[0]
           document.getElementById('purchase_price').value = $product[2]
           document.getElementById('sell_price').value = $product[3]
           document.getElementById('stock_qnty').value = $product[4]

         }

    $(document).ready(function () {

    });
</script>
@endsection
