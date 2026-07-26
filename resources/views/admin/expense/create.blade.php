@extends('admin.layouts.admin')

@section('title', __('Add Expense'))
@section('content-header', __('Add Expense'))

@section('content-actions')
<a href="{{route('admin.expense.index')}}" class="btn btn-primary"><i class="fa fa-chevron-left"></i> Expenses</a>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-2"></div>
    <div class="col-sm-8">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('admin.expense.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-sm-9">
                            <div class="form-group">
                                <label for="expense_head">{{ 'Product'}}</label>
                                <select class="form-control" name="expense_head" id="expense_head" required>
                                    <option>:: Select expense head ::</option>
                                    @foreach ($expense_heads as $exp )
                                        <option value='{{$exp->expense_head}}'>{{$exp->expense_head}}</option>
                                    @endforeach

                                </select>


                                @error('expense_head')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <label for="damage_notes">Expense Head</label><br>

                            <a href="{{route('admin.expense.head.create')}}" role="button" class="btn btn-success">+ Expense Item</a>
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="damage_notes">Expense Description</label>
                        <textarea name="expense_description" class="form-control @error('expense_description') is-invalid @enderror"
                            id="expense_description" placeholder="Expense Description" required>{{ old('expense_description') }}</textarea>
                        @error('expense_description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>






                    <div class="form-group">
                        <label for="expense_amount">Expense Amount</label>
                        <input type="text" name="expense_amount" class="form-control @error('expense_amount') is-invalid @enderror"
                            id="expense_amount" placeholder="Expense Amount" value="{{ old('expense_amount', '') }}" required>
                        @error('expense_amount')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="text-right">
                        <button class="btn btn-primary" type="submit">{{ 'Save Expense' }}</button>
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
