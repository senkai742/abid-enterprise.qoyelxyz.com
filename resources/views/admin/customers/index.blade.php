@extends('admin.layouts.admin')

@section('title', __('customer.Customer_List'))
@section('content-header', __('customer.Customer_List'))
@section('content-actions')
<a href="{{route('admin.customers.create')}}" class="btn btn-primary">{{ __('customer.Add_Customer') }}</a>
@endsection
@section('css')
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')

<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>{{ __('customer.ID') }}</th>
                    <th>{{ __('customer.Avatar') }}</th>
                    <th>{{ __('customer.First_Name') }}</th>
                    <th>{{ __('customer.Last_Name') }}</th>
                    <th>{{ __('customer.Email') }}</th>
                    <th>{{ __('customer.Phone') }}</th>
                    <th>{{ __('customer.Address') }}</th>
                    <th>Previous Balance</th>
                    <th>{{ __('customer.Balance') }} (Due)</th>
                    <th>{{ __('common.Created_At') }}</th>
                    <th>{{ __('customer.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $customer)
                    <tr>
                        <td>{{ $customer->id }}</td>
                        <td>
                            <img width="40" style="border-radius:50%;" src="{{ $customer->getAvatarUrl() }}" alt="">
                        </td>
                        <td>{{ $customer->first_name }}</td>
                        <td>{{ $customer->last_name }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td>{{ $customer->address }}</td>
                        <td>
                            @php
                            $totalSales = $customer->orders->sum('gr_total');
                        @endphp
                        {{ config('settings.currency_symbol') }} {{ number_format($totalSales, 2) }}
                        </td>
                        <td>
                            @if(($customer->balance ?? 0) > 0)
                                <span class="badge badge-danger">{{ config('settings.currency_symbol') }} {{ number_format($customer->balance, 2) }}</span>
                            @else
                                <span class="badge badge-success">{{ config('settings.currency_symbol') }} 0.00</span>
                            @endif
                        </td>
                        <td>{{ $customer->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                            @if(($customer->balance ?? 0) > 0)
                            <button class="btn btn-sm btn-success btn-pay"
                                data-id="{{ $customer->id }}"
                                data-name="{{ $customer->first_name }} {{ $customer->last_name }}"
                                data-balance="{{ number_format($customer->balance, 2) }}"
                                data-url="{{ route('admin.customers.pay', $customer) }}"
                                title="Pay Balance">
                                <i class="fas fa-money-bill-wave"></i> Pay
                            </button>
                            @endif
                            <button class="btn btn-sm btn-danger btn-delete" data-url="{{ route('admin.customers.destroy', $customer) }}" title="Delete"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $customers->render() }}
    </div>
</div>

{{-- Pay Modal --}}
<div class="modal fade" id="customerPayModal" tabindex="-1" role="dialog" aria-labelledby="customerPayModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerPayModalLabel">Pay Customer Balance</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="customerPayForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <p>Customer: <strong id="modalCustomerName"></strong></p>
                    <p>Current Balance Due: <strong id="modalCustomerBalance"></strong></p>
                    <div class="form-group">
                        <label for="payAmount">Amount to Pay</label>
                        <input type="number" class="form-control" name="amount" id="payAmount" step="0.01" min="0.01" required placeholder="Enter amount">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Submit Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    $(document).ready(function() {

        // Pay button handler
        $(document).on('click', '.btn-pay', function() {
            var id       = $(this).data('id');
            var name     = $(this).data('name');
            var balance  = $(this).data('balance');
            var url      = $(this).data('url');

            $('#modalCustomerName').text(name);
            $('#modalCustomerBalance').text('{{ config("settings.currency_symbol") }} ' + balance);
            $('#payAmount').val('').attr('max', parseFloat(balance.replace(/,/g, '')));
            $('#customerPayForm').attr('action', url);
            $('#customerPayModal').modal('show');
        });

        // Delete button handler
        $(document).on('click', '.btn-delete', function() {
            var $this = $(this);
            const swal = Swal.mixin({
                customClass: { confirmButton: 'btn btn-success mr-2', cancelButton: 'btn btn-danger' },
                buttonsStyling: false
            });
            swal.fire({
                title: 'Are you sure?',
                text: 'Do you really want to delete this customer?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.post($this.data('url'), {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    }, function() {
                        $this.closest('tr').fadeOut(500, function() { $(this).remove(); });
                    });
                }
            });
        });
    });
</script>
@endsection
