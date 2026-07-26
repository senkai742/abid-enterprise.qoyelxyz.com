@extends('user.layouts.user')

@section('title', __('Purchase'))
@section('content-header', __('Purchase Product'))

@push('styles')
<style>
.product-row {
    transition: all 0.3s ease;
}
.product-row.highlight {
    background-color: #fff3cd;
    border: 2px solid #ffc107;
}
.branch-quantity-input {
    width: 80px;
}
.cart-table {
    table-layout: fixed;
    width: 100%;
}
.cart-table th,
.cart-table td {
    padding: 0.5rem;
    vertical-align: middle;
}
.qty-input {
    width: 70px;
}
.price-input {
    width: 120px;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-6 col-lg-6">
        <!-- Supplier Selection -->
        <div class="row mb-2">
            <div class="col-md-3">
                <b>Supplier Invoice</b>
                <input type="text"
                       id="supplier_invoice_no"
                       name="supplier_invoice_no"
                       placeholder="Invoice no"
                       class="form-control">
            </div>
            <div class="col-md-9">
                <b>Supplier Name</b>
                <select id="supplier_id" name="supplier_id" class="form-control">
                    <option value="">Select a supplier</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}"
                            data-balance="{{ $supplier->balance }}"
                            data-first-name="{{ $supplier->first_name }}"
                            data-last-name="{{ $supplier->last_name }}"
                            data-address="{{ $supplier->address }}"
                            data-phone="{{ $supplier->phone }}">
                        {{ $supplier->first_name }} {{ $supplier->last_name }} - {{ $supplier->address }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Supplier Info Display -->
        <div class="row mb-3" id="supplier-info" style="display: none;">
            <div class="col-md-4">
                <span class="text-primary"><b id="display-name"></b></span>
            </div>
            <div class="col-md-3">
                <span class="text-primary"><b id="display-address"></b></span>
            </div>
            <div class="col-md-2">
                <span class="text-primary"><b id="display-phone"></b></span>
            </div>
            <div class="col-md-3">
                <span class="text-primary"><b>Balance: <span id="display-balance"></span> BDT</b></span>
            </div>
        </div>

        <!-- Cart -->
        <div class="user-cart mt-1">
            <div class="card">
                <div style="overflow-x: auto;">
                    <table class="table table-striped cart-table">
                        <thead>
                            <tr>
                                <th style="width: 25%">Product Name</th>
                                <th style="width: 20%">Branch</th>
                                <th style="width: 15%">Quantity</th>
                                <th class="text-right" style="width: 20%">Purchase Rate</th>
                                <th class="text-right" style="width: 20%">Total</th>
                            </tr>
                        </thead>
                        <tbody id="cart-items">
                            @if($cart->count() > 0)
                                @foreach($cart as $item)
                                <tr class="product-row" data-product-id="{{ $item->id }}">
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->branch->branch_name ?? 'Default' }}</td>
                                    <td>
                                        <input type="text"
                                               class="form-control form-control-sm qty-input"
                                               value="{{ $item->pivot->qnty }}"
                                               onchange="updateQuantity({{ $item->id }}, this.value)">
                                        <button class="btn btn-danger btn-sm mt-1"
                                                onclick="removeFromCart({{ $item->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                    <td width="120px">
                                        <div class="input-group input-group-sm mb-1">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">{{ config('settings.currency_symbol', '৳') }}</span>
                                            </div>
                                            <input type="text"
                                                   class="form-control input-sm price-input"
                                                   placeholder="Purchase Price"
                                                   value="{{ $item->pivot->purchase_price }}"
                                                   onchange="updatePurchasePrice({{ $item->id }}, this.value)">
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        {{ config('settings.currency_symbol', '৳') }} {{ number_format($item->pivot->purchase_price * $item->pivot->qnty, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No items in cart</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Totals -->
        <div class="row">
            <div class="col">
                <div class="font-weight-bold">Sub. Total</div>
                {{ config('settings.currency_symbol', '৳') }} <span id="sub_total">{{ number_format($sub_total, 2) }}</span>
            </div>
            <div class="col">
                <div class="font-weight-bold">Discount</div>
                <input type="text"
                       id="discount_amount"
                       placeholder="Discount amount"
                       class="form-control form-sm text-right"
                       value="{{ number_format($discount_amount, 2) }}"
                       onchange="calculateTotals()">
            </div>
            <div class="col text-right">
                <div class="font-weight-bold">Gr. Total</div>
                <div>{{ config('settings.currency_symbol', '৳') }} <span id="gr_total">{{ number_format($gr_total, 2) }}</span></div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col">
                <div class="font-weight-bold">Total Balance</div>
                <input type="text"
                       id="new_balance"
                       readonly
                       class="form-control form-sm text-right"
                       value="{{ number_format($new_balance, 2) }}">
            </div>
            <div class="col">
                <div class="font-weight-bold">Paid Amount</div>
                <input type="text"
                       id="paid_amount"
                       placeholder="Paid amount"
                       class="form-control form-sm text-right"
                       value="{{ number_format($paid_amount, 2) }}"
                       onchange="calculateLastBalance()">
            </div>
            <div class="col text-right">
                <div class="font-weight-bold">Last Balance</div>
                <input type="text"
                       id="last_balance"
                       readonly
                       class="form-control form-sm text-right"
                       value="{{ number_format($last_balance, 2) }}">
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-3">
            <div class="col">
                @if(isset($printUrl))
                <a href="{{ $printUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-block">
                    🖨️ Print Invoice
                </a>
                @else
                <button class="btn btn-default btn-block border" disabled>
                    🖨️ Print Invoice
                </button>
                @endif
            </div>
            <div class="col">
                <button type="button"
                        class="btn btn-danger btn-block"
                        onclick="emptyCart()"
                        @if($cart->count() == 0) disabled @endif>
                    Cancel
                </button>
            </div>
            <div class="col">
                <button type="button"
                        class="btn btn-primary btn-block"
                        onclick="submitPurchase()"
                        @if($cart->count() == 0) disabled @endif>
                    Make Purchase
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-6">
        <!-- Product Search -->
        <div class="row">
            <div class="col">
                <b>Product Code</b>
                <form onsubmit="scanBarcode(event)">
                    <input type="text"
                           id="barcode"
                           class="form-control"
                           placeholder="Scan barcode">
                </form>
            </div>
            <div class="col-lg-8 mb-2">
                <b>Product Name</b>
                <input type="text"
                       id="product_search"
                       class="form-control"
                       placeholder="Search product..."
                       onkeyup="searchProducts(event)">
            </div>
        </div>

        <!-- Branch Selection (for multi-branch) -->
        <div class="row mb-3">
            <div class="col">
                <b>Branch</b>
                <select id="branch_id" class="form-control">
                    <option value="">Select branch</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Products List -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Products</h5>
            </div>
            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                <div id="products-list">
                    @foreach($products as $product)
                    <div class="product-item border-bottom pb-2 mb-2"
                         id="product-{{ $product->barcode }}"
                         data-barcode="{{ $product->barcode }}"
                         data-id="{{ $product->id }}"
                         data-purchase-price="{{ $product->purchase_price }}"
                         data-name="{{ $product->name }}">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-1">{{ $product->name }}</h6>
                                <small class="text-muted">Code: {{ $product->barcode }}</small>
                                <br>
                                <small>Price: {{ config('settings.currency_symbol', '৳') }} {{ number_format($product->purchase_price, 2) }}</small>
                            </div>
                            <div class="col-md-4 text-right">
                                <button class="btn btn-sm btn-primary"
                                        onclick="addToCart('{{ $product->barcode }}')">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
// Global variables
let cart = @json($cart->toArray());
let suppliers = @json($suppliers);
let branches = @json($branches);
let currentSubTotal = {{ $sub_total }};
let currentDiscount = {{ $discount_amount }};
let currentGrTotal = {{ $gr_total }};
let currentPaidAmount = {{ $paid_amount }};
let currentNewBalance = {{ $new_balance }};
let currentLastBalance = {{ $last_balance }};

// Initialize
$(document).ready(function() {
    updateSupplierInfo();
    calculateTotals();
});

// Supplier selection
$('#supplier_id').change(function() {
    updateSupplierInfo();
});

function updateSupplierInfo() {
    const supplierId = $('#supplier_id').val();
    if (supplierId) {
        const supplier = suppliers.find(s => s.id == supplierId);
        if (supplier) {
            $('#display-name').text(supplier.first_name + ' ' + supplier.last_name);
            $('#display-address').text(supplier.address);
            $('#display-phone').text(supplier.phone);
            $('#display-balance').text(supplier.balance);
            $('#supplier-info').show();

            // Update balances
            calculateTotals();
        }
    } else {
        $('#supplier-info').hide();
    }
}

// Barcode scanning
function scanBarcode(event) {
    event.preventDefault();
    const barcode = $('#barcode').val();
    const supplierId = $('#supplier_id').val();

    if (!barcode) {
        alert('Please enter a barcode');
        return;
    }

    if (!supplierId) {
        Swal.fire('Please select a supplier', '', 'warning');
        return;
    }

    addToCart(barcode);
    $('#barcode').val('');
}

// Product search
function searchProducts(event) {
    const searchTerm = event.target.value.toLowerCase();
    $('.product-item').each(function() {
        const productName = $(this).data('name').toLowerCase();
        const barcode = $(this).data('barcode').toLowerCase();

        if (productName.includes(searchTerm) || barcode.includes(searchTerm)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

// Add to cart
function addToCart(barcode) {
    const supplierId = $('#supplier_id').val();
    const supplierInvoiceNo = $('#supplier_invoice_no').val();
    const branchId = $('#branch_id').val();

    if (!supplierId) {
        Swal.fire('Please select a supplier', '', 'warning');
        return false;
    }

    // Highlight product
    $('.product-item').removeClass('highlight');
    $(`#product-${barcode}`).addClass('highlight');

    $.ajax({
        url: '/purchase-cart',
        method: 'POST',
        data: {
            barcode: barcode,
            supplier_id: supplierId,
            supplier_invoice_no: supplierInvoiceNo,
            branch_id: branchId,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            location.reload(); // Reload to update cart
        },
        error: function(xhr) {
            Swal.fire('Error!', xhr.responseJSON.message || 'Failed to add product', 'error');
        }
    });
}

// Update quantity
function updateQuantity(productId, quantity) {
    if (!quantity || quantity <= 0) return;

    $.ajax({
        url: '/purchase-cart/change-qty',
        method: 'POST',
        data: {
            product_id: productId,
            quantity: quantity,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            location.reload(); // Reload to update cart
        },
        error: function(xhr) {
            Swal.fire('Error!', xhr.responseJSON.message || 'Failed to update quantity', 'error');
        }
    });
}

// Update purchase price
function updatePurchasePrice(productId, purchasePrice) {
    if (!purchasePrice || purchasePrice <= 0) return;

    $.ajax({
        url: '/purchase-cart/change-purchaseprice',
        method: 'POST',
        data: {
            product_id: productId,
            purchase_price: purchasePrice,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            location.reload(); // Reload to update cart
        },
        error: function(xhr) {
            Swal.fire('Error!', xhr.responseJSON.message || 'Failed to update price', 'error');
        }
    });
}

// Remove from cart
function removeFromCart(productId) {
    $.ajax({
        url: '/purchase-cart/delete',
        method: 'POST',
        data: {
            product_id: productId,
            _method: 'DELETE',
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            location.reload(); // Reload to update cart
        },
        error: function(xhr) {
            Swal.fire('Error!', xhr.responseJSON.message || 'Failed to remove item', 'error');
        }
    });
}

// Empty cart
function emptyCart() {
    $.ajax({
        url: '/purchase-cart/empty',
        method: 'POST',
        data: {
            _method: 'DELETE',
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            location.reload(); // Reload to update cart
        },
        error: function(xhr) {
            Swal.fire('Error!', xhr.responseJSON.message || 'Failed to empty cart', 'error');
        }
    });
}

// Calculate totals
function calculateTotals() {
    const discountAmount = parseFloat($('#discount_amount').val()) || 0;
    const supplierId = $('#supplier_id').val();

    let newBalance = currentNewBalance;
    let grTotal = currentGrTotal;

    if (supplierId) {
        const supplier = suppliers.find(s => s.id == supplierId);
        if (supplier) {
            newBalance = parseFloat(supplier.balance) + (currentSubTotal - discountAmount);
        }
    }

    grTotal = currentSubTotal - discountAmount;

    $('#gr_total').text(numberFormat(grTotal));
    $('#new_balance').val(numberFormat(newBalance));

    currentGrTotal = grTotal;
    currentNewBalance = newBalance;
    currentDiscount = discountAmount;

    calculateLastBalance();
}

// Calculate last balance
function calculateLastBalance() {
    const paidAmount = parseFloat($('#paid_amount').val()) || 0;
    const lastBalance = currentNewBalance - paidAmount;

    $('#last_balance').val(numberFormat(lastBalance));
    currentLastBalance = lastBalance;
    currentPaidAmount = paidAmount;
}

// Submit purchase
function submitPurchase() {
    const supplierId = $('#supplier_id').val();

    if (!supplierId) {
        Swal.fire('Please select a supplier', '', 'warning');
        return false;
    }

    Swal.fire({
        title: 'Save Purchase Invoice',
        input: "text",
        inputValue: currentPaidAmount,
        showCancelButton: true,
        confirmButtonText: "Save Purchase",
        cancelButtonText: "Cancel",
        showLoaderOnConfirm: true,
        preConfirm: (amount) => {
            return $.ajax({
                url: '/purchase',
                method: 'POST',
                data: {
                    supplier_id: supplierId,
                    sub_total: currentSubTotal,
                    discount_amount: currentDiscount,
                    gr_total: currentGrTotal,
                    paid_amount: amount,
                    amount: amount,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire("Success", "Purchase has been saved!", "success");
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                    return response.data;
                },
                error: function(xhr) {
                    Swal.showValidationMessage(xhr.responseJSON.message);
                }
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    });
}

// Number formatting
function numberFormat(amount) {
    if (amount > 0) {
        return Number(amount).toFixed(2);
    } else {
        return '0.00';
    }
}
</script>
@endsection
