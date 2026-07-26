<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesreturnCartController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {

            return response(
                $request->user()->cart->each(function ($product) {
                    $customer = Customer::find($product->pivot->customer_id);
                    $product->pivot->user_balance = $customer?->balance ?? 0;
                })
            );
        }

        // Get products and customers
        $products = Product::get();
        $customers = Customer::get();

        // Get current cart
        $cart = $request->user()->cart()->get();

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
                return $item->pivot->quantity * $item->pivot->sell_price;
            });

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
        }

        $total = 0;
        $printUrl = null;

        $viewPath = Auth::user()->role === 'admin' ? 'admin.salesreturn.salesreturn-cart' : 'user.salesreturn.salesreturn-cart';

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
            'printUrl'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barcode' => 'required|exists:products,barcode',
            'customer_id' => 'required|exists:customers,id',
        ]);
        $barcode = $request->barcode;
        $customer_id = $request->customer_id;

        $product = Product::where('barcode', $barcode)->first();
        $cart = $request->user()->cart()->where('barcode', $barcode)->first();
        if ($cart) {
            // check product quantity
            if ($product->quantity <= $cart->pivot->quantity) {
                return response([
                    'message' => __('cart.available', ['quantity' => $product->quantity]),
                ], 400);
            }
            // update only quantity
            $cart->pivot->quantity = $cart->pivot->quantity + 1;
            $cart->pivot->save();
        } else {
            if ($product->quantity < 1) {
                return response([
                    'message' => __('cart.outstock'),
                ], 400);
            }
            $request->user()->cart()->attach($product->id, ['quantity' => 1, 'customer_id' => $customer_id, 'sell_price' => $product->sell_price]);
        }

        return response('', 204);
    }

    public function changeQty(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $product = Product::find($request->product_id);
            $cart = $request->user()->cart()->where('id', $request->product_id)->first();

            if (!$cart) {
                return response([
                    'success' => false,
                    'message' => 'Product not found in cart',
                ], 404);
            }

            // check product quantity
            if ($product->quantity < $request->quantity) {
                return response([
                    'success' => false,
                    'message' => __('cart.available', ['quantity' => $product->quantity]),
                ], 400);
            }

            $cart->pivot->quantity = $request->quantity;

            try {
                $result = $cart->pivot->save();
                if (!$result) {
                    return response([
                        'success' => false,
                        'message' => 'Failed to update quantity in database - save returned false',
                    ], 500);
                }
            } catch (\Illuminate\Database\QueryException $e) {
                return response([
                    'success' => false,
                    'message' => 'Database query error: ' . $e->getMessage(),
                ], 500);
            }

            return response([
                'success' => true,
                'message' => 'Quantity updated successfully'
            ]);

        } catch (\Exception $e) {
            return response([
                'success' => false,
                'message' => 'General error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function changeSellPrice(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'sell_price' => 'required|numeric|min:0',
        ]);

        try {
            $cart = $request->user()->cart()->where('id', $request->product_id)->first();

            if (!$cart) {
                return response([
                    'success' => false,
                    'message' => 'Product not found in cart',
                ], 404);
            }

            $cart->pivot->sell_price = $request->sell_price;

            try {
                $result = $cart->pivot->save();
                if (!$result) {
                    return response([
                        'success' => false,
                        'message' => 'Failed to update sell price in database - save returned false',
                    ], 500);
                }
            } catch (\Illuminate\Database\QueryException $e) {
                return response([
                    'success' => false,
                    'message' => 'Database query error: ' . $e->getMessage(),
                ], 500);
            }

            return response([
                'success' => true,
                'message' => 'Sell price updated successfully'
            ]);

        } catch (\Exception $e) {
            return response([
                'success' => false,
                'message' => 'General error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id'
        ]);
        $request->user()->cart()->detach($request->product_id);

        return response('', 204);
    }

    public function empty(Request $request)
    {
        $request->user()->cart()->detach();

        return response('', 204);
    }
}
