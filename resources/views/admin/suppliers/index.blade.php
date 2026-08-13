@extends('admin.layouts.admin')

@section('title', __('Supplier List'))
@section('content-header', __('Supplier List'))
@section('content-actions')
<a href="{{route('admin.suppliers.create')}}" class="btn btn-primary">{{ __('Add Supplier') }}</a>
@endsection
@section('css')
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <!-- <th>{{ __('supplier.Avatar') }}</th> -->
                    <th>{{ __('First Name') }}</th>
                    <th>{{ __('Last Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Address') }}</th>
                    <th>{{ __('Balance') }}</th>
                    <th>{{ __('Created At') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($suppliers as $supplier)
                <tr>
                    <td>{{$supplier->id}}</td>
                    {{-- <td>
                       <img width="50" src="{{$supplier->getAvatarUrl()}}" alt="">
                    </td> --}}
                    <td>{{$supplier->first_name}}</td>
                    <td>{{$supplier->last_name}}</td>
                    <td>{{$supplier->email}}</td>
                    <td>{{$supplier->phone}}</td>
                    <td>{{$supplier->address}}</td>
                    <td>{{ config('settings.currency_symbol') }} {{ number_format($supplier->balance ?? 0, 2) }}</td>
                    <td>{{$supplier->created_at}}</td>
                    <td>
                        @php
                            $paymentHistory = [];
                            foreach ($supplier->balancePayments->sortByDesc('created_at') as $pmt) {
                                $paymentHistory[] = [
                                    'id' => $pmt->id,
                                    'amount' => $pmt->amount,
                                    'date' => $pmt->created_at ? $pmt->created_at->format('Y-m-d H:i') : ''
                                ];
                            }
                        @endphp
                        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                        <a href="{{ route('admin.suppliers.pay', $supplier) }}" class="btn btn-sm btn-success" title="Pay">Pay</a>
                        <button class="btn btn-sm btn-info btn-history"
                            data-name="{{ $supplier->first_name }} {{ $supplier->last_name }}"
                            data-history="{{ json_encode($paymentHistory) }}"
                            title="Payment History">
                            <i class="fas fa-history"></i> History
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete" data-url="{{route('admin.suppliers.destroy', $supplier)}}" title="Delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $suppliers->render() }}
    </div>
</div>

{{-- Payment History Modal --}}
<div class="modal fade" id="paymentHistoryModal" tabindex="-1" role="dialog" aria-labelledby="paymentHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentHistoryModalLabel">Payment History - <span id="historyCustomerName"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-striped table-bordered" id="historyTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date & Time</th>
                            <th>Amount Paid</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script type="module">
    $(document).ready(function() {

        // History button handler
        $(document).on('click', '.btn-history', function() {
            var name = $(this).data('name');
            var history = $(this).data('history') || [];

            $('#historyCustomerName').text(name);
            var tbody = $('#historyTableBody');
            tbody.empty();

            if (history.length === 0) {
                tbody.append('<tr><td colspan="3" class="text-center text-muted">No payment history found for this supplier.</td></tr>');
            } else {
                var symbol = '{{ config("settings.currency_symbol") }}';
                var totalPaid = 0;
                
                // Sort history by latest date
                history.sort(function(a, b) {
                    return new Date(b.date) - new Date(a.date);
                });

                history.forEach(function(item, index) {
                    var amt = parseFloat(item.amount) || 0;
                    totalPaid += amt;
                    tbody.append(`
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.date}</td>
                            <td>${symbol} ${amt.toFixed(2)}</td>
                        </tr>
                    `);
                });
                tbody.append(`
                    <tr class="bg-light font-weight-bold">
                        <td colspan="2" class="text-right">Total Payments Made:</td>
                        <td>${symbol} ${totalPaid.toFixed(2)}</td>
                    </tr>
                `);
            }

            $('#paymentHistoryModal').modal('show');
        });

        $(document).on('click', '.btn-delete', function() {
            var $this = $(this);
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            })

            swalWithBootstrapButtons.fire({
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
                        _token: '{{csrf_token()}}'
                    }, function(res) {
                        $this.closest('tr').fadeOut(500, function() {
                            $(this).remove();
                        })
                    })
                }
            })
        })
    })
</script>
@endsection
