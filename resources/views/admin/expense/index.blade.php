@extends('admin.layouts.admin')

@section('title', __('Expense'))
@section('content-header', __('Expense'))
@section('content-actions')
<a href="{{route('admin.expense.create')}}" class="btn btn-primary"><i class="fa fa-plus"></i> Add Expenses</a>
@endsection
@section('content')

<div class="card">
    <div class="card-body">
        <!-- Tab navigation -->
        <ul class="nav nav-tabs" id="expenseTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="report-tab" data-toggle="tab" href="#report" role="tab" aria-controls="report" aria-selected="true">Report</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="expense-tab" data-toggle="tab" href="#expense" role="tab" aria-controls="expense" aria-selected="false">Expense</a>
            </li>
        </ul>
        <div class="tab-content mt-3" id="expenseTabContent">
            <!-- Report Tab -->
            <div class="tab-pane fade show active" id="report" role="tabpanel" aria-labelledby="report-tab">
                <div class="report-summary-area p-3" style="background: #f8f9fa; border-radius: 8px;">
                    <h4 class="mb-4 font-weight-bold">Financial Summary Report</h4>

                    <!-- Period Display -->
                    <div class="text-right mb-3">
                        <div class="text-muted">
                            <small>Period: {{ \Carbon\Carbon::now()->startOfMonth()->format('M d, Y') }} - {{ \Carbon\Carbon::now()->endOfMonth()->format('M d, Y') }}</small>
                        </div>
                    </div>

                    <div class="report-cards-row">
                        <div class="report-card mb-3">
                            <a href="{{ route('admin.expense.sales-details') }}" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#007bff;"><i class="fa fa-shopping-cart"></i></div>
                                        <div class="font-weight-bold">Total Sales</div>
                                        <div class="h5 text-success mt-1">{{ config('settings.currency_symbol') }} {{ number_format($netSales, 2) }}
                                            <small class="text-muted d-block" style="font-size: 0.8em;">Net (after returns, purchase returns)</small>
                                            <small class="text-muted d-block" style="font-size: 0.8em;">Gross: {{ config('settings.currency_symbol') }} {{ number_format($totalSales, 2) }}, Sales Returns: -{{ config('settings.currency_symbol') }} {{ number_format($returnsTotal, 2) }}, Purchase Returns: -{{ config('settings.currency_symbol') }} {{ number_format($purchaseReturnsTotal, 2) }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="report-card mb-3">
                            <a href="{{ route('admin.expense.purchase-details') }}" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#6f42c1;"><i class="fa fa-truck"></i></div>
                                        <div class="font-weight-bold">Total Purchase</div>
                                        <div class="h5 text-primary mt-1">{{ config('settings.currency_symbol') }} {{ number_format($totalPurchase, 2) }}</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="report-card mb-3">
                            <a href="{{ route('admin.purchasereturn.index') }}" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#fd7e14;"><i class="fa fa-undo-alt"></i></div>
                                        <div class="font-weight-bold">Purchase Returns</div>
                                        <div class="h5 text-warning mt-1">-{{ config('settings.currency_symbol') }} {{ number_format($purchaseReturnsTotal, 2) }}
                                            <small class="text-muted d-block" style="font-size: 0.8em;">Profit Loss: -{{ config('settings.currency_symbol') }} {{ number_format($purchaseReturnsProfit, 2) }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="report-card mb-3">
                            <a href="{{ route('admin.expense.expense-details') }}" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#dc3545;"><i class="fa fa-credit-card"></i></div>
                                        <div class="font-weight-bold">Total Expenses</div>
                                        <div class="h5 text-danger mt-1">{{ config('settings.currency_symbol') }} {{ number_format($totalExpenses, 2) }}</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="report-card mb-3">
                            <a href="{{ route('admin.damage.index') }}" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#343a40;"><i class="fa fa-exclamation-triangle"></i></div>
                                        <div class="font-weight-bold">Damaged Products</div>
                                        <div class="h5 text-dark mt-1">-{{ config('settings.currency_symbol') }} {{ number_format($damagesTotal, 2) }}</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="report-card mb-3">
                            <a href="{{ route('admin.expense.profit-details') }}" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#28a745;"><i class="fa fa-chart-line"></i></div>
                                        <div class="font-weight-bold">Total Profit</div>
                                        <div class="h5 text-success mt-1">{{ config('settings.currency_symbol') }} {{ number_format($netProfit, 2) }}
                                            <small class="text-muted d-block" style="font-size: 0.8em;">Net (after all returns & damages)</small>
                                            <small class="text-muted d-block" style="font-size: 0.8em;">Gross: {{ config('settings.currency_symbol') }} {{ number_format($totalProfit, 2) }}, Sales Returns: -{{ config('settings.currency_symbol') }} {{ number_format($returnsProfit, 2) }}, Purchase Returns: -{{ config('settings.currency_symbol') }} {{ number_format($purchaseReturnsProfit, 2) }}, Damages: -{{ config('settings.currency_symbol') }} {{ number_format($damagesTotal, 2) }}</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="report-card mb-3">
                            <a href="{{ route('admin.expense.cash-details') }}" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#ffc107;"><i class="fa fa-wallet"></i></div>
                                        <div class="font-weight-bold">Cash in Hand</div>
                                        <div class="h5 text-warning mt-1">{{ config('settings.currency_symbol') }} {{ number_format($cashInHand, 2) }}
                                            <small class="text-muted d-block" style="font-size: 0.8em;">Net (after all returns & damages)</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="report-card mb-3">
                            <a href="{{ route('admin.expense.purchase-details') }}" class="text-decoration-none">
                                <div class="card shadow-sm border-0 text-center h-100">
                                    <div class="card-body d-flex flex-column justify-content-center" style="min-height: 150px;">
                                        <div class="mb-2" style="font-size:2rem; color:#e83e8c;"><i class="fa fa-exclamation-circle"></i></div>
                                        <div class="font-weight-bold">Purchase Due</div>
                                        <div class="h5 text-danger mt-1">{{ config('settings.currency_symbol') }} {{ number_format($totalPurchaseDue, 2) }}</div>
                                        <small class="text-muted d-block" style="font-size: 0.8em;">Unpaid/Partially Paid Purchases</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Expense Tab -->
            <div class="tab-pane fade" id="expense" role="tabpanel" aria-labelledby="expense-tab">
                <!-- Existing expense filter and table -->
                <div class="row">
                    <div class="col-md-5"></div>
                    <div class="col-md-7">
                        <form action="{{route('admin.expense.index')}}">
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="date" name="start_date" class="form-control" value="{{request('start_date')}}" />
                                </div>
                                <div class="col-md-3">
                                    <input type="date" name="end_date" class="form-control" value="{{request('end_date')}}" />
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <select class="form-control" name="expense_head" id="expense_head">
                                            <option value="">:: Select expense head ::</option>
                                            @if(request('expense_head'))
                                                <option value="">-- Clear Selection --</option>
                                            @endif
                                            @foreach ($expense_heads as $exp )
                                                <option value='{{$exp->expense_head}}' {{ request('expense_head') == $exp->expense_head ? 'selected' : '' }}>
                                                    {{$exp->expense_head}}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('product_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <button class="btn btn-outline-primary" type="submit">{{ __('Show') }}</button>
                                    @if(request('start_date') || request('end_date') || request('expense_head'))
                                        <a href="{{ route('admin.expense.index') }}" class="btn btn-outline-secondary ml-2">
                                            <i class="fa fa-times"></i> Clear
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ 'ID' }}</th>
                            <th>{{ 'Expense' }}</th>
                            <th>{{ 'Expense Amount.' }}</th>
                            <th>{{ 'Description' }}</th>
                            <th>{{ 'Created' }}</th>

                        </tr>
                    </thead>
                    <tbody>
                        @if($expenses->count() > 0)
                            @foreach ($expenses as $expense)
                            <tr>
                                <td> {{$expense->id}}</td>
                                <td>{{$expense->expense_head}}</td>
                                <td>{{number_format($expense->expense_amount,0)}}</td>
                                <td>{{$expense->expense_description}}</td>
                                <td>{{$expense->created_at}}</td>
                                <td><form action="/admin/expense/{{$expense->id}}" method="POST" onsubmit="return confirm('Are you sure you want to delete this?');"> @method('DELETE') @csrf <button type="submit"><i class="fa fa-trash"></i></button></form></td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="fa fa-info-circle"></i>
                                        @if(request('expense_head') || request('start_date') || request('end_date'))
                                            No expenses found matching your filter criteria.
                                        @else
                                            No expenses found. <a href="{{ route('admin.expense.create') }}" class="alert-link">Create your first expense</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    @if($expenses->count() > 0)
                    <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th>{{ config('settings.currency_symbol') }} {{ number_format($total, 2) }}</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>

                @if($expenses->count() > 0)
                <div class="text-center">{{ $expenses->render() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@section('model')
<!-- Modal -->
<div class="modal fade" id="modalInvoice" tabindex="-1" role="dialog" aria-labelledby="modalInvoiceLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInvoiceLabel">Next Gen POS</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Placeholder for dynamic content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

<style>
.report-cards-row {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  justify-content: flex-start;
}
.report-card {
  flex: 0 0 220px;
  max-width: 220px;
  min-width: 220px;
  margin-bottom: 1.5rem;
}
.report-card a {
  display: block;
  border-radius: 12px;
  transition: box-shadow 0.2s, background 0.2s, transform 0.15s;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  cursor: pointer;
  background: #fff;
}
.report-card a:hover, .report-card a:focus {
  box-shadow: 0 6px 24px rgba(0,0,0,0.12);
  background: #f5f7fa;
  text-decoration: none;
  transform: translateY(-2px) scale(1.03);
}
.report-card a:active {
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  background: #f0f0f0;
  transform: scale(0.98);
}
@media (max-width: 991.98px) {
  .report-cards-row {
    flex-wrap: wrap;
    justify-content: center;
  }
  .report-card {
    flex: 1 1 45%;
    max-width: 100%;
    min-width: 180px;
  }
}
@media (max-width: 767.98px) {
  .report-card {
    flex: 1 1 100%;
    max-width: 100%;
    min-width: 0;
  }
}
</style>

@section('js')
<script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Use event delegation to bind to the document for dynamically generated elements
    $(document).on('click', '.btnShowInvoice', function(event) {
        console.log("Modal show event triggered!");

        // Fetch data from the clicked button
        var button = $(this); // Button that triggered the modal
        var salesreturnId = button.data('salesreturn-id');
        var customerName = button.data('customer-name');
        var totalAmount = button.data('total');
        var receivedAmount = button.data('received');
        var payment = button.data('payment');
        var createdAt = button.data('created-at');
        var items = button.data('items'); // Ensure this is correctly passed as a JSON

        // Log the data to ensure it's being captured correctly
        console.log({
            salesreturnId,
            customerName,
            totalAmount,
            receivedAmount,
            createdAt,
            items
        });

        // Open the modal
        $('#modalInvoice').modal('show');

        // Populate the modal body with dynamic data (you can extend this part)
        var modalBody = $('#modalInvoice').find('.modal-body');

        // Construct items HTML if items exist
        var itemsHTML = '';
        if (items) {
            items.forEach(function(item, index) {
                itemsHTML += `
            <tr>
                <td>${index + 1}</td>
                <td>${item.product.name}</td>
                <td>${item.description || 'N/A'}</td>
                <td>${parseFloat(item.product.sell_price).toFixed(2)}</td>
                <td>${item.quantity}</td>
                <td>${(parseFloat(item.product.sell_price) * item.quantity).toFixed(2)}</td>
            </tr>
        `;
            });
        }

        // Update the modal body content
        modalBody.html(`
    <div class="card">
        <div class="card-header">
            Invoice <strong>${createdAt.split('T')[0]}</strong>
            <span class="float-right"> <strong>Status:</strong> ${

                        receivedAmount == 0?
                            '<span class="badge badge-danger">{{ __('salesreturn.Not_Paid') }}</span>':
                        receivedAmount < totalAmount ?
                            '<span class="badge badge-warning">{{ __('salesreturn.Partial') }}</span>':
                        receivedAmount == totalAmount?
                            '<span class="badge badge-success">{{ __('salesreturn.Paid') }}</span>':
                        receivedAmount > totalAmount?
                            '<span class="badge badge-info">{{ __('salesreturn.Change') }}</span>':''
            }</span>


        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="mb-3">To: <strong>${customerName || 'N/A'}</strong></h6>
                </div>
            </div>
            <div class="table-responsive-sm">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th>Unit Cost</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHTML}
                    </tbody>
                    <tfoot>
                      <tr>
                        <th class="text-right" colspan="5">
                          Total
                        </th>
                        <th class="right">
                          <strong>{{config('settings.currency_symbol')}} ${totalAmount}</strong>
                        </th>
                      </tr>

                      <tr>
                        <th class="text-right" colspan="5">
                          Paid
                        </th>
                        <th class="right">
                          <strong>{{config('settings.currency_symbol')}} ${receivedAmount}</strong>
                        </th>
                      </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
  </div>
</div>
`);
    });
    $(document).ready(function() {
    // Event handler when the partial payment modal is triggered
    $('#partialPaymentModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal

        // Get the salesreturn ID from data-attributes
        var salesreturnId = button.data('salesreturns-id');
        var remainingAmount = button.data('remaining-amount');

        // Find modal and set the salesreturn ID in the hidden field
        var modal = $(this);
        modal.find('#modalsalesreturnId').val(salesreturnId);
        modal.find('#partialAmount').attr('max', remainingAmount); // Set max value for partial payment
    });
});

</script>
@endsection
