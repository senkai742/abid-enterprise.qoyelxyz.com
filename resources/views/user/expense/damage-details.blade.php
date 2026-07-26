@extends('user.layouts.user')
@section('content-header', 'Damaged Products Details')
@section('content')
<a href="{{ route('user.expense.index') }}" class="btn btn-secondary mb-3">&larr; Back to Report Summary</a>

<div class="container-fluid">
    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form method="GET" action="{{ route('user.expense.damage-details') }}" class="form-inline">
                <div class="form-group mr-3">
                    <label for="start_date" class="mr-2">From:</label>
                    <input type="date" class="form-control" id="start_date" name="start_date"
                           value="{{ request('start_date', \Carbon\Carbon::parse($startDate)->format('Y-m-d')) }}">
                </div>
                <div class="form-group mr-3">
                    <label for="end_date" class="mr-2">To:</label>
                    <input type="date" class="form-control" id="end_date" name="end_date"
                           value="{{ request('end_date', \Carbon\Carbon::parse($endDate)->format('Y-m-d')) }}">
                </div>
                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                <a href="{{ route('user.expense.damage-details') }}" class="btn btn-secondary">Reset</a>
            </form>
        </div>
        <div class="col-md-4 text-right">
            <div class="text-muted">
                <small>Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</small>
            </div>
        </div>
    </div>

    <!-- Damaged Products Table -->
    <div class="card">
        <div class="card-header font-weight-bold">Damaged Products</div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Purchase Price</th>
                        <th>Total Loss</th>
                        <th>Notes</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($damages as $damage)
                        <tr>
                            <td>{{ $damage->id }}</td>
                            <td>{{ $damage->product->name ?? 'N/A' }}</td>
                            <td>{{ number_format($damage->qnty, 0) }}</td>
                            <td>{{ config('settings.currency_symbol') }} {{ number_format($damage->purchase_price, 2) }}</td>
                            <td>{{ config('settings.currency_symbol') }} {{ number_format($damage->purchase_price * $damage->qnty, 2) }}</td>
                            <td>{{ $damage->notes }}</td>
                            <td>{{ $damage->created_at }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No damaged products found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-center">
            {{ $damages->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
