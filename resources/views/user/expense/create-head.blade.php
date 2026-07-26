@extends('user.layouts.user')

@section('title', __('Add Expense Head'))
@section('content-header', __('Add Expense Head'))
@section('content-actions')
<a href="{{route('user.expense.create')}}" class="btn btn-primary"><i class="fa fa-chevron-left"></i> Add Expenses</a>
@endsection
@section('content')
<div class="row">
    <div class="col-sm-2"></div>
    <div class="col-sm-8">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('user.expense.head.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf


                    <div class="form-group">
                        <label for="damage_notes">Expense Head</label>
                        <textarea name="expense_head" class="form-control @error('expense_head') is-invalid @enderror"
                            id="expense_head" placeholder="House Rent" required>{{ old('expense_head') }}</textarea>
                        @error('expense_head')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="text-right">
                        <button class="btn btn-primary" type="submit">{{ 'Save Expense Head' }}</button>
                    </div>
                </form>
            </div>
        </div>


         <table class="table">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Expense Head' }}</th>
                    <th>{{ 'Created' }}</th>
                    <th>{{ 'Actions' }}</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($expense_heads as $expense)
                <tr>
                    <td> {{$expense->id}}</td>
                    <td>{{$expense->expense_head}}</td>

                    <td>{{$expense->created_at}}</td>
                    <td class="text-center"><form action="/admin/expense-head/{{$expense->id}}" method="POST" onsubmit="return confirm('Are you sure you want to delete this?');"> @method('DELETE') @csrf <button type="submit"><i class="fa fa-trash"></i></button></form></td>

                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>

                    <th></th>
                    <th></th>

                </tr>
            </tfoot>
        </table>

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
