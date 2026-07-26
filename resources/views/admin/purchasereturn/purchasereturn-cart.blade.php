@extends('admin.layouts.admin')

@section('title', __('Purchase Return'))
@section('content-header', __('Purchase Return'))

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
.product-item {
    cursor: pointer;
    padding: 10px;
    border: 1px solid #ddd;
    margin-bottom: 5px;
    border-radius: 4px;
}
.product-item:hover {
    background-color: #f8f9fa;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-6 col-lg-6">
        <!-- Purchase Selection -->
        <div class="row mb-2">
            <div class="col-md-3">
                <b>Purchase ID</b>
                <input type="text"
                       id="purchase_id"
                       name="purchase_id"
                       placeholder="Purchase ID"
                       class="form-control">
                <button type="button" class="btn btn-info btn-sm mt-1" onclick="findPurchaseID()">
                    Find Purchase
                </button>
            </div>
            <div class="col-md-9">
                <b>Supplier Name</b>
                <select id="supplier_id" name="supplier_id" class="form-control">
                    <option value="">Select a supplier</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}"
                            data-first-name="{{ $supplier->first_name }}"
                            data-last-name="{{ $supplier->last_name }}"
                            data-address="{{ $supplier->address ?? '' }}"
                            data-phone="{{ $supplier->phone ?? '' }}">
                        {{ $supplier->first_name }} {{ $supplier->last_name }}
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
                                <th class="text-right" style="width: 20%">Purchase Rate</th>
                                <th class="text-right" style="width: 20%">Total</th>
                                <th style="width: 15%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cart-items">
                            <tr>
                                <td colspan="5" class="text-center text-muted">No items in cart</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Totals -->
        <div class="row">
            <div class="col">
                <div class="font-weight-bold">Sub. Total</div>
                {{ config('settings.currency_symbol', '৳') }} <span id="sub_total">0.00</span>
            </div>
            <div class="col">
                <div class="font-weight-bold">Return Amount</div>
                <input type="text"
                       id="return_amount"
                       placeholder="Return amount"
                       class="form-control form-sm text-right"
                       value="0"
                       onchange="calculateTotals()">
            </div>
            <div class="col text-right">
                <div class="font-weight-bold">Total</div>
                <div>{{ config('settings.currency_symbol', '৳') }} <span id="gr_total">0.00</span></div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-3">
            <div class="col">
                <button type="button"
                        class="btn btn-danger btn-block"
                        onclick="emptyCart()">
                    Cancel
                </button>
            </div>
            <div class="col">
                <button type="button"
                        class="btn btn-primary btn-block"
                        onclick="submitPurchaseReturn()">
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
                    <div class="product-item"
                         data-id="{{ $product->id }}"
                         data-name="{{ $product->name }}"
                         data-barcode="{{ $product->barcode }}"
                         data-purchase-price="{{ $product->purchase_price ?? 0 }}"
                         data-quantity="{{ $product->real_stock ?? 0 }}"
                         onclick="addToCart({{ $product->id }})">
                        <div class="d-flex justify-content-between">
                            <span>{{ $product->name }}</span>
                            <span class="text-muted">{{ config('settings.currency_symbol', '৳') }} {{ number_format($product->purchase_price ?? 0, 2) }}</span>
                        </div>
                        <small class="text-muted">Stock: {{ $product->real_stock ?? 0 }}</small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let cart = [];
let currentPurchaseId = null;
let currentSupplierId = null;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Supplier selection
    document.getElementById('supplier_id').addEventListener('change', updateSupplierInfo);
});

// Supplier selection
function updateSupplierInfo() {
    const supplierId = document.getElementById('supplier_id').value;
    const supplierInfo = document.getElementById('supplier-info');

    if (supplierId) {
        const select = document.getElementById('supplier_id');
        const option = select.options[select.selectedIndex];

        document.getElementById('display-name').textContent =
            (option.dataset.firstName || '') + ' ' + (option.dataset.lastName || '');
        document.getElementById('display-address').textContent = option.dataset.address || '';
        document.getElementById('display-phone').textContent = option.dataset.phone || '';

        supplierInfo.style.display = 'flex';
        currentSupplierId = supplierId;
    } else {
        supplierInfo.style.display = 'none';
        currentSupplierId = null;
    }
}

