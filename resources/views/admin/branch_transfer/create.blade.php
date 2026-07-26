@extends('admin.layouts.admin')

@section('title', 'Transfer Product Between Branches')
@section('content-header', 'Transfer Product')

@section('content')
<div class="row">
    <div class="col-sm-8 offset-sm-2">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.branch-transfer.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="product_id">Product</label>
                        <select name="product_id" id="product_id" class="form-control" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="from_branch_id">From Branch</label>
                        <select name="from_branch_id" id="from_branch_id" class="form-control" required>
                            <option value="">Select Source Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="to_branch_id">To Branch</label>
                        <select name="to_branch_id" id="to_branch_id" class="form-control" required>
                            <option value="">Select Destination Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
                    </div>
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-primary">Transfer</button>
                    <a href="{{ route('admin.branch-transfer.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
