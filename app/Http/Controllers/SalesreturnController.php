<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SalesreturnItemCart;
use App\Models\Salesreturn;
use App\Models\SalesreturnItems;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\BranchProductStock;

class SalesreturnController extends Controller
{
    public function create(Request $request)
    {
        $products = new Product();
        $products = $products->get();
        $customers = new Customer();
        $customers = $customers->get();

        // Get current cart with customer relationship
        $cart = $request->user()->salesreturnCart()->with(['customer'])->get();

        // Calculate totals
        $sub_total = 0;
        $discount_amount = 0;
        $gr_total = 0;
        $return_amount = 0;
        $new_balance = 0;
        $last_balance = 0;
        $prev_balance = 0;

        if ($cart->count() > 0) {
            $sub_total = $cart->sum(function ($item) {
                return $item->pivot->qnty * $item->pivot->sell_price;
            });

            $order_id = $cart[0]->pivot->order_id ?? null;
            if ($order_id) {
                $order = \App\Models\Sale::find($order_id);
                if ($order) {
                    $discount_amount = $order->discount_amount;
                }
            }

            $gr_total = $sub_total - $discount_amount;

            $customer_id = $cart[0]->pivot->customer_id ?? null;

            if ($customer_id) {
                $customer = Customer::find($customer_id);
                if ($customer) {
                    $prev_balance = $customer->balance;
                    $new_balance = $customer->balance - $gr_total;
                    $last_balance = $new_balance + $return_amount;
                }
            }
        } else {
            $customer_id = null;
            $order_id = null;
        }

        $total = 0;
        $printUrl = null;

        $viewPath = Auth::user()->role === 'admin' ? 'admin.salesreturn.create' : 'user.salesreturn.create';

        return view($viewPath, compact(
            'products',
            'customers',
            'cart',
            'sub_total',
            'discount_amount',
            'gr_total',
            'return_amount',
            'new_balance',
            'last_balance',
            'prev_balance',
            'total',
            'printUrl',
            'customer_id',
            'order_id'
        ));
    }

