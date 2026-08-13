<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleStoreRequest;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\SalesreturnItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\BranchProductStock;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $sales = new Sale();
        if ($user->role !== 'admin') {
            $sales = $sales->where('branch_id', $user->branch_id)->where('company_id', $user->company_id);
        }
        if ($request->start_date) {
            $sales = $sales->where('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $sales = $sales->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        $sales = $sales->with(['items.product', 'payments', 'customer'])->latest()->paginate(10);

        $total = $sales->map(function ($i) {
            return $i->total();
        })->sum();
        $receivedAmount = $sales->map(function ($i) {
            return $i->receivedAmount();
        })->sum();

        $viewPath = $user->role === 'admin' ? 'admin.sales.index' : 'user.sales.index';
        return view($viewPath, compact('sales', 'total', 'receivedAmount'));
    }

    public function show(Sale $sale)
    {
        $sales = $sale->load(['items', 'customer', 'items.product']);
        return response()->json($sales);
    }

    public function store(SaleStoreRequest $request)
    {
        try {
            $user = Auth::user();
            $company_id = $user->company_id;
            $branch_id = $user->role == 'admin' ? $request->branch_id : $user->branch_id;
            $salesman_id = $request->salesman_id != '0' ? $request->salesman_id : 0;

            // Calculate grand total from cart items
            $cart = Auth::user()->cart()->get();
            $subTotal = 0;
            foreach ($cart as $item) {
                $product = \App\Models\Product::find($item->id);
                $sellPrice = $product ? $product->sell_price : 0;
                $subTotal += $sellPrice * $item->pivot->quantity;
            }
            $grandTotal = $subTotal - ($request->discount_amount ?? 0);

            // Prepare installment data
            $installmentData = [];
            if ($request->installment_plan) {
                $installmentData = [
                    'installment_plan' => $request->installment_plan,
                    'installment_amount' => $grandTotal / $request->installment_plan,
                    'installment_start_date' => now(),
                    'installment_status' => 'active',
                ];
            }

            // Debug: Check what table the Sale model is using
            $saleModel = new Sale();
            $tableName = $saleModel->getTable();

            // Debug: Check request data
            $debugData = [
                'table_name' => $tableName,
                'request_data' => [
                    'customer_id' => $request->customer_id,
                    // 'salesman_id' => $request->salesman_id != "0"?$request->salesman_id:"",
                    'installment_plan' => $request->installment_plan,
                    'installmentData' => $installmentData
                ]
            ];

            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                // 'salesman_id' => $request->salesman_id != "0"?$request->salesman_id:"",
                'sub_total' => 0,
                'discount_amount' => 0,
                'gr_total' => 0,
                'paid_amount' => 0,
                'profit_amount' => 0,
                'user_id' => $user->id,
                'branch_id' => $branch_id,
                'company_id' => $company_id,
                ...$installmentData,
            ]);

            $cart = Auth::user()->cart()->get();
            $sum_cart = $cart->sum('sell_price');

            $totalProfit = 0; // Initialize profit calculation
            $subTotal = 0; // Initialize subtotal calculation

            foreach ($cart as $item) {
                $product = \App\Models\Product::find($item->id);
                $purchasePrice = $product && $product->purchase_price !== null ? $product->purchase_price : 0;
                $sale->items()->create([
                    'purchase_price' => $purchasePrice,
                    'sell_price' => $item->sell_price * $item->pivot->quantity,
                    'quantity' => $item->pivot->quantity,
                    'product_id' => $item->id,
                    'sale_id' => $sale->id,
                    'user_id' => $user->id,
                    'branch_id' => $branch_id,
                    'company_id' => $company_id,
                ]);

                // Calculate profit for this item: (sell_price - purchase_price) * quantity
                $itemProfit = ($item->sell_price - $purchasePrice) * $item->pivot->quantity;
                $totalProfit += $itemProfit;

                // Calculate subtotal
                $subTotal += $item->sell_price * $item->pivot->quantity;

                // Update branch stock
                $stock = BranchProductStock::where('product_id', $item->id)
                    ->where('branch_id', $branch_id)
                    ->first();
                if ($stock) {
                    $stock->quantity -= $item->pivot->quantity;
                    $stock->save();
                }

                // Update global product stock
                if ($product) {
                    $product->quantity -= $item->pivot->quantity;
                    $product->save();
                }
            }

            // Update the order with calculated values
            $discount = $request->discount_amount ?? 0;
            $sale->sub_total = $subTotal;
            $sale->discount_amount = $discount;
            $sale->gr_total = $subTotal - $discount;
            $sale->paid_amount = $request->amount;
            $sale->profit_amount = $totalProfit;
            $sale->save();

            Auth::user()->cart()->detach();
            $sale->payments()->create([
                'amount' => $request->amount,
                'user_id' => $user->id,
                'branch_id' => $branch_id,
                'company_id' => $company_id,
            ]);

            if ($request->customer_id) {
                $customer = Customer::where('id', $request->customer_id)->first();
                if ($customer) {
                    $customer->balance = $customer->balance + (($subTotal - $discount) - $request->amount);
                    $customer->save();
                }
            }

            // Return sale with debug data for frontend logging
            return response()->json([
                'sale' => $sale,
                'debug' => $debugData
            ]);
        } catch (\Exception $e) {
            // Extract key error info without full trace
            $errorMessage = $e->getMessage();
            $errorType = get_class($e);

            // Clean up the error message for readability
            if (strpos($errorMessage, 'SQLSTATE') === 0) {
                // Extract the useful part of SQL errors
                if (preg_match('/SQLSTATE\[\w+\]:\s*(.+?)(?:\s*\(.+\))?$/s', $errorMessage, $matches)) {
                    $errorMessage = trim($matches[1]);
                }
            }

            return response()->json([
                'error' => 'Failed to save sale',
                'type' => $errorType,
                'message' => $errorMessage,
                'request_data' => $request->all()
            ], 500);
        }
    }
    public function partialPayment(Request $request)
    {
        $orderId = $request->order_id;
        $amount = $request->amount;
        $user = Auth::user();
        if ($user->role == "admin") {
            $branch_id = $request->branch_id;
        } else {
            $branch_id = $user->branch_id;
        }

        // Find the order
        $order = Sale::findOrFail($orderId);

        // Check how much has already been returned for this order
        $totalReturned = SalesreturnItems::where('order_id', $orderId)->sum('total_price');

        // Effective remaining balance = (grand total - what was returned) - what was already paid
        $effectiveTotal = $order->gr_total - $totalReturned;
        $remainingAmount = $effectiveTotal - $order->receivedAmount();

        if ($remainingAmount <= 0) {
            $route = $user->role === 'admin' ? 'admin.sales.index' : 'user.sales.index';
            return redirect()->route($route)->withErrors('This sale has already been fully paid or returned.');
        }

        // Check if the amount exceeds the remaining balance
        if ($amount > $remainingAmount) {
            $route = $user->role === 'admin' ? 'admin.sales.index' : 'user.sales.index';
            return redirect()->route($route)->withErrors('Amount exceeds remaining balance of ' . number_format($remainingAmount, 2));
        }

        // Save the payment
        DB::transaction(function () use ($order, $amount, $user, $branch_id) {
            $order->payments()->create([
                'amount' => $amount,
                'user_id' => Auth::id(),
                'branch_id' => $branch_id,
                'company_id' => $user->company_id,
            ]);
        });

        // Update customer balance AFTER successful validation and save
        if ($order->customer_id) {
            $customer = Customer::where('id', $order->customer_id)->first();
            if ($customer) {
                $customer->balance = $customer->balance - $amount;
                $customer->save();
            }
        }

        return redirect()->route($user->role === 'admin' ? 'admin.sales.index' : 'user.sales.index')->with('success', 'Partial payment of ' . config('settings.currency_symbol') . number_format($amount, 2) . ' made successfully.');
    }

    public function print($id)
    {
        $order = Sale::with(['customer', 'items.product'])->findOrFail($id);
        return view('orders.print', compact('order'));
    }
}
