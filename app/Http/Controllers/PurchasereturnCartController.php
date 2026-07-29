<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\PurchaseCart;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItems;
use App\Models\PurchaseReturnItemCart;
use Illuminate\Http\Request;

class PurchasereturnCartController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::all();
        $products = Product::with('branchStocks')->get();

        // Annotate each product with real total stock from branch_product_stocks
        $products->each(function ($product) {
            $product->real_stock = $product->branchStocks->sum('quantity');
        });

        $purchases = Purchase::with(['supplier', 'items'])->latest()->take(50)->get();

        $viewPath = auth()->user()->role === 'admin' ? 'admin.purchasereturn.purchasereturn-cart' : 'user.purchasereturn.purchasereturn-cart';
        return view($viewPath, compact('suppliers', 'products', 'purchases'));
    }

    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $supplierId = $request->supplier_id;
            $purchaseId = $request->purchase_id;

            // If no purchase_id, that's okay - allow ad-hoc returns
            if (empty($purchaseId)) {
                $purchaseId = null;
            }

            $returnAmount = $request->return_amount ?? 0;

            // Get cart items from the request
            $items = json_decode($request->items, true);

            if (empty($items)) {
                return response()->json(['success' => false, 'message' => 'Cart is empty'], 400);
            }

            // Calculate totals
            $totalPrice = 0;
            $totalQty = 0;
            foreach ($items as $item) {
                $totalPrice += $item['total_price'];
                $totalQty += $item['qnty'];
            }

            $discountAmount = floatval($request->discount_amount ?? 0);
            $netTotal = $totalPrice - $discountAmount;
            
            $profitAmount = $netTotal - $returnAmount;

            $purchaseReturn = PurchaseReturn::create([
                'supplier_id' => $supplierId,
                'user_id' => $user->id,
                'purchase_id' => $purchaseId,
                'total_qnty' => $totalQty,
                'total_amount' => $netTotal, 
                'return_amount' => $returnAmount,
                'profit_amount' => $profitAmount,
                'notes' => $request->notes ?? '',
                'company_id' => $user->company_id,
                'branch_id' => $user->branch_id,
            ]);

            $supplier = Supplier::find($supplierId);
            if ($supplier) {
                $supplier->balance += ($netTotal - $returnAmount);
                $supplier->save();
            }

            // Create purchase return items and update stock
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);

                // Returning to supplier = REMOVE from our stock
                if ($product) {
                    $product->quantity = max(0, $product->quantity - $item['qnty']);
                    $product->save();
                }

                // Also deduct from branch stock (use the branch stored on the cart item, or fallback to user branch)
                $branchId = !empty($item['branch_id']) ? $item['branch_id'] : $user->branch_id;
                $stock = \App\Models\BranchProductStock::where('product_id', $item['product_id'])
                    ->where('branch_id', $branchId)
                    ->first();
                if ($stock) {
                    $stock->quantity = max(0, $stock->quantity - $item['qnty']);
                    $stock->save();
                }

                PurchaseReturnItems::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'purchase_id' => !empty($item['purchase_id']) ? $item['purchase_id'] : $purchaseId,
                    'product_id' => $item['product_id'],
                    'purchase_price' => $item['purchase_price'],
                    'qnty' => $item['qnty'],
                    'supplier_id' => !empty($item['supplier_id']) ? $item['supplier_id'] : $supplierId,
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                    'branch_id' => $branchId,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Purchase return created successfully']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function changeQty(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);
        $cart = auth()->user()->cart()->where('id', $request->product_id)->first();

        if ($cart) {
            // check product quantity
            if ($product->quantity < $request->quantity) {
                return response([
                    'message' => __('cart.available', ['quantity' => $product->quantity]),
                ], 400);
            }
            $cart->pivot->quantity = $request->quantity;
            $cart->pivot->save();
        }

        return response([
            'success' => true
        ]);
    }

    public function delete(Request $request)
    {
        try {
            // Delete all items from purchase return cart
            PurchaseReturnItemCart::truncate();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function empty(Request $request)
    {
        try {
            // Delete all items from purchase return cart
            PurchaseReturnItemCart::truncate();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
