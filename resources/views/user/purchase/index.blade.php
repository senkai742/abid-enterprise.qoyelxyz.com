@extends('layouts.admin')

@section('title', __('Purchase'))
@section('content-header', __('Purchase'))
@section('content-actions')
<a href="{{route('purchases.create')}}" class="btn btn-primary">{{ __('New Purchase') }}</a>
@endsection
@section('content')

<div class="card">
    
    <div class="card-body">
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <form action="{{route('purchase.index')}}">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="date" name="start_date" class="form-control" value="{{request('start_date')}}" />
                        </div>
                        <div class="col-md-4">
                            <input type="date" name="end_date" class="form-control" value="{{request('end_date')}}" />
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-primary" type="submit">{{ __('Purchase submit') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ 'ID' }}</th>
                    <th>{{ 'Supplier' }}</th>
                    <th>{{ 'Invoice' }}</th>
                    <th>{{ 'Total' }}</th>
                    <th>{{ 'Discount' }}</th>
                    <th>{{ 'Gr. Total' }}</th>
                    <th>{{ 'Paid' }}</th>
                    <th>{{ 'Due' }}</th>
                    <th>{{ 'Created' }}</th>
           
                </tr>
            </thead>
            <tbody>
                @foreach ($purchases as $purchase)
                <tr>
                    <td> {{$purchase->id}}</td>
                    <td>{{$purchase->supplier?$purchase->supplier->first_name:""}}</td>
                    <td>{{$purchase->invoice_no}}</td>
                    <td>{{ config('settings.currency_symbol') }} {{number_format($purchase->sub_total)}}</td>
                    <td>{{ config('settings.currency_symbol') }} {{number_format($purchase->discount_amount)}}</td>
                    <td>{{ config('settings.currency_symbol') }} {{number_format($purchase->gr_total)}}</td>
                    <td>{{ config('settings.currency_symbol') }} {{number_format($purchase->paid_amount)}}</td>
                    <td>{{$purchase->created_at}}</td>
                    <td><a href="/admin/purchase/details/{{ $purchase->id }}" class="btn btn-success"><i class="fa fa-eye"></i></a></td>
                     
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th>{{ config('settings.currency_symbol') }} {{ number_format($total, 2) }}</th>
                    <th>{{ config('settings.currency_symbol') }} {{ number_format($total, 2) }}</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    
        <div class="text-center"> </div>
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
                                    '<span class="badge badge-danger">salesreturn.Not_Paid</span>':
                                receivedAmount < totalAmount ?
                                    '<span class="badge badge-warning">salesreturn.Partial</span>':
                                receivedAmount == totalAmount?
                                    '<span class="badge badge-success">salesreturn.Paid</span>':
                                receivedAmount > totalAmount?
                                    '<span class="badge badge-info">salesreturn.Change</span>':''
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
                            </tfood>
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
