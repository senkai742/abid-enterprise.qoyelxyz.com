@extends('admin.layouts.admin')

@section('title', __('POS CART'))

@section('content')
<style>
    #cart-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    #cart-table thead,
    #cart-table tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }
    
    #cart-table tbody {
        display: block;
        max-height: 370px;
        overflow-y: auto;
        background:#eee;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <!-- Products Section (Left) -->
        <div class="col-md-5 col-lg-5">
            <div class="card">
                
                <div class="card-body">
                    <!-- Barcode Scanner -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <form id="barcode-form">
                                <div class="input-group">
                                    <input type="text" id="barcode-input" class="form-control" placeholder="Scan barcode or enter manually">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">Add</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                     
                        <div class="col-6">
                            <input type="text" id="product-search" class="form-control" placeholder="Search products...">
                        </div>
                    </div>

                    <!-- Products Grid -->
                    <div class="order-product" style="height:400px;overflow-y:auto">
                        @foreach($products as $product)
                        <div class="item product-div" data-barcode="{{ $product->barcode }}" data-product-id="{{ $product->id }}" data-stock="{{ $product->available_stock }}" style="width: 90%; overflow: hidden; height: 40px; cursor: pointer;display:inline-flex" title="{{ $product->name }}">
                            @if($product->image)
                                <img src="{{$product->image? asset('public/' . $product->image):'/images/img-placeholder.jpg' }}" onerror="this.onerror=null; this.src='{{ asset('images/img-placeholder.jpg') }}';" style="width: 40px; height: 40px; object-fit: cover;">
                            @else
                                <img src="{{ asset('images/img-placeholder.jpg') }}" alt="" style="width: 40px; height:40px; object-fit: cover;">
                            @endif
                            <h5 style="{{ $product->available_stock <= 5 ? 'color: red;' : '' }};margin-top:6px;margin-left:5px;font-size:17px">
                                {{ $product->name }}({{ $product->available_stock }})
                            </h5>
                        </div>
                        @endforeach
                    </div>
                </div>
                
            </div>
            
            
             <div class="row mt-3">
                 
                   <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <label class="mb-0 me-3" style="min-width:80px;">Total Bal.</label>
                                <input type="text" id="total-balance" class="form-control" readonly>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <label class="mb-0 me-3" style="min-width:80px;">Paid Amount</label>
                                <input type="number" id="paid-amount" class="form-control" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6 mt-2">
                            <div class="d-flex align-items-center">
                                <label class="mb-0 me-3" style="min-width:80px;">Last Balance</label>
                                <input type="text" id="last-balance" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    
        </div>

        <!-- Cart Section (Right) -->
        <div class="col-md-7 col-lg-7">
            <div class="card">
                 
                <div class="card-body">
                    <!-- Customer Selection -->
                    <div class="row mb-2">
                        <div class="col-md-5">
                            <input type="text" id="customer-input" class="form-control" placeholder="Enter customer ID or name" />
                        </div>
                        <div class="col-md-7">
                            <select id="customer-select" class="form-control">
                                <option value="">Select a customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}"
                                            data-balance="{{ $customer->balance }}"
                                            data-name="{{ $customer->first_name }} {{ $customer->last_name }}"
                                            data-address="{{ $customer->address }}"
                                            data-phone="{{ $customer->phone }}" {{($customer->first_name=="Walking")?"selected":""}}>
                                        {{ $customer->first_name }} {{ $customer->last_name }} - {{ $customer->address }}
                                    </option>
                                @endforeach
                                <option>+ Add Customer</option>
                            </select>
                        </div>
                    </div>

                    <!-- Customer Info Display -->
                    <div class="row mb-3" id="customer-info" style="display: none;">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <strong>Customer:</strong> <span id="customer-name"></span> |
                                <strong>Balance:</strong> <span id="customer-balance"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Items Table -->
                    <div class="table-responsive">
                        <table class="table table-striped" id="cart-table">
                            <thead>
                                <tr>
                                    <th style="width:180px">Product</th>
                                    <th>_Qnty_</th>
                                    <th class="">Price</th>
                                    <th class="">Dis.</th>
                                    <th class="text">Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="cart-items">
                                
                                @foreach($cart as $item)
                                <tr data-product-id="{{ $item->id }}">
                                    <td style="width:185px">{{ $item->name }}</td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm qty-input"
                                               value="{{ $item->pivot->quantity }}"
                                               data-product-id="{{ $item->id }}"
                                               min="1">
                                    </td>
                                    <td class="text-right item-price">
                                        <!--{{ config('app.currency_symbol', '$') }} --> 
                                        {{ number_format($item->sell_price, 2) }}
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm product-discount-input"
                                               value="0"
                                               data-product-id="{{ $item->id }}"
                                               min="0" step="0.01">
                                    </td>
                                    <td class="text-right">
                                        <!--{{ config('app.currency_symbol', '$') }}-->
                                        <span class="item-total">{{ number_format($item->sell_price * $item->pivot->quantity, 2) }}</span>
                                    </td>
                                    <td>
                                        <button class=" btn-sm delete-item"
                                                data-product-id="{{ $item->id }}">
                                            <i class="fas fa-remove"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals Section -->
                    <div class="row mt-0">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <label class="mb-0 me-3" style="min-width:80px;">Sub Total</label>
                                <input type="text" id="sub-total" class="form-control" readonly style="font-size:18px">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <label class="mb-0 me-3" style="min-width:70px;">Discount</label>
                                <input type="number" id="discount-amount" class="form-control" step="0.01" min="0" style="font-size:18px">
                            </div>
                        </div>
                        
                         <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <label for="grand-total" class="mb-0 me-3" style="min-width:80px;">
                                    Grand Total
                                </label>
                                <input type="text" id="grand-total" class="form-control" readonly style="font-size:18px">
                            </div>
                        </div>
                        
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <button id="empty-cart" class="btn btn-danger btn-block">
                                Cancel
                            </button>
                        </div>
                        
                         <div class="col-md-4">
                            <a href="#" id="print-invoice" class="btn btn-success btn-block" style="display: none;" target="_blank">
                                🖨️ Print Invoice
                            </a>
                        </div>
                        
                        <div class="col-md-4">
                            <button id="checkout" class="btn btn-primary btn-block">
                                Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    let selectedCustomer = null;
    let selectedBranch = {{ $branch_id ?: '' }};
    let cartItems = @json($cart);
    let discountAmount = 0;
    let paidAmount = 0;

    // Individual feature flags
    let features = {
        barcode_scanner: '{{ $settings->where("key","feature_barcode_scanner")->first()?->value ?? "0" }}' === '1',
        salesman_selection: '{{ $settings->where("key","feature_salesman_selection")->first()?->value ?? "0" }}' === '1',
        installment_plans: '{{ $settings->where("key","feature_installment_plans")->first()?->value ?? "0" }}' === '1'
    };

    // Initialize customer from dropdown on page load (e.g. if Walking Customer is pre-selected)
    const initialCustomerSelect = document.getElementById('customer-select');
    if (initialCustomerSelect && initialCustomerSelect.value) {
        const initOption = initialCustomerSelect.options[initialCustomerSelect.selectedIndex];
        if (initOption && initOption.value) {
            selectedCustomer = {
                id: initOption.value,
                name: initOption.getAttribute('data-name') || initOption.text,
                address: initOption.getAttribute('data-address') || '',
                phone: initOption.getAttribute('data-phone') || '',
                balance: parseFloat(initOption.getAttribute('data-balance')) || 0
            };
            document.getElementById('customer-name').textContent = selectedCustomer.name;
            document.getElementById('customer-balance').textContent = selectedCustomer.balance.toFixed(2) + ' BDT';
            document.getElementById('customer-info').style.display = 'block';
        }
    }

    // Check if any product has discount
    function hasProductDiscounts() {
        const discountInputs = document.querySelectorAll('.product-discount-input');
        return Array.from(discountInputs).some(input => parseFloat(input.value) > 0);
    }

    // Update global discount field state
    function updateGlobalDiscountState() {
        const globalDiscountField = document.getElementById('discount-amount');
        const hasProductDiscountsFlag = hasProductDiscounts();

        globalDiscountField.disabled = hasProductDiscountsFlag;
        if (hasProductDiscountsFlag) {
            globalDiscountField.value = '0';
            discountAmount = 0;
        }
    }

    // Update calculations
    function updateCalculations() {
        let subTotal = 0;
        let totalProductDiscounts = 0;

        console.log('Updating calculations for cart items:', cartItems);

        // Calculate subtotal and product discounts based on current DOM values
        const cartRows = document.querySelectorAll('#cart-items tr[data-product-id]');

        cartRows.forEach(row => {
            const productId = row.getAttribute('data-product-id');
            const qtyInput = row.querySelector('.qty-input');
            const discountInput = row.querySelector('.product-discount-input');
            const itemTotalElement = row.querySelector('.item-total');

            if (qtyInput && discountInput && itemTotalElement) {
                const quantity = parseInt(qtyInput.value) || 0;
                const discount = parseFloat(discountInput.value) || 0;

                // Get the sell price from the cartItems array or from DOM
                const cartItem = cartItems.find(item => item.id == productId);
                const sellPrice = cartItem ? parseFloat(cartItem.sell_price) || 0 : 0;

                const itemSubtotal = sellPrice * quantity;
                const maxDiscount = Math.min(discount, itemSubtotal);

                // Update discount input if it exceeds maximum
                if (discount > itemSubtotal) {
                    discountInput.value = maxDiscount.toFixed(2);
                }

                const itemTotal = itemSubtotal - maxDiscount;
                subTotal += itemTotal;
                totalProductDiscounts += maxDiscount;

                // Update item total display
                itemTotalElement.textContent = itemTotal.toFixed(2);

                console.log(`Item ${productId}: qty=${quantity}, price=${sellPrice}, subtotal=${itemSubtotal}, discount=${maxDiscount}, total=${itemTotal}`);
            }
        });

        console.log('Subtotal calculated:', subTotal);

        // Apply global discount only if no product discounts
        const hasProductDiscountsFlag = hasProductDiscounts();
        const effectiveGlobalDiscount = hasProductDiscountsFlag ? 0 : discountAmount;
        const grandTotal = subTotal - effectiveGlobalDiscount;

        let totalBalance = selectedCustomer ? selectedCustomer.balance + grandTotal : 0;
        const lastBalance = totalBalance - paidAmount;

        document.getElementById('sub-total').value = subTotal.toFixed(2);
        document.getElementById('grand-total').value = grandTotal.toFixed(2);
        document.getElementById('total-balance').value = totalBalance.toFixed(2);
        document.getElementById('last-balance').value = lastBalance.toFixed(2);

        console.log('Final calculations - Subtotal:', subTotal, 'Grand Total:', grandTotal, 'Total Balance:', totalBalance);

        // Update global discount state
        updateGlobalDiscountState();
    }

    // Customer selection from dropdown
    document.getElementById('customer-select').addEventListener('change', function() {
        setCustomerFromSelect(this.value);
    });

    // Customer input (for manual entry)
    document.getElementById('customer-input').addEventListener('change', function() {
        const customerId = this.value;
        if (customerId) {
            // Try to find customer by ID
            const customers = @json($customers);
            const customer = customers.find(c => c.id == customerId);
            if (customer) {
                setCustomerFromData(customer);
            } else {
                alert('Customer not found');
            }
        } else {
            selectedCustomer = null;
            document.getElementById('customer-info').style.display = 'none';
            updateCalculations();
        }
    });

    function setCustomerFromSelect(customerId) {
        if (customerId) {
            const option = document.querySelector(`#customer-select option[value="${customerId}"]`);
            selectedCustomer = {
                id: customerId,
                name: option.getAttribute('data-name'),
                address: option.getAttribute('data-address'),
                phone: option.getAttribute('data-phone'),
                balance: parseFloat(option.getAttribute('data-balance')) || 0
            };

            document.getElementById('customer-name').textContent = selectedCustomer.name;
            document.getElementById('customer-balance').textContent = selectedCustomer.balance.toFixed(2) + ' BDT';
            document.getElementById('customer-info').style.display = 'block';
        } else {
            selectedCustomer = null;
            document.getElementById('customer-info').style.display = 'none';
        }
        updateCalculations();
    }

    function setCustomerFromData(customer) {
        selectedCustomer = {
            id: customer.id,
            name: `${customer.first_name} ${customer.last_name}`,
            address: customer.address,
            phone: customer.phone,
            balance: parseFloat(customer.balance) || 0
        };

        document.getElementById('customer-name').textContent = selectedCustomer.name;
        document.getElementById('customer-balance').textContent = selectedCustomer.balance.toFixed(2) + ' BDT';
        document.getElementById('customer-info').style.display = 'block';
        updateCalculations();
    }

    // Quantity change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('qty-input')) {
            const productId = e.target.getAttribute('data-product-id');
            const quantity = parseInt(e.target.value);

            if (!selectedCustomer || !selectedBranch) {
                alert('Please select customer and branch first');
                return;
            }

            fetch('/admin/cart/change-qty', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity,
                    customer_id: selectedCustomer.id,
                    branch_id: selectedBranch
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart item quantity
                    const item = cartItems.find(item => item.id == productId);
                    if (item) {
                        item.pivot.quantity = quantity;
                        updateCalculations();
                    }
                } else {
                    alert('Error updating quantity');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating quantity');
            });
        }
    });

    // Delete item
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-item') || e.target.closest('.delete-item')) {
            const button = e.target.classList.contains('delete-item') ? e.target : e.target.closest('.delete-item');
            const productId = button.getAttribute('data-product-id');

            fetch('/admin/cart/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    product_id: productId,
                    branch_id: selectedBranch,
                    _method: 'DELETE'
                })
            })
            .then(() => {
                // Remove from cart items
                cartItems = cartItems.filter(item => item.id != productId);
                button.closest('tr').remove();
                updateCalculations();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting item');
            });
        }
    });

    // Empty cart
    document.getElementById('empty-cart').addEventListener('click', function() {
        fetch('/admin/cart/empty', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                _method: 'DELETE'
            })
        })
        .then(() => {
            cartItems = [];
            document.getElementById('cart-items').innerHTML = '';
            updateCalculations();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error emptying cart');
        });
    });

    // Barcode scanning
    document.getElementById('barcode-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const barcode = document.getElementById('barcode-input').value;

        if (!barcode) {
            alert('Please enter a barcode');
            return;
        }

        if (!selectedCustomer || !selectedBranch) {
            alert('Please select customer and branch first');
            return;
        }

        fetch('/admin/cart-store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                barcode: barcode,
                customer_id: selectedCustomer.id,
                branch_id: selectedBranch
            })
        })
        .then(response => {
            if (response.ok) {
                document.getElementById('barcode-input').value = '';
                // Update stock in real-time
                const productItem = document.querySelector(`.item.product-div[data-barcode="${barcode}"]`);
                if (productItem) {
                    const currentStock = parseInt(productItem.getAttribute('data-stock')) || 0;
                    if (currentStock > 0) {
                        const newStock = currentStock - 1;
                        productItem.setAttribute('data-stock', newStock);
                        const stockText = productItem.querySelector('h5');
                        const productName = stockText.textContent.split('(')[0];
                        stockText.textContent = `${productName}(${newStock})`;

                        // Update color based on stock level
                        if (newStock <= 5) {
                            stockText.style.color = 'red';
                        }
                    }
                }
                // Reload cart items
                loadCartItems();
            } else {
                return response.json().then(data => {
                    alert(data.message || 'Error adding product');
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding product');
        });
    });

    // Product click
    document.addEventListener('click', function(e) {
        if (e.target.closest('.item.product-div')) {
            const item = e.target.closest('.item.product-div');
            const barcode = item.getAttribute('data-barcode');
            const productId = item.getAttribute('data-product-id');

            console.log('Product clicked:', { barcode, productId, selectedCustomer, selectedBranch });

            if (!selectedCustomer || !selectedBranch) {
                alert('Please select customer and branch first');
                return;
            }

            // Highlight selected product
            document.querySelectorAll('.item.product-div').forEach(el => {
                el.style.backgroundColor = '#fff';
            });
            item.style.border = "2px solid #fcc";
            item.style.backgroundColor = "#fee";

            console.log('Sending cart request with:', {
                barcode: barcode,
                customer_id: selectedCustomer.id,
                branch_id: selectedBranch
            });

            fetch('/admin/cart-store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    barcode: barcode,
                    customer_id: selectedCustomer.id,
                    branch_id: selectedBranch
                })
            })
            .then(async response => {
                console.log('=== PRODUCT ADD REQUEST ===');
                console.log('Cart response status:', response.status);
                console.log('Response ok:', response);

                if (response.ok) {
                    console.log('✅ Product added successfully to server');

                    // Update stock in real-time
                    const currentStock = parseInt(item.getAttribute('data-stock')) || 0;
                    if (currentStock > 0) {
                        const newStock = currentStock - 1;
                        item.setAttribute('data-stock', newStock);
                        const stockText = item.querySelector('h5');
                        const productName = stockText.textContent.split('(')[0];
                        stockText.textContent = `${productName}(${newStock})`;

                        // Update color based on stock level
                        if (newStock <= 5) {
                            stockText.style.color = 'red';
                        }
                    }

                    // Add item to cart locally instead of reloading
                    addProductToCartLocally(productId, barcode);
                } else {
                    console.log('❌ Error response received');
                    console.log('Response status:', response.status);
                    console.log('Response statusText:', response.statusText);

                    try {
                        const contentType = response.headers.get('content-type');
                        console.log('Content-Type:', contentType);

                        let errorMessage = `HTTP ${response.status}: ${response.statusText}`;

                        if (contentType && contentType.includes('application/json')) {
                            console.log('Trying to parse as JSON...');
                            const data = await response.json();
                            console.log('✅ Parsed JSON error response:', data);
                            errorMessage = data.message || data.error || errorMessage;
                        } else {
                            console.log('Trying to parse as text...');
                            const text = await response.text();
                            console.log('✅ Raw text response (first 200 chars):', text.substring(0, 200));
                            if (text.length > 0) {
                                errorMessage = `Server Error: ${text.substring(0, 100)}${text.length > 100 ? '...' : ''}`;
                            }
                        }

                        console.log('🔔 Final error message:', errorMessage);
                        alert(`Error adding product 01: ${errorMessage}`);

                    } catch (parseError) {
                        console.error('❌ Error parsing response:', parseError);
                        alert(`Error adding product 02: HTTP ${response.status} - ${response.statusText}`);
                    }
                }
            })
            .catch(networkError => {
                console.error('🚨 Network error during product add:', networkError);
                alert(`Network error: ${networkError.message}`);
            });
        }
    });

    // Add product to cart locally (without server reload)
    function addProductToCartLocally(productId, barcode) {
        console.log('Adding product to cart locally:', { productId, barcode });

        // Check if product is already in cart
        const existingItem = cartItems.find(item => item.id == productId);

        if (existingItem) {
            // Update quantity for existing item
            existingItem.pivot.quantity += 1;
            console.log('Updated existing item quantity to:', existingItem.pivot.quantity);
        } else {
            // Add new item to cart
            // Find product details from the products data
            const products = @json($products);
            const product = products.find(p => p.id == productId);

            if (product) {
                const newItem = {
                    id: product.id,
                    name: product.name,
                    sell_price: product.sell_price,
                    barcode: product.barcode,
                    pivot: {
                        quantity: 1,
                        customer_id: selectedCustomer.id,
                        branch_id: selectedBranch,
                        user_id: 1, // Assuming current user
                        product_id: product.id
                    }
                };
                cartItems.push(newItem);
                console.log('Added new item to cart:', newItem);
            } else {
                console.error('Product not found in products list');
                // Fallback to reloading cart
                loadCartItems();
                return;
            }
        }

        // Update cart table
        updateCartTable();

        // Update calculations
        updateCalculations();

        console.log('Cart updated locally, new cart items:', cartItems);
    }

    // Product search (trigger on Enter key)
    document.getElementById('product-search').addEventListener('keydown', function(e) {
        if (e.keyCode === 13) { // Enter key
            const searchTerm = this.value.toLowerCase();
            const productItems = document.querySelectorAll('.item.product-div');

            productItems.forEach(item => {
                const productName = item.querySelector('h5').textContent.toLowerCase();
                if (productName.includes(searchTerm)) {
                    item.style.display = 'inline-block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    });

    // Discount change
    document.getElementById('discount-amount').addEventListener('input', function() {
        discountAmount = parseFloat(this.value) || 0;
        updateCalculations();
    });

    // Paid amount change
    document.getElementById('paid-amount').addEventListener('input', function() {
        paidAmount = parseFloat(this.value) || 0;
        updateCalculations();
    });

    // Checkout with SweetAlert
    document.getElementById('checkout').addEventListener('click', function() {
        if (!selectedCustomer || !selectedCustomer.id) {
            Swal.fire({
                title: 'Customer Required',
                text: 'Please select a customer before checking out.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            // Highlight the customer dropdown
            document.getElementById('customer-select').style.border = '2px solid red';
            setTimeout(() => {
                document.getElementById('customer-select').style.border = '';
            }, 3000);
            return;
        }

        if (cartItems.length === 0) {
            Swal.fire({
                title: 'Cart is Empty',
                text: 'Please add items to the cart before checking out.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        const paidAmountValue = document.getElementById('paid-amount').value;

        Swal.fire({
            title: 'Save POS',
            input: 'text',
            inputValue: paidAmountValue,
            showCancelButton: true,
            confirmButtonText: 'Save Sale',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: (amount) => {
                // Get selected salesman and installment values (only if features are enabled)
                const salesmanId = features.salesman_selection ?
                    (document.getElementById('salesman-select')?.value || null) : null;
                const installmentPlan = features.installment_plans ?
                    (document.getElementById('installment-select')?.value || null) : null;

                let finalDiscountAmount = discountAmount;
                if (hasProductDiscounts()) {
                    let totalProductDiscounts = 0;
                    document.querySelectorAll('.product-discount-input').forEach(input => {
                        totalProductDiscounts += parseFloat(input.value) || 0;
                    });
                    finalDiscountAmount = totalProductDiscounts;
                }

                console.log('Checkout data:', {
                    customer_id: selectedCustomer.id,
                    branch_id: selectedBranch,
                    amount: amount,
                    discount_amount: finalDiscountAmount,
                    salesman_id: salesmanId,
                    installment_plan: installmentPlan
                });

                return fetch('/admin/sales', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        customer_id: selectedCustomer.id,
                        branch_id: selectedBranch,
                        amount: amount,
                        discount_amount: finalDiscountAmount,
                        salesman_id: salesmanId?salesmanId:0,
                        installment_plan: installmentPlan
                    })
                })
                .then(response => {
                    console.log('Sale response status:', response.status);
                    console.log('Sale response ok:', response.ok);

                    if (response.ok) {
                        return response.json();
                    } else {
                        // Try to get error details
                        return response.text().then(text => {
                            console.error('Sale error response:', text);
                            try {
                                const jsonError = JSON.parse(text);
                                throw new Error(jsonError.message || jsonError.error || text);
                            } catch (e) {
                                throw new Error(text || 'Unknown error occurred');
                            }
                        });
                    }
                })
                .then(data => {
                    console.log('Sale success data:', data);

                    // Log debug information from server
                    if (data.debug) {
                        console.log('🔧 SERVER DEBUG INFO:');
                        console.log('📊 Table name:', data.debug.table_name);
                        console.log('📝 Request data:', data.debug.request_data);
                        console.log('🎯 Installment data:', data.debug.request_data.installmentData);
                    }

                    if (data.sale && data.sale.id) {
                        const printUrl = `/admin/sales/print/${data.sale.id}`;
                        document.getElementById('print-invoice').href = printUrl;
                        document.getElementById('print-invoice').style.display = 'block';
                        return data.sale;
                    } else {
                        Swal.showValidationMessage(data.message || 'Error completing sale');
                    }
                })
                .catch(error => {
                    console.error('Sale error:', error);
                    console.error('Error message:', error.message);
                    console.error('Error stack:', error.stack);
                    Swal.showValidationMessage(`Error: ${error.message}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.value) {
                Swal.fire('Success', 'Order has been saved!', 'success');
                // Reload page
                setTimeout(() => location.reload(), 1000);
            }
        });
    });

    // Load cart items via AJAX
    function loadCartItems() {
        console.log('Loading cart items...');
        fetch('/admin/cart', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Cart GET response status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Raw cart data received:', data);
            console.log('Cart data type:', typeof data);
            console.log('Is array:', Array.isArray(data));

            // Preserve existing discount values when reloading cart
            const existingDiscounts = {};
            document.querySelectorAll('.product-discount-input').forEach(input => {
                const productId = input.getAttribute('data-product-id');
                existingDiscounts[productId] = input.value;
            });

            cartItems = Array.isArray(data) ? data : [];
            console.log('Cart items set to:', cartItems);

            updateCartTable();

            // Restore discount values
            cartItems.forEach(item => {
                if (existingDiscounts[item.id]) {
                    const discountInput = document.querySelector(`.product-discount-input[data-product-id="${item.id}"]`);
                    if (discountInput) {
                        discountInput.value = existingDiscounts[item.id];
                        console.log(`Restored discount for item ${item.id}: ${existingDiscounts[item.id]}`);
                    }
                }
            });

            updateCalculations();
            console.log('Cart loading completed');
        })
        .catch(error => {
            console.error('Error loading cart:', error);
        });
    }

    // Update cart table with new items
    function updateCartTable() {
        const cartTableBody = document.getElementById('cart-items');
        cartTableBody.innerHTML = '';

        cartItems.forEach(item => {
            const row = document.createElement('tr');
            row.setAttribute('data-product-id', item.id);
            row.innerHTML = `
                <td>${item.name}</td>
                <td>
                    <input type="number" class="form-control form-control-sm qty-input"
                           value="${item.pivot.quantity}"
                           data-product-id="${item.id}"
                           min="1">
                </td>
                <td class="text-right item-price">
                    ${(parseFloat(item.sell_price) || 0).toFixed(2)}
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm product-discount-input"
                           value="0"
                           data-product-id="${item.id}"
                           min="0" step="0.01">
                </td>
                <td class="text-right">
                    <span class="item-total">${((parseFloat(item.sell_price) || 0) * item.pivot.quantity).toFixed(2)}</span>
                </td>
                <td>
                    <button class="btn btn-danger btn-sm delete-item"
                            data-product-id="${item.id}">
                        <i class="fas fa-remove"></i>
                    </button>
                </td>
            `;
            cartTableBody.appendChild(row);
        });

        // Re-attach event listeners for dynamically created elements
        attachDynamicEventListeners();
    }

    // Attach event listeners for dynamically created elements
    function attachDynamicEventListeners() {
        // Product discount inputs
        document.querySelectorAll('.product-discount-input').forEach(input => {
            input.addEventListener('input', function() {
                updateCalculations();
            });
        });

        // Quantity inputs
        document.querySelectorAll('.qty-input').forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.getAttribute('data-product-id');
                const quantity = parseInt(this.value);

                if (!selectedCustomer || !selectedBranch) {
                    alert('Please select customer and branch first');
                    return;
                }

                fetch('/admin/cart/change-qty', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity,
                        customer_id: selectedCustomer.id,
                        branch_id: selectedBranch
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update cart item quantity
                        const item = cartItems.find(item => item.id == productId);
                        if (item) {
                            item.pivot.quantity = quantity;
                            updateCalculations();
                        }
                    } else {
                        alert('Error updating quantity');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating quantity');
                });
            });
        });

        // Delete buttons
        document.querySelectorAll('.delete-item').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');

                fetch('/admin/cart/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        branch_id: selectedBranch,
                        _method: 'DELETE'
                    })
                })
                .then(() => {
                    // Remove from cart items
                    cartItems = cartItems.filter(item => item.id != productId);
                    this.closest('tr').remove();
                    updateCalculations();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting item');
                });
            });
        });
    }

    // Configure UI based on individual features
    function configureFeaturesUI() {
        console.log('🔧 configureFeaturesUI() called');
        console.log('📊 Features from config:', features);

        // Get references to sections
        const barcodeSection = document.getElementById('barcode-form');
        const salesmanSection = document.getElementById('salesman-section');
        const installmentSection = document.getElementById('installment-section');

        console.log('🎯 DOM elements found:', {
            barcodeSection: !!barcodeSection,
            salesmanSection: !!salesmanSection,
            installmentSection: !!installmentSection
        });

        // Create salesman section if it doesn't exist and feature is enabled
        if (!salesmanSection && features.salesman_selection) {
            const salesmanDiv = document.createElement('div');
            salesmanDiv.id = 'salesman-section';
            salesmanDiv.className = 'row mb-3';
            salesmanDiv.innerHTML = `
                <div class="col-12">
                    <div class="form-group">
                        <label>Salesman</label>
                        <select id="salesman-select" class="form-control">
                            <option value="">Select Salesman</option>
                            <!-- Salesman options will be loaded dynamically -->
                        </select>
                    </div>
                </div>
            `;
            // Insert before product search
            const productSearchRow = document.getElementById('product-search').parentNode.parentNode;
            productSearchRow.parentNode.insertBefore(salesmanDiv, productSearchRow);
        }

        // Create installment section if it doesn't exist and feature is enabled
        if (!installmentSection && features.installment_plans) {
            const installmentDiv = document.createElement('div');
            installmentDiv.id = 'installment-section';
            installmentDiv.className = 'row mb-3';
            installmentDiv.innerHTML = `
                <div class="col-12">
                    <div class="form-group">
                        <label>Installment Plan</label>
                        <select id="installment-select" class="form-control">
                            <option value="">Select Installment Plan</option>
                            <option value="3">3 Months</option>
                            <option value="6">6 Months</option>
                            <option value="12">12 Months</option>
                        </select>
                    </div>
                </div>
            `;
            // Insert before product search
            const productSearchRow = document.getElementById('product-search').parentNode.parentNode;
            productSearchRow.parentNode.insertBefore(installmentDiv, productSearchRow);
        }

        // Configure visibility based on individual features
        console.log('Configuring UI based on individual features...');

        // Barcode scanner
        if (barcodeSection) {
            barcodeSection.style.display = features.barcode_scanner ? 'block' : 'none';
            console.log(`  - Barcode scanner: ${features.barcode_scanner ? 'VISIBLE' : 'HIDDEN'}`);
        }

        // Salesman selection
        if (salesmanSection) {
            salesmanSection.style.display = features.salesman_selection ? 'block' : 'none';
            console.log(`  - Salesman selection: ${features.salesman_selection ? 'VISIBLE' : 'HIDDEN'}`);
        }

        // Installment plans
        if (installmentSection) {
            installmentSection.style.display = features.installment_plans ? 'block' : 'none';
            console.log(`  - Installment plans: ${features.installment_plans ? 'VISIBLE' : 'HIDDEN'}`);
        }

        // Load salesman options if salesman section is visible
        const salesmanSectionVisible = document.getElementById('salesman-section') &&
                                      document.getElementById('salesman-section').style.display !== 'none';
        if (salesmanSectionVisible) {
            loadSalesmen();
        }

        // Auto-focus appropriate field
        setTimeout(() => autoFocusBasedOnFeatures(), 100);
    }

    // Auto-focus appropriate input field based on features
    function autoFocusBasedOnFeatures() {
        console.log('Setting auto-focus based on features:', features);

        if (features.barcode_scanner) {
            // Focus barcode input if barcode is available
            const barcodeInput = document.getElementById('barcode-input');
            if (barcodeInput && barcodeInput.style.display !== 'none') {
                console.log('Auto-focusing barcode input');
                barcodeInput.focus();
                return;
            }
        }

        // Otherwise, focus the product search field
        const productSearch = document.getElementById('product-search');
        if (productSearch) {
            console.log('Auto-focusing product search');
            productSearch.focus();
        }
    }

    // Load salesmen from the data passed by controller (no AJAX needed)
    function loadSalesmen() {
        console.log('🌟 loadSalesmen() function called');

        // Get salesmen data from the view (passed by controller)
        const salesmenData = @json($salesmen);
        console.log('📊 Salesmen data from controller:', salesmenData);
        console.log('📊 Salesmen data type:', typeof salesmenData);
        console.log('📊 Is array:', Array.isArray(salesmenData));
        console.log('📊 Salesmen length:', salesmenData ? salesmenData.length : 'N/A');

        const salesmanSelect = document.getElementById('salesman-select');
        if (salesmanSelect) {
            console.log('🎯 Salesman select element found');
            salesmanSelect.innerHTML = '<option value="">Select Salesman</option>';

            if (Array.isArray(salesmenData)) {
                salesmenData.forEach((salesman, index) => {
                    console.log(`👤 Processing salesman ${index + 1}:`, salesman);
                    const option = document.createElement('option');
                    option.value = salesman.id;
                    option.textContent = `${salesman.first_name} ${salesman.last_name}`;
                    salesmanSelect.appendChild(option);
                });
                console.log(`✅ Successfully loaded ${salesmenData.length} salesmen into dropdown`);
            } else {
                console.error('❌ Salesmen data is not an array:', salesmenData);
            }
        } else {
            console.error('❌ Salesman select element not found');
        }
    }

    // Initial setup - attach event listeners for initially rendered elements
    attachDynamicEventListeners();

    // Configure UI based on individual features
    configureFeaturesUI();

    // Initial calculations
    updateCalculations();
});

// const customerSelect = document.getElementById('customer-select');
// customerSelect.dispatchEvent(new Event('change'));

 
 


            
    
    
</script>
@endsection
