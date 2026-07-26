@extends('admin.layouts.admin')
@section('content-header', 'Cash Flow Details Report')
@section('content')

<a href="{{ route('admin.expense.index') }}" class="btn btn-secondary mb-3">&larr; Back</a>

<div class="container-fluid">
    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form method="GET" action="{{ route('admin.expense.cash-details') }}" class="form-inline">
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
                <a href="{{ route('admin.expense.cash-details') }}" class="btn btn-secondary">Reset</a>
            </form>
        </div>
        <div class="col-md-4 text-right">
            <div class="text-muted">
                <small>Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</small>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="d-flex flex-wrap gap-5 mb-4">
        <div class="card bg-primary text-white summary-card mr-3" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h6 class="summary-card-title">Total Sales</h6>
                <div class="summary-card-value">{{ config('settings.currency_symbol') }} {{ number_format($totalSales, 2) }}</div>
            </div>
        </div>
        <div class="card bg-info text-white summary-card mr-3" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h6 class="summary-card-title">Total Purchase</h6>
                <div class="summary-card-value">{{ config('settings.currency_symbol') }} {{ number_format($totalPurchase, 2) }}</div>
            </div>
        </div>
        <div class="card bg-danger text-white summary-card mr-3" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h6 class="summary-card-title">Total Expenses</h6>
                <div class="summary-card-value">{{ config('settings.currency_symbol') }} {{ number_format($totalExpenses, 2) }}</div>
            </div>
        </div>
        <div class="card bg-warning text-white summary-card mr-3" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h6 class="summary-card-title">Sales Returns</h6>
                <div class="summary-card-value">-{{ config('settings.currency_symbol') }} {{ number_format($returnsTotal, 2) }}</div>
            </div>
        </div>
        <div class="card bg-orange text-white summary-card mr-3" style="width: 240px; height: 140px; background-color: #fd7e14 !important;">
            <div class="card-body text-center">
                <h6 class="summary-card-title">Purchase Returns</h6>
                <div class="summary-card-value">-{{ config('settings.currency_symbol') }} {{ number_format($purchaseReturnsTotal, 2) }}</div>
            </div>
        </div>
        <div class="card bg-dark text-white summary-card mr-3" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h6 class="summary-card-title">Damaged Products</h6>
                <div class="summary-card-value">-{{ config('settings.currency_symbol') }} {{ number_format($damagesTotal, 2) }}</div>
            </div>
        </div>
        <div class="card bg-success text-white summary-card mr-3" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h6 class="summary-card-title">Net Sales</h6>
                <div class="summary-card-value">{{ config('settings.currency_symbol') }} {{ number_format($netSales, 2) }}</div>
                <small class="text-white-50 d-block mt-2" style="font-size: 0.8em;">Net = Total Sales - Sales Returns - Purchase Returns</small>
            </div>
        </div>
        <div class="card bg-dark text-white summary-card mr-3" style="width: 240px; height: 140px;">
            <div class="card-body text-center">
                <h6 class="summary-card-title">Cash in Hand</h6>
                <div class="summary-card-value">{{ config('settings.currency_symbol') }} {{ number_format($cashInHand, 2) }}</div>
                <small class="text-white-50 d-block mt-2" style="font-size: 0.8em;">Net = Net Sales - Purchases - Expenses - Damaged Products</small>
            </div>
        </div>
    </div>

    <!-- Cash Flow Tables as Tabs -->
    <ul class="nav nav-tabs mb-3" id="cashDetailsTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="customer-payments-tab" data-toggle="tab" href="#customer-payments" role="tab" aria-controls="customer-payments" aria-selected="true">Customer Payments</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="supplier-payments-tab" data-toggle="tab" href="#supplier-payments" role="tab" aria-controls="supplier-payments" aria-selected="false">Supplier Payments</a>
        </li>
    </ul>
    <div class="tab-content" id="cashDetailsTabsContent">
        <div class="tab-pane fade show active" id="customer-payments" role="tabpanel" aria-labelledby="customer-payments-tab">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customer Payments</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Payment ID</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Order ID</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customerPayments as $payment)
                                <tr>
                                    <td>#{{ $payment->id }}</td>
                                    <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                    <td>{{ $payment->order && $payment->order->customer ? $payment->order->customer->first_name . ' ' . $payment->order->customer->last_name : 'N/A' }}</td>
                                    <td>#{{ $payment->order_id }}</td>
                                    <td>{{ config('settings.currency_symbol') }} {{ number_format($payment->amount, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No customer payments found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $customerPayments->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="supplier-payments" role="tabpanel" aria-labelledby="supplier-payments-tab">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Supplier Payments</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Payment ID</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Purchase ID</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supplierPayments as $payment)
                                <tr>
                                    <td>#{{ $payment->id }}</td>
                                    <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                    <td>{{ $payment->purchase && $payment->purchase->supplier ? $payment->purchase->supplier->first_name . ' ' . $payment->purchase->supplier->last_name : 'N/A' }}</td>
                                    <td>#{{ $payment->purchase_id }}</td>
                                    <td>{{ config('settings.currency_symbol') }} {{ number_format($payment->amount, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No supplier payments found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $supplierPayments->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Ensure Bootstrap tabs work if not already initialized
        $(function () {
            $('#cashDetailsTabs a').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
            });
        });
    </script>
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

@endsection
