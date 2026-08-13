<?php

use App\Http\Controllers\PurchaseCartController;
use App\Http\Controllers\Admin\CartController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\SalesreturnCartController;
use App\Http\Controllers\SalesreturnController;
use App\Http\Controllers\PurchasereturnController;
use App\Http\Controllers\PurchasereturnCartController;
use App\Http\Controllers\DamageController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserExpenseReportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        } else {
            return redirect('/user/dashboard');
        }
    }
    return redirect('/login');
})->name('home');

Auth::routes();

// User Routes
Route::prefix('user', 'user_guard')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [UserController::class, 'index'])->name('user.dashboard');

    // User Products
    Route::resource('products', ProductController::class)->names([
        'index' => 'user.products.index',
        'create' => 'user.products.create',
        'store' => 'user.products.store',
        'show' => 'user.products.show',
        'edit' => 'user.products.edit',
        'update' => 'user.products.update',
        'destroy' => 'user.products.destroy',
    ]);

    // User Customers
    Route::resource('customers', CustomerController::class)->names([
        'index' => 'user.customers.index',
        'create' => 'user.customers.create',
        'store' => 'user.customers.store',
        'show' => 'user.customers.show',
        'edit' => 'user.customers.edit',
        'update' => 'user.customers.update',
        'destroy' => 'user.customers.destroy',
    ]);
    Route::post('/customers/{customer}/pay', [CustomerController::class, 'pay'])->name('user.customers.pay');

    // User Suppliers
    Route::resource('suppliers', SupplierController::class)->names([
        'index' => 'user.suppliers.index',
        'create' => 'user.suppliers.create',
        'store' => 'user.suppliers.store',
        'show' => 'user.suppliers.show',
        'edit' => 'user.suppliers.edit',
        'update' => 'user.suppliers.update',
        'destroy' => 'user.suppliers.destroy',
    ]);

    // User Sales
    Route::resource('sales', SaleController::class)->names([
        'index' => 'user.sales.index',
        'create' => 'user.sales.create',
        'store' => 'user.sales.store',
        'show' => 'user.sales.show',
        'edit' => 'user.sales.edit',
        'update' => 'user.sales.update',
        'destroy' => 'user.sales.destroy',
    ]);
    Route::post('/sales/partial-payment', [SaleController::class, 'partialPayment'])->name('user.sales.partial-payment');
    Route::get('/sales/print/{id}', [SaleController::class, 'print'])->name('user.sales.print');

    // User Sales Return
    Route::resource('salesreturn', SalesreturnController::class)->names([
        'index' => 'user.salesreturn.index',
        'create' => 'user.salesreturn.create',
        'store' => 'user.salesreturn.store',
        'show' => 'user.salesreturn.show',
        'edit' => 'user.salesreturn.edit',
        'update' => 'user.salesreturn.update',
        'destroy' => 'user.salesreturn.destroy',
    ]);
    Route::get('/salesreturn-cart', [SalesreturnCartController::class, 'index'])->name('user.salesreturns.index');
    Route::post('/salesreturn-cart', [SalesreturnCartController::class, 'store'])->name('user.salesreturns.store');
    Route::post('/salesreturn-cart/change-qty', [SalesreturnCartController::class, 'changeQty']);
    Route::post('/salesreturn-cart/change-sell-price', [SalesreturnCartController::class, 'changeSellPrice']);
    Route::delete('/salesreturn-cart/delete', [SalesreturnCartController::class, 'delete']);
    Route::delete('/salesreturn-cart/empty', [SalesreturnCartController::class, 'empty']);
    Route::get('/salesreturn/findorderid/{order_id}', [SalesreturnController::class, 'findOrderID']);
    Route::post('/salesreturn/cart', [SalesreturnController::class, 'addProductToCart']);
    Route::post('/salesreturn/changeqnty', [SalesreturnController::class, 'changeQnty']);
    Route::post('/salesreturn/delete', [SalesreturnController::class, 'handleDelete']);
    Route::get('/salesreturn/details/{salesreturn_id}', [SalesreturnController::class, 'salesreturnDetails'])->name('user.salesreturn.details');
    Route::post('/salesreturn/finalsave', [SalesreturnController::class, 'finalSave']);

    // User Damage
    Route::resource('damage', DamageController::class)->names([
        'index' => 'user.damage.index',
        'create' => 'user.damage.create',
        'store' => 'user.damage.store',
        'show' => 'user.damage.show',
        'edit' => 'user.damage.edit',
        'update' => 'user.damage.update',
        'destroy' => 'user.damage.destroy',
    ]);
    Route::get('/damage-create', [DamageController::class, 'create'])->name('user.damages.create');
    Route::post('/damage-cart', [DamageController::class, 'store'])->name('user.damages.store');
    Route::get('/damage/findorderid/{order_id}', [DamageController::class, 'findOrderID']);
    Route::post('/damage/cart', [DamageController::class, 'addProductToCart']);
    Route::post('/damage/changeqnty', [DamageController::class, 'changeQnty']);
    Route::post('/damage/delete', [DamageController::class, 'handleDelete']);
    Route::get('/damage/details/{salesreturn_id}', [DamageController::class, 'damage.details']);
    Route::post('/damage/finalsave', [DamageController::class, 'finalSave']);

    // User expense routes
    Route::get('/expense/sales-details', [UserExpenseReportController::class, 'salesDetails'])->name('user.expense.sales-details');
    Route::get('/expense/expense-details', [ExpenseController::class, 'expenseDetails'])->name('user.expense.expense-details');
    Route::get('/expense/profit-details', [UserExpenseReportController::class, 'profitDetails'])->name('user.expense.profit-details');
    Route::get('/expense/cash-details', [UserExpenseReportController::class, 'cashDetails'])->name('user.expense.cash-details');
    Route::get('/expense/damage-details', [UserExpenseReportController::class, 'damageDetails'])->name('user.expense.damage-details');
    Route::get('/expense-head-create', [ExpenseController::class, 'createExpenseHead'])->name('user.expense.head.create');
    Route::post('/expense-head-store', [ExpenseController::class, 'storeExpenseHead'])->name('user.expense.head.store');
    Route::delete('/expense-head/{exp_head_id}', [ExpenseController::class, 'deleteExpenseHead'])->name('user.expense.head.delete');
    // Resource route LAST
    Route::resource('expense', ExpenseController::class)->names([
        'index' => 'user.expense.index',
        'create' => 'user.expense.create',
        'store' => 'user.expense.store',
        'show' => 'user.expense.show',
        'edit' => 'user.expense.edit',
        'update' => 'user.expense.update',
        'destroy' => 'user.expense.destroy',
    ]);

    // User Cart (POS)
    Route::get('/cart', [App\Http\Controllers\UserCartController::class, 'index'])->name('user.cart.index');
    Route::get('/user-cart', [App\Http\Controllers\UserCartController::class, 'getCart'])->name('user.cart.get');
    Route::post('/user-cart', [App\Http\Controllers\UserCartController::class, 'store'])->name('user.cart.store');
    Route::post('/user-cart/change-qty', [App\Http\Controllers\UserCartController::class, 'changeQty'])->name('user.cart.change-qty');
    Route::delete('/user-cart/delete', [App\Http\Controllers\UserCartController::class, 'delete'])->name('user.cart.delete');
    Route::delete('/user-cart/empty', [App\Http\Controllers\UserCartController::class, 'empty'])->name('user.cart.empty');

    // User Branches
    Route::get('/load-branches', [App\Http\Controllers\UserCartController::class, 'loadBranches'])->name('user.load.branches');

    // User Translations
    Route::get('/locale/{type}', function ($type) {
        $translations = trans($type);
        return response()->json($translations);
    });
});

