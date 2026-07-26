<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BranchProductStock;

class PurchaseCartController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {

            return response(
                $request->user()->purchaseCart->each(function ($product) {
                    $supplier = Supplier::find($product->pivot->supplier_id);
                    $product->pivot->user_balance = $supplier?->balance ?? 0;
                })
            );
        }


        return view('purchase.index');
    }

    public function purchaseCart(Request $request)
    {
        return response(
            $request->user()->purchaseCart->each(function ($product) {
                $supplier = Supplier::find($product->pivot->supplier_id);
                $product->pivot->user_balance = $supplier?->balance ?? 0;
            })
        );


        $purchase = Purchase::create([
            'supplier_id' => $request->supplier_id,
            'invoice_no' => $request->invoice_no,
            'sub_total' => $request->sub_total,
            'discount_amount' => $request->discount_amount,
            'gr_total' => $request->gr_total,
            'paid_amount' => $request->paid_amount,
            'user_id' => $request->user()->id,
        ]);

        $purchaseCarts = $request->user()->purchaseCart()->get();

        foreach ($purchaseCarts as $item) {
            $purchase->items()->create([
                'purchase_price' => $item->purchase_price,
                'qnty' => $item->pivot->quantity,
                'product_id' => $item->id,
            ]);
            // Update branch stock
            $stock = BranchProductStock::firstOrCreate([
                'product_id' => $item->id,
                'branch_id' => $item->pivot->branch_id,
            ]);
            $stock->quantity += $item->pivot->quantity;
            $stock->save();
        }
        $request->user()->purchaseCart()->detach();
        $purchase->supplierPayments()->create([
            'amount' => $request->amount,
            'purchase_id' => $purchase->id,
            'user_id' => $request->user()->id,
        ]);

        if ($request->supplier_id) {
            $supplier = Supplier::where('id', $request->supplier_id)->first();
            $supplier->balance = $supplier->balance + ($sum_cart - $request->amount);
            $supplier->save();
        }
        return $purchase;

        return response(
            ['hello']
            // $request->user()->purchaseCart()->get()
        );
    }

    public function create(Request $request)
    {
        $products = new Product();
        $products = $products->get();
        $suppliers = new Supplier();
        $suppliers = $suppliers->get();

        // Get branches for multi-branch support
        $branches = \App\Models\Branch::all();

        // Get current cart with supplier and branch relationships
        $cart = $request->user()->purchaseCart()->with([ 'branch'])->get();

        // Calculate totals
        $sub_total = 0;
        $discount_amount = 0;
        $gr_total = 0;
        $paid_amount = 0;
        $new_balance = 0;
        $last_balance = 0;
        $prev_balance = 0;

        $supplier_id = null;
        if ($cart->count() > 0) {
            $sub_total = $cart->sum(function ($item) {
                return $item->pivot->qnty * $item->pivot->purchase_price;
            });

            $gr_total = $sub_total - $discount_amount;

            $supplier_id = $cart[0]->pivot->supplier_id ?? null;
            $supplier_invoice_no = $cart[0]->pivot->supplier_invoice_id ?? '';

            if ($supplier_id) {
                $supplier = Supplier::find($supplier_id);
                if ($supplier) {
                    $prev_balance = $supplier->balance;
                    $new_balance = $supplier->balance + $gr_total;
                    $last_balance = $new_balance - $paid_amount;
                }
            }
        }

        $salesreturns = [];
        $total = 0;
        $printUrl = null;

        $viewPath = Auth::user()->role === 'admin' ? 'admin.purchase.create' : 'user.purchase.create';

        return view($viewPath, compact(
            'products',
            'suppliers',
            'branches',
            'cart',
            'sub_total',
            'discount_amount',
            'gr_total',
            'paid_amount',
            'new_balance',
            'last_balance',
            'prev_balance',
            'salesreturns',
            'total',
            'printUrl',
            'supplier_id'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barcode' => 'required|exists:products,barcode',
            'supplier_id' => 'required|exists:suppliers,id',
        ]);
        $barcode = $request->barcode;

        $user = $request->user();
        $company_id = $user->company_id;
        $product = Product::where('barcode', $barcode)->first();

        // Multi-branch support
        if ($request->has('branch_quantities') && is_array($request->branch_quantities)) {
            foreach ($request->branch_quantities as $branch_id => $quantity) {
                if ((int)$quantity > 0) {
                    $user->purchaseCart()->attach($product->id, [
                        'qnty' => (int)$quantity,
                        'supplier_id' => $request->supplier_id,
                        'supplier_invoice_id' => $request->supplier_invoice_no,
                        'purchase_price' => $product->purchase_price,
                        'sell_price' => $product->sell_price,
                        'user_id' => $user->id,
                        'branch_id' => $branch_id,
                        'company_id' => $company_id,
                    ]);
                }
            }
            return response('cart added', 204);
        }

        // Fallback: single branch (legacy)
        $branch_id = $user->role == 'admin' ? ($request->branch_id ?? $user->branch_id) : $user->branch_id;
        $cart = $request->user()->purchaseCart()->where('barcode', $barcode)->first();
        if ($cart) {
            $cart->pivot->qnty = $cart->pivot->qnty + 1;
            $cart->pivot->save();
        } else {
            $request->user()->purchaseCart()->attach($product->id, [
                'qnty' => 1,
                'supplier_id' => $request->supplier_id,
                'supplier_invoice_id' => $request->supplier_invoice_no,
                'purchase_price' => $product->purchase_price,
                'sell_price' => $product->sell_price,
                'user_id' => $user->id,
                'branch_id' => $branch_id,
                'company_id' => $company_id,
            ]);
        }
        return response('cart added', 204);
    }

    public function changpurchaseeQty(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);
        $cart = $request->user()->purchaseCart()->where('products.id', $request->product_id)->first();

        if ($cart) {

            $cart->pivot->qnty = $request->quantity;
            $cart->pivot->save();
        }

        return response([
            'success' => true
        ]);
    }

    public function changePurchaseprice(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'purchase_price' => 'required|numeric|min:1',
        ]);

        $product = Product::find($request->product_id);
        $cart = $request->user()->purchaseCart()->where('products.id', $request->product_id)->first();

        if ($cart) {
            $product->purchase_price = $request->purchase_price;
            $product->save();

            $cart->pivot->purchase_price = $request->purchase_price;
            $cart->pivot->save();
        }

        return response([
            'success' => true
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id'
        ]);
        $request->user()->purchaseCart()->detach($request->product_id);

        return response('', 204);
    }

    public function empty(Request $request)
    {
        $request->user()->purchaseCart()->detach();

        return response('', 204);
    }
}