    public function index(Request $request)
    {
        $salesreturns = new Salesreturn();
        if ($request->start_date) {
            $salesreturns = $salesreturns->where('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $salesreturns = $salesreturns->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        $salesreturns = $salesreturns->with(['items.product', 'customer', 'items'])->latest()->paginate(10);

        $total = 0;
        $viewPath = auth()->user()->role === 'admin' ? 'admin.salesreturn.index' : 'user.salesreturn.index';
        return view($viewPath, compact('salesreturns', 'total'));
    }

    public function salesreturnDetails($salesreturn_id)
    {
        $salesreturns = Salesreturn::where('id', $salesreturn_id)->with(['items', 'customer'])->get();
        $total = 0;
        $viewPath = auth()->user()->role === 'admin' ? 'admin.salesreturn.details' : 'user.salesreturn.details';
        return view($viewPath, compact('salesreturns', 'total'));
    }

    public function findOrderID($order_id)
    {
        $order = Sale::where('id', $order_id)->with(['items', 'customer', 'items.product'])->first();
        if ($order) {
            $items = $order->items;

            // Calculate already-returned quantities for this order
            $returnedQtys = SalesreturnItems::where('order_id', $order_id)
                ->selectRaw('product_id, SUM(qnty) as returned_qty')
                ->groupBy('product_id')
                ->pluck('returned_qty', 'product_id');

            // Check if everything has already been fully returned
            $allFullyReturned = $items->every(function ($item) use ($returnedQtys) {
                $originalQty = $item->quantity > 0 ? $item->quantity : 1;
                $alreadyReturned = $returnedQtys->get($item->product_id, 0);
                return $alreadyReturned >= $originalQty;
            });

            if ($allFullyReturned) {
                return response()->json(['error' => 'This order has already been fully returned.'], 422);
            }

            SalesreturnItemCart::truncate();
            foreach ($items as $item) {
                $originalQty = $item->quantity > 0 ? $item->quantity : 1;
                $unit_price = $item->sell_price / $originalQty;

                // Reduce qty by what was already returned
                $alreadyReturned = $returnedQtys->get($item->product_id, 0);
                $remainingQty = $originalQty - $alreadyReturned;

                if ($remainingQty <= 0) {
                    continue; // Skip fully returned items
                }

                $data = [
                    'purchase_price' => $item->purchase_price ?? 0,
                    'total_price' => $unit_price * $remainingQty,
                    'sell_price' => $unit_price,
                    'qnty' => $remainingQty,
                    'product_id' => $item->product_id,
                    'order_id' => $order_id,
                    'customer_id' => $order->customer_id,
                    'user_id' => Auth::user()->id,
                    'branch_id' => Auth::user()->branch_id,
                    'company_id' => Auth::user()->company_id,
                ];
                SalesreturnItemCart::create($data);
            }
            $salesreturn_item = SalesreturnItemCart::with(['product', 'customer', 'product'])->get();
            return response()->json(['order' => $order, 'salesreturn_item' => $salesreturn_item]);
        } else {
            return response()->json(['error' => 'Order not found'], 404);
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
            'customer_id' => 'required|exists:customers,id',
        ]);
        $barcode = $request->barcode;
        $product_id = $request->product_id;
        $customer_id = $request->customer_id;


        $salesreturn_cart = SalesreturnItemCart::where('product_id', $product_id)->first();
        if ($salesreturn_cart) {

            // update only quantity
            $salesreturn_cart->qnty = $salesreturn_cart->qnty + 1;
            $salesreturn_cart->total_price = $salesreturn_cart->qnty * $salesreturn_cart->sell_price;
            $salesreturn_cart->save();
        }

        return response('', 204);
    }

    public function finalSave(Request $request)
    {
        \Log::info('finalSave called with data: ', $request->all());

        try {
            // Get cart items from salesreturn_item_carts table
            $cart_items = $request->user()->salesreturnCart()->wherePivot('customer_id', $request->customer_id)->get();

            \Log::info('Cart items found: ' . $cart_items->count());

            if ($cart_items->count() > 0) {
                \Log::info('Processing cart items for sales return');

                $total_price = $cart_items->sum(function($item) {
                    return $item->pivot->qnty * $item->pivot->sell_price;
                });
                $discount_amount = floatval($request->discount_amount ?? 0);
                $discounted_total = $total_price - $discount_amount;
                $return_amount = $request->amount;
                $profit_amount = $discounted_total - $return_amount;

                \Log::info('Creating sales return with data: ', [
                    'customer_id' => $request->customer_id,
                    'user_id' => Auth::user()->id,
                    'total_amount' => $total_price,
                    'return_amount' => $return_amount,
                ]);

                $salesreturn = Salesreturn::create([
                    'customer_id' => $request->customer_id,
                    'user_id' => Auth::user()->id,
                    'order_id' => $cart_items[0]->pivot->order_id ?? null,
                    'total_qnty' => $cart_items->sum(function($item) {
                        return $item->pivot->qnty;
                    }),
                    'total_amount' => $total_price,
                    'return_amount' => $return_amount,
                    'profit_amount' => $profit_amount,
                    'notes' => $request->notes,
                    'branch_id' => Auth::user()->branch_id,
                    'company_id' => Auth::user()->company_id,
                ]);

                foreach ($cart_items as $item) {
                    $data = [
                        'salesreturn_id' => $salesreturn->id,
                        'order_id' => $item->pivot->order_id ?? null,
                        'product_id' => $item->id,
                        'purchase_price' => $item->purchase_price ?? 0,
                        'sell_price' => $item->pivot->sell_price,
                        'qnty' => $item->pivot->qnty,
                        'total_price' => $item->pivot->sell_price * $item->pivot->qnty,
                        'customer_id' => $item->pivot->customer_id,
                        'user_id' => Auth::user()->id,
                        'branch_id' => Auth::user()->branch_id,
                        'company_id' => Auth::user()->company_id,
                    ];
                    SalesreturnItems::create($data);

                    // Update product stock
                    $product = Product::find($item->id);
                    if ($product) {
                        $product->quantity += $item->pivot->qnty;
                        $product->save();
                    }

                    // Update branch stock
                    $stock = BranchProductStock::firstOrCreate([
                        'product_id' => $item->id,
                        'branch_id' => Auth::user()->branch_id,
                    ]);
                    $stock->quantity += $item->pivot->qnty;
                    $stock->save();
                }

                if ($request->customer_id) {
                    $customer = Customer::where('id', $request->customer_id)->first();
                    $customer->balance = $customer->balance - $discounted_total + floatval($request->amount);
                    $customer->save();
                }

                // Empty the return cart
                $request->user()->salesreturnCart()->detach();

                return response([
                    'success' => true,
                    'message' => 'Sales return processed successfully'
                ]);
            } else {
                return response([
                    'success' => false,
                    'message' => 'No items in the sales return cart for this customer'
                ], 400);
            }
        } catch (\Exception $e) {
            return response([
                'success' => false,
                'message' => 'Error processing sales return: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handleDelete(Request $request)
    {
        $product_id = $request->product_id;
        SalesreturnItemCart::where('product_id', $product_id)->delete();
        return response()->json('success');
    }

    public function store(Request $request)
    {
        $order = Sale::create([
            'customer_id' => $request->customer_id,
            'user_id' => Auth::user()->id,
        ]);

        $cart = Auth::user()->cart()->get();
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
        Auth::user()->cart()->detach();
        $order->payments()->create([
            'amount' => $request->amount,
            'user_id' => Auth::user()->id,
        ]);

        if ($request->customer_id) {
            $customer = Customer::where('id', $request->customer_id)->first();
            $customer->balance = $customer->balance + ($sum_cart - $request->amount);
            $customer->save();
        }
        return $order;
    }
}
