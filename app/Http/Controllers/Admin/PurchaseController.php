<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PurchaseStoreRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\BranchProductStock;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $products = new Product();
        $products = $products->get();
        $suppliers = new Supplier();
        $suppliers = $suppliers->get();
        $salesreturns = [];
        $total = 0;
        $purchases = new Purchase();
        if ($request->start_date) {
            $purchases = $purchases->where('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $purchases = $purchases->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        $purchases = $purchases->with(['items.product', 'supplierPayments', 'supplier'])->latest()->paginate(10);

        $total = $purchases->map(function ($i) {
            return 1;
        })->sum();
        $receivedAmount = $purchases->map(function ($i) {
            return 1;
        })->sum();


        return view('admin.purchase.index', compact('products', 'suppliers', 'purchases', 'total'));
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
        $cart = $request->user()->purchaseCart()->with(['supplier', 'branch'])->get();

        // Calculate totals
        $sub_total = 0;
        $discount_amount = 0;
        $gr_total = 0;
        $paid_amount = 0;
        $new_balance = 0;
        $last_balance = 0;
        $prev_balance = 0;

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

        $total = 0;
        $printUrl = null;

        return view('admin.purchase.create', compact(
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
            'total',
            'printUrl'
        ));
    }

    public function purchaseDetails($purchase_id)
    {
        $purchase = Purchase::with(['items.product', 'items.branch', 'supplier'])->find($purchase_id);
        $total = 0;
        return view('admin.purchase.details', compact('purchase', 'total'));
    }




    public function store(PurchaseStoreRequest $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        $cart_line = "";
        $invoice_no = "";
        if (!$request->paid_amount) {
        }

        $cart = $request->user()->purchaseCart()->get();

        // Get supplier_id from the first cart item
        $supplier_id = isset($cart[0]) ? ($cart[0]->pivot->supplier_id ?? $request->supplier_id) : $request->supplier_id;

        if (isset($cart[0]->pivot)) {

            $invoice_no = $cart[0]->pivot ? $cart[0]->pivot->supplier_invoice_id : '';
        }

        $user = Auth::user();
        $purchase = Purchase::create([
            'supplier_id' => $supplier_id,
            'invoice_no' => $invoice_no,
            'paid_amount' => $request->paid_amount,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'branch_id' => $user->branch_id,
        ]);

        $sub_total = 0;
        $discount_amount = 0;
        $gr_total = 0;
        $paid_amount = 0;

        foreach ($cart as $item) {
            $product = Product::where('id', $item->id)->first();
            $purchase->items()->create([
                'purchase_price' => $item->pivot->purchase_price,
                'quantity' => $item->pivot->qnty,
                'product_id' => $item->id,
                'branch_id' => $item->pivot->branch_id, // Save branch_id
            ]);
            // Update branch stock
            $stock = BranchProductStock::firstOrCreate([
                'product_id' => $item->id,
                'branch_id' => $item->pivot->branch_id,
            ]);
            $stock->quantity += $item->pivot->qnty;
            $stock->save();

            // Update global product stock
            if ($product) {
                $product->quantity += $item->pivot->qnty;
                $product->save();
            }

            $sub_total += $item->pivot->qnty * $item->pivot->purchase_price;
        }

        $discount_amount = $request->discount_amount;
        $gr_total = $sub_total - $discount_amount;
        $paid_amount = $request->paid_amount;

        $purchase->sub_total = $sub_total;
        $purchase->discount_amount = $discount_amount;
        $purchase->gr_total = $gr_total;
        $purchase->paid_amount = $paid_amount;
        $purchase->save();

        $request->user()->purchaseCart()->detach();

        // Update supplier balance without global scope
        $supplier = \App\Models\Supplier::withoutGlobalScopes()->find($supplier_id);
        if ($supplier) {
            // Decrease balance by unpaid amount (debt increases)
            $supplier->balance -= ($purchase->gr_total - $paid_amount);
            $supplier->save();
        }
        if ($request->paid_amount > 0) {
            $purchase->supplierPayments()->create([
                'amount' => $request->paid_amount,
                'user_id' => $request->user()->id,
            ]);
        }

        return $purchase;
    }

    public function partialPayment(Request $request)
    {
        $purchaseId = $request->purchase_id;
        $amount = $request->paid_amount;

        // Find the purchase
        $purchase = Purchase::findOrFail($purchaseId);

        // Check how much has already been returned for this purchase
        $totalReturned = \App\Models\PurchaseReturnItems::where('purchase_id', $purchaseId)->sum('total_price');

        // Effective total = original total - returned total
        $effectiveTotal = $purchase->gr_total - $totalReturned;

        // Check if the amount exceeds the remaining balance
        $remainingAmount = $effectiveTotal - $purchase->paid_amount;

        if ($remainingAmount <= 0) {
            return redirect()->route('admin.purchase.index')->withErrors('This purchase has already been fully paid or returned.');
        }

        if ($amount > $remainingAmount) {
            return redirect()->route('admin.purchase.index')->withErrors('Amount exceeds remaining balance of ' . number_format($remainingAmount, 2));
        }

        // Update the paid_amount on the purchase
        $purchase->paid_amount = $purchase->paid_amount + $amount;
        $purchase->save();

        // Save the supplier payment record
        DB::transaction(function () use ($purchase, $amount) {
            $purchase->supplierPayments()->create([
                'amount' => $amount,
                'user_id' => Auth::user()->id,
            ]);
        });

        return redirect()->route('admin.purchase.index')->with('success', 'Payment of ' . config('settings.currency_symbol') . number_format($amount, 2) . ' made successfully.');
    }

    public function print($id)
    {
        $order = Purchase::with(['supplier', 'items'])->findOrFail($id);
        return view('purchase.print', compact('order'));
    }
}