// Find Purchase ID
function findPurchaseID() {
    const purchaseId = document.getElementById('purchase_id').value;
    if (!purchaseId) {
        Swal.fire('Warning', 'Please enter a purchase ID', 'warning');
        return;
    }

    fetch(`/admin/purchasereturn/findpurchaseid/${purchaseId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                Swal.fire('Error!', data.error, 'error');
            } else if (data.purchase) {
                // Set supplier
                document.getElementById('supplier_id').value = data.purchase.supplier_id;
                updateSupplierInfo();

                // Store purchase ID for later use
                currentPurchaseId = data.purchase.id;

                // Load items to cart
                if (data.purchasereturn_items && data.purchasereturn_items.length > 0) {
                    cart = data.purchasereturn_items.map(item => ({
                        id: item.id,
                        product_id: item.product_id,
                        product_name: item.product ? item.product.name : 'Unknown',
                        qnty: item.qnty,
                        purchase_price: item.purchase_price,
                        total_price: item.total_price,
                        purchase_id: item.purchase_id,
                        supplier_id: item.supplier_id,
                        branch_id: item.branch_id ?? null
                    }));
                    renderCart();
                    calculateTotals();
                }
                Swal.fire('Success', 'Purchase found and loaded', 'success');
            } else {
                Swal.fire('Warning', 'No purchase found with this ID', 'warning');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error!', 'Failed to find purchase', 'error');
        });
}

// Barcode scanning
function scanBarcode(event) {
    event.preventDefault();
    const barcode = document.getElementById('barcode').value;
    if (barcode) {
        // Find product by barcode and add to cart
        const productItem = document.querySelector(`.product-item[data-barcode="${barcode}"]`);
        if (productItem) {
            const productId = parseInt(productItem.dataset.id);
            addToCart(productId);
            document.getElementById('barcode').value = '';
        } else {
            Swal.fire('Warning', 'Product not found', 'warning');
        }
    }
}

// Product search
function searchProducts(event) {
    const searchTerm = event.target.value.toLowerCase();
    const productItems = document.querySelectorAll('.product-item');

    productItems.forEach(item => {
        const productName = item.dataset.name.toLowerCase();
        if (productName.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Add to cart
function addToCart(productId) {
    if (!currentSupplierId) {
        Swal.fire('Warning', 'Please select a supplier first', 'warning');
        return;
    }

    const productItem = document.querySelector(`.product-item[data-id="${productId}"]`);
    if (!productItem) return;

    const existingItem = cart.find(item => item.product_id === productId);

    if (existingItem) {
        // Increase quantity
        const maxQty = parseInt(productItem.dataset.quantity) || 0;
        if (existingItem.qnty >= maxQty) {
            Swal.fire('Warning', 'Not enough stock', 'warning');
            return;
        }
        existingItem.qnty += 1;
        existingItem.total_price = existingItem.qnty * existingItem.purchase_price;
    } else {
        // Add new item
        cart.push({
            id: Date.now(),
            product_id: productId,
            product_name: productItem.dataset.name,
            qnty: 1,
            purchase_price: parseFloat(productItem.dataset.purchasePrice) || 0,
            total_price: parseFloat(productItem.dataset.purchasePrice) || 0
        });
    }

    renderCart();
    calculateTotals();

    // Save to server
    saveCartToServer();
}

// Remove from cart
function removeFromCart(cartItemId) {
    cart = cart.filter(item => item.id !== cartItemId);
    renderCart();
    calculateTotals();
    saveCartToServer();
}

// Update quantity
function updateQuantity(cartItemId, quantity) {
    const item = cart.find(i => i.id === cartItemId);
    if (item) {
        item.qnty = parseInt(quantity) || 0;
        item.total_price = item.qnty * (parseFloat(item.purchase_price) || 0);
        renderCart();
        calculateTotals();
        saveCartToServer();
    }
}

// Render cart
function renderCart() {
    const tbody = document.getElementById('cart-items');

    if (cart.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No items in cart</td></tr>';
        return;
    }

    tbody.innerHTML = cart.map(item => `
        <tr class="product-row" data-product-id="${item.product_id}">
            <td>${item.product_name}</td>
            <td>
                <input type="text"
                       class="form-control form-control-sm qty-input"
                       value="${item.qnty}"
                       onchange="updateQuantity(${item.id}, this.value)">
            </td>
            <td class="text-right">
                {{ config('settings.currency_symbol', '৳') }} ${(parseFloat(item.purchase_price) || 0).toFixed(2)}
            </td>
            <td class="text-right">
                {{ config('settings.currency_symbol', '৳') }} ${(parseFloat(item.total_price) || 0).toFixed(2)}
            </td>
            <td>
                <button class="btn btn-danger btn-sm" onclick="removeFromCart(${item.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Calculate totals
function calculateTotals() {
    const subTotal = cart.reduce((sum, item) => sum + (parseFloat(item.total_price) || 0), 0);
    const returnAmount = parseFloat(document.getElementById('return_amount').value) || 0;

    document.getElementById('sub_total').textContent = subTotal.toFixed(2);
    document.getElementById('gr_total').textContent = (subTotal - returnAmount).toFixed(2);
}

// Empty cart
function emptyCart() {
    if (cart.length === 0) return;

    Swal.fire({
        title: 'Are you sure?',
        text: 'This will remove all items from the cart',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, empty it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            cart = [];
            renderCart();
            calculateTotals();

            // Clear cart on server
            fetch('/admin/purchasereturn-cart/empty', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
        }
    });
}

// Save cart to server
function saveCartToServer() {
    // This would typically sync with the server
    // For now, we just keep local state
}

// Submit purchase return
function submitPurchaseReturn() {
    if (cart.length === 0) {
        Swal.fire('Warning', 'Cart is empty', 'warning');
        return;
    }

    if (!currentSupplierId) {
        Swal.fire('Warning', 'Please select a supplier', 'warning');
        return;
    }

    const returnAmount = parseFloat(document.getElementById('return_amount').value) || 0;

    Swal.fire({
        title: 'Confirm Purchase Return',
        text: `Return Amount: {{ config('settings.currency_symbol', '৳') }} ${returnAmount}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Confirm',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit to server
            const formData = new FormData();
            formData.append('supplier_id', currentSupplierId);
            formData.append('purchase_id', currentPurchaseId || '');
            formData.append('return_amount', returnAmount);
            formData.append('items', JSON.stringify(cart));

            fetch('/admin/purchasereturn-cart', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', 'Purchase return has been processed!', 'success');
                    cart = [];
                    renderCart();
                    calculateTotals();
                    window.location.href = '/admin/purchasereturn';
                } else {
                    Swal.fire('Error!', data.message || 'Failed to process return', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Failed to process return', 'error');
            });
        }
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
