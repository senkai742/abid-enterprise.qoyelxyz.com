@extends('admin.layouts.admin')

@section('title', __('Add Damage'))
@section('content-header', __('Add Damage'))

@section('content')
<div class="row">
    <div class="col-sm-2"></div>
    <div class="col-sm-8">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('admin.damages.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="branch_id">{{ 'Branch' }}</label>
                        <select class="form-control" name="branch_id" id="branch_id" onchange="updateStock()" required>
                            <option value="">:: Select branch ::</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" {{ auth()->user()->branch_id == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="select-product">{{ 'Product'}}</label>
                        <select onchange="selectProduct(this)" class="form-control" name="select-product" id="select-product" required>
                            <option value="">:: Select product for damage ::</option>
                            @foreach ($products as $product )
                                @php
                                    $branchStocks = $product->branchStocks->pluck('quantity', 'branch_id')->toJson();
                                @endphp
                                <option value='{{$product->id}}' data-purchase-price="{{ $product->purchase_price ?? 0 }}" data-sell-price="{{ $product->sell_price ?? 0 }}" data-stocks="{{ $branchStocks }}">{{$product->name}}</option>
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
           const selectedOption = $this.options[$this.selectedIndex];
           if($this.value) {
               document.getElementById('product_id').value = $this.value;
               document.getElementById('purchase_price').value = selectedOption.dataset.purchasePrice || 0;
               document.getElementById('sell_price').value = selectedOption.dataset.sellPrice || 0;
           } else {
               document.getElementById('product_id').value = '';
               document.getElementById('purchase_price').value = '';
               document.getElementById('sell_price').value = '';
           }
           updateStock();
     }

     function updateStock() {
         const productSelect = document.getElementById('select-product');
         const branchSelect = document.getElementById('branch_id');
         const stockQntyInput = document.getElementById('stock_qnty');

         if (productSelect.selectedIndex > 0 && branchSelect.value) {
             const selectedOption = productSelect.options[productSelect.selectedIndex];
             const stocks = JSON.parse(selectedOption.dataset.stocks || '{}');
             const branchId = branchSelect.value;
             stockQntyInput.value = stocks[branchId] || 0;
         } else {
             stockQntyInput.value = 0;
         }
     }

    $(document).ready(function () {

    });
</script>
@endsection
