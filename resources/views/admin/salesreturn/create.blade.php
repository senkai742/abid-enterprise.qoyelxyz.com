@extends('layouts.admin')

@section('title', __('Sales Return'))
@section('content-header', __('Sales Return Product'))

@push('styles')
<style>
.product-row {
    transition: all 0.3s ease;
}
.product-row.highlight {
    background-color: #fff3cd;
    border: 2px solid #ffc107;
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
        <!-- Customer Selection -->
        <div class="row mb-2">
            <div class="col-md-3">
                <b>Order ID</b>
                <input type="text"
                       id="order_id"
                       name="order_id"
                       placeholder="Order ID"
                       class="form-control">
                <button type="button" class="btn btn-info btn-sm mt-1" onclick="findOrderID()">
                    Find Order
                </button>
            </div>
            <div class="col-md-9">
                <b>Customer Name</b>
                <select id="customer_id" name="customer_id" class="form-control">
                    <option value="">Select a customer</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}"
                            data-balance="{{ $customer->balance }}"
                            data-first-name="{{ $customer->first_name }}"
                            data-last-name="{{ $customer->last_name }}"
                            data-address="{{ $customer->address }}"
                            data-phone="{{ $customer->phone }}">
                        {{ $customer->first_name }} {{ $customer->last_name }} - {{ $customer->address }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Customer Info Display -->
        <div class="row mb-3" id="customer-info" style="display: none;">
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
                                <th style="width: 30%">Product Name</th>
                                <th style="width: 15%">Quantity</th>
                                <th class="text-right" style="width: 20%">Sell Rate</th>
                                <th class="text-right" style="width: 20%">Total</th>
                                <th style="width: 15%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cart-items">
                            @if($cart->count() > 0)
                                @foreach($cart as $item)
                                <tr class="product-row" data-product-id="{{ $item->id }}">
                                    <td>{{ $item->name }}</td>
                                    <td>
                                        <input type="text"
                                               class="form-control form-control-sm qty-input"
                                               value="{{ $item->pivot->qnty }}"
                                               onchange="updateQuantity({{ $item->id }}, this.value)">
                                    </td>
                                    <td width="120px">
                                        <div class="input-group input-group-sm mb-1">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">{{ config('settings.currency_symbol', '৳') }}</span>
                                            </div>
                                            <input type="text"
                                                   class="form-control input-sm price-input"
                                                   placeholder="Sell Price"
                                                   value="{{ $item->pivot->sell_price }}"
                                                   onchange="updateSellPrice({{ $item->id }}, this.value)">
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        {{ config('settings.currency_symbol', '৳') }} {{ number_format($item->pivot->sell_price * $item->pivot->qnty, 2) }}
                                    </td>
                                    <td>
                                        <button class="btn btn-danger btn-sm"
                                                onclick="removeFromCart({{ $item->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
                <div class="font-weight-bold">Return Amount</div>
                <input type="text"
                       id="return_amount"
                       placeholder="Return amount"
                       class="form-control form-sm text-right"
                       value="{{ number_format($return_amount, 2) }}"
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
                        onclick="submitSalesReturn()"
                        @if($cart->count() == 0) disabled @endif>
                    Process Return
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
                         data-sell-price="{{ $product->sell_price }}"
                         data-name="{{ $product->name }}">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-1">{{ $product->name }}</h6>
                                <small class="text-muted">Code: {{ $product->barcode }}</small>
                                <br>
                                <small>Price: {{ config('settings.currency_symbol', '৳') }} {{ number_format($product->sell_price, 2) }}</small>
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
let customers = @json($customers);
let currentSubTotal = {{ $sub_total }};
let currentDiscount = {{ $discount_amount }};
let currentGrTotal = {{ $gr_total }};
let currentReturnAmount = {{ $return_amount }};
let currentNewBalance = {{ $new_balance }};
let currentLastBalance = {{ $last_balance }};

// Initialize
$(document).ready(function() {
    updateCustomerInfo();
    calculateTotals();
});

// Customer selection
$('#customer_id').change(function() {
    updateCustomerInfo();
});

function updateCustomerInfo() {
    const customerId = $('#customer_id').val();
    if (customerId) {
        const customer = customers.find(c => c.id == customerId);
        if (customer) {
            $('#display-name').text(customer.first_name + ' ' + customer.last_name);
            $('#display-address').text(customer.address);
            $('#display-phone').text(customer.phone);
            $('#display-balance').text(customer.balance);
            $('#customer-info').show();

            // Update balances
            calculateTotals();
        }
    } else {
        $('#customer-info').hide();
    }
}

// Find Order ID
function findOrderID() {
    const orderId = $('#order_id').val();
    if (!orderId) {
        Swal.fire('Please enter an Order ID', '', 'warning');
        return;
    }

    $.ajax({
        url: '/salesreturn/findorderid/' + orderId,
        method: 'GET',
        success: function(response) {
            if (response.error) {
                Swal.fire('Order not found', response.error, 'error');
                return;
            }

            // Load order items to cart
            location.reload(); // Reload to update cart with order items
        },
        error: function(xhr) {
            Swal.fire('Error!', 'Failed to find order', 'error');
        }
    });
}

// Barcode scanning
function scanBarcode(event) {
    event.preventDefault();
    const barcode = $('#barcode').val();
    const customerId = $('#customer_id').val();

    if (!barcode) {
        alert('Please enter a barcode');
        return;
    }

    if (!customerId) {
        Swal.fire('Please select a customer', '', 'warning');
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
    const customerId = $('#customer_id').val();

    if (!customerId) {
        Swal.fire('Please select a customer', '', 'warning');
        return false;
    }

    // Highlight product
    $('.product-item').removeClass('highlight');
    $(`#product-${barcode}`).addClass('highlight');

    $.ajax({
        url: '/salesreturn-cart',
        method: 'POST',
        data: {
            barcode: barcode,
            customer_id: customerId,
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
        url: '/salesreturn-cart/change-qty',
        method: 'POST',
        data: {
            product_id: productId,
            qnty: quantity,
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

// Update sell price
function updateSellPrice(productId, sellPrice) {
    if (!sellPrice || sellPrice <= 0) return;

    $.ajax({
        url: '/salesreturn/changeqnty',
        method: 'POST',
        data: {
            product_id: productId,
            sell_price: sellPrice,
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
        url: '/salesreturn-cart/delete',
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
        url: '/salesreturn-cart/empty',
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
    const customerId = $('#customer_id').val();

    let newBalance = currentNewBalance;
    let grTotal = currentGrTotal;

    if (customerId) {
        const customer = customers.find(c => c.id == customerId);
        if (customer) {
            newBalance = parseFloat(customer.balance) - (currentSubTotal - discountAmount);
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
    const returnAmount = parseFloat($('#return_amount').val()) || 0;
    const lastBalance = currentNewBalance + returnAmount;

    $('#last_balance').val(numberFormat(lastBalance));
    currentLastBalance = lastBalance;
    currentReturnAmount = returnAmount;
}

// Submit sales return
function submitSalesReturn() {
    const customerId = $('#customer_id').val();

    if (!customerId) {
        Swal.fire('Please select a customer', '', 'warning');
        return false;
    }

    Swal.fire({
        title: 'Process Sales Return',
        input: "text",
        inputValue: currentReturnAmount,
        showCancelButton: true,
        confirmButtonText: "Process Return",
        cancelButtonText: "Cancel",
        showLoaderOnConfirm: true,
        preConfirm: (amount) => {
            return $.ajax({
                url: '/salesreturn/finalsave',
                method: 'POST',
                data: {
                    customer_id: customerId,
                    sub_total: currentSubTotal,
                    discount_amount: currentDiscount,
                    gr_total: currentGrTotal,
                    return_amount: amount,
                    amount: amount,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire("Success", "Sales return has been processed!", "success");
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
