<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Models\Order;
use App\Models\SalesreturnItemCart;
use App\Models\PurchaseReturnItemCart;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItems;
use App\Models\Salesreturn;
use App\Models\SalesreturnItems;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BranchProductStock;

class PurchasereturnController extends Controller
{
    public function index(Request $request)
    {
        $purchase_returns = new PurchaseReturn();
        if ($request->start_date) {
            $purchase_returns = $purchase_returns->where('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $purchase_returns = $purchase_returns->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        $purchase_returns = $purchase_returns->with(['items.product', 'supplier', 'items'])->latest()->paginate(10);

        $total = 0;

        $viewPath = auth()->user()->role === 'admin' ? 'admin.purchasereturn.index' : 'user.purchasereturn.index';
        return view($viewPath, compact('purchase_returns', 'total'));
    }

    public function salesreturnDetails($salesreturn_id)
    {
        $salesreturns = Salesreturn::where('id', $salesreturn_id)->with(['items', 'customer'])->get();
        $total = 0;
        return view('salesreturn.details', compact('salesreturns', 'total'));
    }

    public function findPurchaseID($purchase_id)
    {
        $purchase = Purchase::where('id', $purchase_id)->with(['items', 'supplier', 'items.product'])->first();
        if ($purchase) {
            $items = $purchase->items;
            PurchaseReturnItemCart::truncate();
            $user = auth()->user();
            foreach ($items as $item) {
                $data = [
                    'purchase_price' => $item->purchase_price,
                    'total_price' => $item->purchase_price * $item->qnty,
                    'sell_price' => $item->sell_price,
                    'qnty' => $item->qnty,
                    'product_id' => $item->product_id,
                    'purchase_id' => $purchase_id, // ensure purchase_id is set
                    'supplier_id' => $purchase->supplier_id,
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                    'branch_id' => $user->branch_id,
                ];
                PurchaseReturnItemCart::create($data);
            }
            $purchasereturn_items = PurchaseReturnItemCart::with(['product', 'supplier', 'product'])->get();
            return response()->json(['purchase' => $purchase, 'purchasereturn_items' => $purchasereturn_items]);
        }
    }

    public function changeQnty(Request $request)
    {
        $salesreturn_item = SalesreturnItemCart::where('product_id', $request->product_id)->first();
        if ($salesreturn_item) {
            $salesreturn_item->qnty = $request->qnty;
            $salesreturn_item->sell_price = $request->sell_price;
            $salesreturn_item->total_price = $request->sell_price * $request->qnty;
            $salesreturn_item->save();
        }

        return response()->json($request->all());
    }

    public function addProductToCart(Request $request)
    {
        $request->validate([
            'barcode' => 'required',
            'supplier_id' => 'required|exists:suppliers,id',
        ]);
        $barcode = $request->barcode;
        $product_id = $request->product_id;
        $supplier_id = $request->supplier_id;
        $purchase_id = $request->purchase_id; // get purchase_id from request if available
        $product = Product::where('id', $request->product_id)->first();

        $user = $request->user();
        $purchasereturn_cart = PurchaseReturnItemCart::where('product_id', $product_id)->first();
        if ($purchasereturn_cart) {
            // update only quantity
            $purchasereturn_cart->qnty = $purchasereturn_cart->qnty + 1;
            $purchasereturn_cart->total_price = $purchasereturn_cart->qnty * $purchasereturn_cart->purchase_price;
            // ensure purchase_id is set if not already
            if (!$purchasereturn_cart->purchase_id && $purchase_id) {
                $purchasereturn_cart->purchase_id = $purchase_id;
            }
            $purchasereturn_cart->save();
        } else {
            $purchasereturn_cart = new PurchaseReturnItemCart;
            $purchasereturn_cart->supplier_id = $request->supplier_id;
            $purchasereturn_cart->product_id = $request->product_id;
            $purchasereturn_cart->qnty =  1;
            $purchasereturn_cart->purchase_price = $product->purchase_price;
            $purchasereturn_cart->total_price = 1 * $product->purchase_price;
            $purchasereturn_cart->user_id = $user->id;
            $purchasereturn_cart->company_id = $user->company_id;
            $purchasereturn_cart->branch_id = $user->branch_id;
            $purchasereturn_cart->purchase_id = $purchase_id;
            $purchasereturn_cart->save();
        }
        $purchasereturn_cart = PurchaseReturnItemCart::with(['product', 'supplier', 'product'])->get();
        return response()->json($purchasereturn_cart, 200);
    }

    public function finalSave(Request $request)
    {
        try {
            $user = auth()->user();
            // Fallback: ensure all cart items for this user are linked to this purchase_id
            PurchaseReturnItemCart::where('user_id', $user->id)
                ->whereNull('purchase_id')
                ->update(['purchase_id' => $request->purchase_id]);

            $purchase = Purchase::where('id', $request->purchase_id)->first();
            $return_items = PurchaseReturnItemCart::where('purchase_id', $request->purchase_id)->get();

            if (!!$purchase) {
                $total_price = $return_items->sum('total_price');
                $total_qnty = $return_items->sum('qnty');
                $return_amount = $request->amount;
                $profit_amount = $total_price - $return_amount;
                $purchasereturn = PurchaseReturn::create([
                    'supplier_id' => $request->supplier_id,
                    'user_id' => $user->id,
                    'purchase_id' => $request->purchase_id,
                    'total_qnty' => $total_qnty,
                    'total_amount' => $total_price,
                    'return_amount' => $return_amount,
                    'profit_amount' => $profit_amount,
                    'notes' => $request->notes,
                    'company_id' => $user->company_id,
                    'branch_id' => $user->branch_id,
                ]);



                foreach ($return_items as $item) {
                    $data = [
                        'purchase_return_id' => $purchasereturn->id,
                        'purchase_id' => $request->purchase_id,
                        'product_id' => $item->product_id,
                        'purchase_price' => $item->purchase_price,
                        'sell_price' => $item->sell_price,
                        'qnty' => $item->qnty,
                        'supplier_id' => $request->supplier_id,
                        'user_id' => $request->user()->id,
                    ];
                    PurchaseReturnItems::create($data);
                    // Update branch stock
                    $stock = BranchProductStock::firstOrCreate([
                        'product_id' => $item->product_id,
                        'branch_id' => $item->branch_id,
                    ]);
                    $stock->quantity -= $item->qnty;
                    $stock->save();
                }


                if ($request->supplier_id) {
                    $supplier = Supplier::where('id', $request->supplier_id)->first();
                    $supplier->balance = $supplier->balance - $request->amount;
                    $supplier->save();
                }

                PurchaseReturnItemCart::truncate();

                return $purchasereturn;
            }
        } catch (\Exception $ex) {
            return response()->json($ex->getMessage(), 500);
        }
    }

    public function handleDelete(Request $request)
    {
        $product_id = $request->product_id;
        SalesreturnItemCart::where('product_id', $product_id)->delete();
        return response()->json('success');
    }

    public function store(OrderStoreRequest $request)
    {
        $order = Sale::create([
            'customer_id' => $request->customer_id,
            'user_id' => $request->user()->id,
        ]);

        $cart = $request->user()->cart()->get();
        $sum_cart = $cart->sum('sell_price');

        foreach ($cart as $item) {
            $order->items()->create([
                'sell_price' => $item->sell_price * $item->pivot->quantity,
                'quantity' => $item->pivot->quantity,
                'product_id' => $item->id,
            ]);
            $item->quantity = $item->quantity - $item->pivot->quantity;
            $item->save();
        }
        $request->user()->cart()->detach();
        $order->payments()->create([
            'amount' => $request->amount,
            'user_id' => $request->user()->id,
        ]);

        if ($request->customer_id) {
            $customer = Customer::where('id', $request->customer_id)->first();
            $customer->balance = $customer->balance + ($sum_cart - $request->amount);
            $customer->save();
        }
        return $order;
    }

    public function details($id)
    {
        $purchase_return = PurchaseReturn::with(['items.product', 'supplier', 'purchase'])->findOrFail($id);
        $total = $purchase_return->total_amount;
        $viewPath = auth()->user()->role === 'admin' ? 'admin.purchasereturn.details' : 'user.purchasereturn.details';
        return view($viewPath, compact('purchase_return', 'total'));
    }
}