// Admin Routes
Route::middleware(['auth', 'admin_guard'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // Admin Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('admin.settings.store');
    Route::post('/settings/logo', [SettingController::class, 'uploadLogo'])->name('admin.settings.logo');
    Route::get('/branch/list', [SettingController::class, 'branchList'])->name('admin.branch.list');
    Route::get('/load-branches', [SettingController::class, 'loadBranches'])->name('adminload.branches');
    Route::post('/branch/store', [SettingController::class, 'branchStore'])->name('admin.branch.store');
    Route::post('/user/store', [SettingController::class, 'userStore'])->name('admin.user.store');
    Route::patch('/user/update/{user}', [SettingController::class, 'updateUser'])->name('admin.user.update');
    Route::delete('/admin/user/delete/{user}', [SettingController::class, 'deleteUser'])->name('admin.user.delete');
    Route::get('/users', [SettingController::class, 'getUsers'])->name('admin.users');
    Route::delete('/admin/branch/delete/{branch}', [SettingController::class, 'deleteBranch'])->name('admin.branch.delete');
    Route::patch('/branch/update/{branch}', [SettingController::class, 'updateBranch'])->name('admin.branch.update');
    Route::get('/branches', [SettingController::class, 'loadBranches'])->name('admin.branches');

    // Admin Products
    Route::resource('products', ProductController::class)->names([
        'index' => 'admin.products.index',
        'create' => 'admin.products.create',
        'store' => 'admin.products.store',
        'show' => 'admin.products.show',
        'edit' => 'admin.products.edit',
        'update' => 'admin.products.update',
        'destroy' => 'admin.products.destroy',
    ]);

    // Admin Customers
    Route::resource('customers', CustomerController::class)->names([
        'index' => 'admin.customers.index',
        'create' => 'admin.customers.create',
        'store' => 'admin.customers.store',
        'show' => 'admin.customers.show',
        'edit' => 'admin.customers.edit',
        'update' => 'admin.customers.update',
        'destroy' => 'admin.customers.destroy',
    ]);
    Route::post('/customers/{customer}/pay', [CustomerController::class, 'pay'])->name('admin.customers.pay');

    // Admin Suppliers
    Route::resource('suppliers', SupplierController::class)->names([
        'index' => 'admin.suppliers.index',
        'create' => 'admin.suppliers.create',
        'store' => 'admin.suppliers.store',
        'show' => 'admin.suppliers.show',
        'edit' => 'admin.suppliers.edit',
        'update' => 'admin.suppliers.update',
        'destroy' => 'admin.suppliers.destroy',
    ]);

    // Admin Cart
    Route::get('/cart', [CartController::class, 'index'])->name('admin.cart.index');
    Route::post('/cart-store', [CartController::class, 'store'])->name('admin.cart.store');
    Route::post('/cart/change-qty', [CartController::class, 'changeQty']);
    Route::delete('/cart/delete', [CartController::class, 'delete']);
    Route::delete('/cart/empty', [CartController::class, 'empty']);

    // Admin Sales
    Route::resource('sales', SaleController::class)->names([
        'index' => 'admin.sales.index',
        'create' => 'admin.sales.create',
        'store' => 'admin.sales.store',
        'show' => 'admin.sales.show',
        'edit' => 'admin.sales.edit',
        'update' => 'admin.sales.update',
        'destroy' => 'admin.sales.destroy',
    ]);
    Route::post('/sales/partial-payment', [SaleController::class, 'partialPayment'])->name('admin.sales.partial-payment');
    Route::get('/sales/print/{id}', [SaleController::class, 'print'])->name('admin.sales.print');

    // Admin Purchase
    Route::resource('purchase', PurchaseController::class)->names([
        'index' => 'admin.purchase.index',
        'create' => 'admin.purchase.create',
        'store' => 'admin.purchase.store',
        'show' => 'admin.purchase.show',
        'edit' => 'admin.purchase.edit',
        'update' => 'admin.purchase.update',
        'destroy' => 'admin.purchase.destroy',
    ]);
    Route::get('/purchase/print/{id}', [PurchaseController::class, 'print'])->name('admin.purchase.print');
    Route::post('/purchase/partial-payment', [PurchaseController::class, 'partialPayment'])->name('admin.purchase.partial-payment');

    // Admin Purchase Cart
    Route::get('/purchase-index', [PurchaseCartController::class, 'index'])->name('admin.purchases.index');
    Route::get('/purchase-create', [PurchaseCartController::class, 'create'])->name('admin.purchases.create');
    Route::get('/purchasecart', [PurchaseCartController::class, 'purchaseCart'])->name('admin.purchases.cart');
    Route::post('/purchase-cart', [PurchaseCartController::class, 'store'])->name('admin.purchases.store');
    Route::post('/purchase-cart/change-qty', [PurchaseCartController::class, 'changpurchaseeQty']);
    Route::post('/purchase-cart/change-purchaseprice', [PurchaseCartController::class, 'changePurchaseprice']);
    Route::delete('/purchase-cart/delete', [PurchaseCartController::class, 'delete']);
    Route::delete('/purchase-cart/empty', [PurchaseCartController::class, 'empty']);
    Route::get('/purchase/details/{purchase_id}', [PurchaseController::class, 'purchaseDetails']);

    // Admin Sales Return
    Route::resource('salesreturn', SalesreturnController::class)->names([
        'index' => 'admin.salesreturn.index',
        'create' => 'admin.salesreturn.create',
        'store' => 'admin.salesreturn.store',
        'show' => 'admin.salesreturn.show',
        'edit' => 'admin.salesreturn.edit',
        'update' => 'admin.salesreturn.update',
        'destroy' => 'admin.salesreturn.destroy',
    ]);
    Route::get('/salesreturn-cart', [SalesreturnCartController::class, 'index'])->name('admin.salesreturns.index');
    Route::post('/salesreturn-cart', [SalesreturnCartController::class, 'store'])->name('admin.salesreturns.store');
    Route::post('/salesreturn-cart/change-qty', [SalesreturnCartController::class, 'changeQty']);
    Route::post('/salesreturn-cart/change-sell-price', [SalesreturnCartController::class, 'changeSellPrice']);
    Route::delete('/salesreturn-cart/delete', [SalesreturnCartController::class, 'delete']);
    Route::delete('/salesreturn-cart/empty', [SalesreturnCartController::class, 'empty']);
    Route::get('/salesreturn/findorderid/{order_id}', [SalesreturnController::class, 'findOrderID']);
    Route::post('/salesreturn/cart', [SalesreturnController::class, 'addProductToCart']);
    Route::post('/salesreturn/changeqnty', [SalesreturnController::class, 'changeQnty']);
    Route::post('/salesreturn/delete', [SalesreturnController::class, 'handleDelete']);
    Route::get('/salesreturn/details/{salesreturn_id}', [SalesreturnController::class, 'salesreturnDetails']);
    Route::post('/salesreturn/finalsave', [SalesreturnController::class, 'finalSave']);

    // Admin Purchase Return
    Route::resource('purchasereturn', PurchasereturnController::class)->names([
        'index' => 'admin.purchasereturn.index',
        'create' => 'admin.purchasereturn.create',
        'store' => 'admin.purchasereturn.store',
        'show' => 'admin.purchasereturn.show',
        'edit' => 'admin.purchasereturn.edit',
        'update' => 'admin.purchasereturn.update',
        'destroy' => 'admin.purchasereturn.destroy',
    ]);
    Route::get('/purchasereturn-cart', [PurchasereturnCartController::class, 'index'])->name('admin.purchasereturns.index');
    Route::post('/purchasereturn-cart', [PurchasereturnCartController::class, 'store'])->name('admin.purchasereturns.store');
    Route::post('/purchasereturn-cart/change-qty', [PurchasereturnCartController::class, 'changeQty']);
    Route::delete('/purchasereturn-cart/delete', [PurchasereturnCartController::class, 'delete']);
    Route::delete('/purchasereturn-cart/empty', [PurchasereturnCartController::class, 'empty']);
    Route::get('/purchasereturn/findpurchaseid/{purchase_id}', [PurchasereturnController::class, 'findPurchaseID']);
    Route::post('/purchasereturn/cart', [PurchasereturnController::class, 'addProductToCart']);
    Route::post('/purchasereturn/changeqnty', [PurchasereturnController::class, 'changeQnty']);
    Route::post('/purchasereturn/delete', [PurchasereturnController::class, 'handleDelete']);
    Route::get('/purchasereturn/details/{id}', [PurchasereturnController::class, 'details']);
    Route::post('/purchasereturn/finalsave', [PurchasereturnController::class, 'finalSave']);

    // Admin Damage
    Route::resource('damage', DamageController::class)->names([
        'index' => 'admin.damage.index',
        'create' => 'admin.damage.create',
        'store' => 'admin.damage.store',
        'show' => 'admin.damage.show',
        'edit' => 'admin.damage.edit',
        'update' => 'admin.damage.update',
        'destroy' => 'admin.damage.destroy',
    ]);
    Route::get('/damage-create', [DamageController::class, 'create'])->name('admin.damages.create');
    Route::post('/damage-cart', [DamageController::class, 'store'])->name('admin.damages.store');
    Route::get('/damage/findorderid/{order_id}', [DamageController::class, 'findOrderID']);
    Route::post('/damage/cart', [DamageController::class, 'addProductToCart']);
    Route::post('/damage/changeqnty', [DamageController::class, 'changeQnty']);
    Route::post('/damage/delete', [DamageController::class, 'handleDelete']);
    Route::get('/damage/details/{salesreturn_id}', [DamageController::class, 'damage.details']);
    Route::post('/damage/finalsave', [DamageController::class, 'finalSave']);

    // Admin Expense Reports (move these above the resource route)
    Route::get('/expense/sales-details', [ExpenseController::class, 'salesDetails'])->name('admin.expense.sales-details');
    Route::get('/expense/purchase-details', [ExpenseController::class, 'purchaseDetails'])->name('admin.expense.purchase-details');
    Route::get('/expense/expense-details', [ExpenseController::class, 'expenseDetails'])->name('admin.expense.expense-details');
    Route::get('/expense/profit-details', [ExpenseController::class, 'profitDetails'])->name('admin.expense.profit-details');
    Route::get('/expense/cash-details', [ExpenseController::class, 'cashDetails'])->name('admin.expense.cash-details');
    // Admin Expense
    Route::resource('expense', ExpenseController::class)->names([
        'index' => 'admin.expense.index',
        'create' => 'admin.expense.create',
        'store' => 'admin.expense.store',
        'show' => 'admin.expense.show',
        'edit' => 'admin.expense.edit',
        'update' => 'admin.expense.update',
        'destroy' => 'admin.expense.destroy',
    ]);
    Route::get('/expense-head-create', [ExpenseController::class, 'createExpenseHead'])->name('admin.expense.head.create');
    Route::post('/expense-head-store', [ExpenseController::class, 'storeExpenseHead'])->name('admin.expense.head.store');
    Route::delete('/expense-head/{exp_head_id}', [ExpenseController::class, 'deleteExpenseHead'])->name('admin.expense.head.delete');

    // Translations route for React component
    Route::get('/locale/{type}', function ($type) {
        $translations = trans($type);
        return response()->json($translations);
    });

    Route::get('admin/suppliers/{supplier}/pay', [App\Http\Controllers\SupplierController::class, 'showPayForm'])->name('admin.suppliers.pay');
    Route::post('admin/suppliers/{supplier}/pay', [App\Http\Controllers\SupplierController::class, 'pay'])->name('admin.suppliers.pay.submit');

    Route::get('/branch-transfer', [\App\Http\Controllers\Admin\BranchTransferController::class, 'index'])->name('admin.branch-transfer.index');
    Route::get('/branch-transfer/create', [\App\Http\Controllers\Admin\BranchTransferController::class, 'create'])->name('admin.branch-transfer.create');
    Route::post('/branch-transfer', [\App\Http\Controllers\Admin\BranchTransferController::class, 'store'])->name('admin.branch-transfer.store');
});

