@extends('admin.layouts.admin')

@section('title', __('Pay Supplier'))
@section('content-header', __('Pay Supplier'))
@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.suppliers.pay.submit', $supplier) }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="amount">{{ __('Amount') }}</label>
                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" id="amount" min="1" required>
                @error('amount')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
            <button class="btn btn-success" type="submit">{{ __('Pay') }}</button>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
