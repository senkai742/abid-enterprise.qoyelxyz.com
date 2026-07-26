@extends('user.layouts.user')
@section('content-header', 'Profit Details Report')
@section('content')

<a href="{{ route('user.expense.index') }}" class="btn btn-secondary mb-3">&larr; Back</a>

<div class="container-fluid">
    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form method="GET" action="{{ route('user.expense.profit-details') }}" class="form-inline">
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
                <a href="{{ route('user.expense.profit-details') }}" class="btn btn-secondary">Reset</a>
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
        <div class="card bg-success text-white summary-card mr-3" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h5 class="summary-card-title">Gross Profit</h5>
                <div class="summary-card-value">{{ config('settings.currency_symbol') }} {{ number_format($totalProfit, 2) }}</div>
            </div>
        </div>
        <div class="card bg-danger text-white summary-card mr-3" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h5 class="summary-card-title">Sales Returns Loss</h5>
                <div class="summary-card-value">-{{ config('settings.currency_symbol') }} {{ number_format($returnsProfit, 2) }}</div>
            </div>
        </div>
        <div class="card bg-warning text-white summary-card mr-3" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h5 class="summary-card-title">Purchase Returns Loss</h5>
                <div class="summary-card-value">-{{ config('settings.currency_symbol') }} {{ number_format($purchaseReturnsProfit, 2) }}</div>
            </div>
        </div>
        <div class="card bg-dark text-white summary-card mr-3" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h5 class="summary-card-title">Damaged Products Loss</h5>
                <div class="summary-card-value">-{{ config('settings.currency_symbol') }} {{ number_format($damagesTotal, 2) }}</div>
            </div>
        </div>
        <div class="card bg-primary text-white summary-card" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h5 class="summary-card-title">Net Profit</h5>
                <div class="summary-card-value">{{ config('settings.currency_symbol') }} {{ number_format($netProfit, 2) }}</div>
                <small class="text-white-50 d-block mt-2" style="font-size: 0.8em;">Net = Gross - Sales Returns Loss - Purchase Returns Loss - Damaged Products Loss</small>
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

    <!-- Profit Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Profit Details</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total Sales</th>
                            <th>Profit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                            <td>{{ $order->customer ? $order->customer->first_name . ' ' . $order->customer->last_name : 'Walk-in Customer' }}</td>
                            <td>
                                @foreach($order->items as $item)
                                    <div class="small">
                                        {{ $item->product->name ?? 'N/A' }} x {{ $item->quantity }}
                                    </div>
                                @endforeach
                            </td>
                            <td>{{ config('settings.currency_symbol') }} {{ number_format($order->total(), 2) }}</td>
                            <td>
                                <span class="badge badge-{{ $order->profit_amount >= 0 ? 'success' : 'danger' }}">
                                    {{ config('settings.currency_symbol') }} {{ number_format($order->profit_amount, 2) }}
                                </span>
                            </td>
                            <td>
                                @if($order->receivedAmount() >= $order->total())
                                    <span class="badge badge-success">Paid</span>
                                @else
                                    <span class="badge badge-warning">Partial</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No orders found for the selected period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