// Temporary test route for sales return
Route::get('/test-salesreturn/{order_id}', function ($order_id) {
    $order = \App\Models\Sale::withoutGlobalScopes()
        ->where('id', $order_id)
        ->with([
            'items' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'customer' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'items.product' => function ($query) {
                $query->withoutGlobalScopes();
            }
        ])
        ->first();

    if ($order) {
        return response()->json([
            'success' => true,
            'order' => $order,
            'customer' => $order->customer,
            'items' => $order->items
        ]);
    } else {
        return response()->json(['success' => false, 'message' => 'Order not found']);
    }
});

// Test route to simulate findOrderID with authentication
Route::get('/test-findorderid/{order_id}', function ($order_id) {
    // Simulate being logged in as user 1
    \Illuminate\Support\Facades\Auth::login(\App\Models\User::find(1));

    $order = \App\Models\Sale::where('id', $order_id)->with(['items', 'customer', 'items.product'])->first();

    if ($order) {
        // Simulate the findOrderID logic
        $items = $order->items;
        \App\Models\SalesreturnItemCart::truncate();

        foreach ($items as $item) {
            $data = [
                'purchase_price' => $item->purchase_price,
                'total_price' => $item->sell_price * $item->quantity,
                'sell_price' => $item->sell_price,
                'qnty' => $item->quantity,
                'product_id' => $item->product_id,
                'order_id' => $order_id,
                'customer_id' => $order->customer_id,
                'user_id' => \Illuminate\Support\Facades\Auth::user()->id,
            ];
            \App\Models\SalesreturnItemCart::create($data);
        }

        $salesreturn_item = \App\Models\SalesreturnItemCart::with(['product', 'customer', 'product'])->get();

        return response()->json([
            'success' => true,
            'order' => $order,
            'customer' => $order->customer,
            'items' => $order->items,
            'salesreturn_items' => $salesreturn_item,
            'user' => \Illuminate\Support\Facades\Auth::user()
        ]);
    } else {
        return response()->json(['success' => false, 'message' => 'Order not found']);
    }
});
