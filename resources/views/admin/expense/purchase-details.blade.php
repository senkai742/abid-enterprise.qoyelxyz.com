@extends('admin.layouts.admin')
@section('content-header', 'Purchase Details Report')
@section('content')

<a href="{{ route('admin.expense.index') }}" class="btn btn-secondary mb-3">&larr; Back</a>

<div class="container-fluid">
    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form method="GET" action="{{ route('admin.expense.purchase-details') }}" class="form-inline">
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
                <a href="{{ route('admin.expense.purchase-details') }}" class="btn btn-secondary">Reset</a>
            </form>
        </div>
        <div class="col-md-4 text-right">
            <div class="text-muted">
                <small>Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</small>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="d-flex flex-wrap gap-3 mb-4">
        <div class="card bg-info text-white summary-card" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h5 class="summary-card-title">Total Purchase</h5>
                <div class="summary-card-value">{{ config('settings.currency_symbol') }} {{ number_format($totalPurchase, 2) }}</div>
            </div>
        </div>
    </div>
    <style>
        .summary-card-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .summary-card-value {
            font-size: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        .card-body.text-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
    </style>

    <!-- Purchase Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Purchase Details</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Purchase ID</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Invoice No</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>#{{ $purchase->id }}</td>
                            <td>{{ $purchase->created_at->format('M d, Y H:i') }}</td>
                            <td>{{ $purchase->supplier ? $purchase->supplier->first_name . ' ' . $purchase->supplier->last_name : 'N/A' }}</td>
                            <td>{{ $purchase->invoice_no ?? 'N/A' }}</td>
                            <td>
                                @foreach($purchase->items as $item)
                                    <div class="small">
                                        {{ $item->product->name }} x {{ $item->quantity }} = {{ config('settings.currency_symbol') }} {{ number_format($item->purchase_price * $item->quantity, 2) }}
                                    </div>
                                @endforeach
                            </td>
                            <td>{{ config('settings.currency_symbol') }} {{ number_format($purchase->gr_total, 2) }}</td>
                            <td>{{ config('settings.currency_symbol') }} {{ number_format($purchase->paid_amount, 2) }}</td>
                            <td>
                                @if($purchase->paid_amount >= $purchase->gr_total)
                                    <span class="badge badge-success">Paid</span>
                                @else
                                    <span class="badge badge-warning">Partial</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No purchases found for the selected period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $purchases->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
