<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseStoreRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::all();
        $suppliers = Supplier::all();
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


        return view('purchase.index', compact('products', 'suppliers','purchases','total'));
    }




    public function store(PurchaseStoreRequest $request)
    {
        $purchase = Purchase::create([
            'supplier_id' => $request->supplier_id,
            'user_id' => $request->user()->id,
        ]);

        $cart = $request->user()->purchaseCart()->get();
        foreach ($cart as $item) {
            $purchase->items()->create([
                'purchase_price' => $item->purchase_price * $item->pivot->quantity,
                'quantity' => $item->pivot->quantity,
                'product_id' => $item->id,
            ]);
            $item->quantity = $item->quantity + $item->pivot->quantity;
            $item->save();
        }
        $request->user()->purchaseCart()->detach();
        $purchase->supplierPayments()->create([
            'amount' => $request->amount,
            'user_id' => $request->user()->id,
        ]);
        return 'success';
    }

    public function partialPayment(Request $request)
    {
        // return $request;
        $purchaseId = $request->purchase_id;
        $amount = $request->amount;

        // Find the purchase
        $purchase = Purchase::findOrFail($purchaseId);

        // Check if the amount exceeds the remaining balance
        $remainingAmount = $purchase->total() - $purchase->receivedAmount();
        if ($amount > $remainingAmount) {
            return redirect()->route('purchases.index')->withErrors('Amount exceeds remaining balance');
        }

        // Save the payment
        DB::transaction(function () use ($purchase, $amount) {
            $purchase->supplierPayments()->create([
                'amount' => $amount,
                'user_id' => auth()->user()->id,
            ]);
        });

        return redirect()->route('purchases.index')->with('success', 'Partial payment of ' . config('settings.currency_symbol') . number_format($amount, 2) . ' made successfully.');
    }

    
}
