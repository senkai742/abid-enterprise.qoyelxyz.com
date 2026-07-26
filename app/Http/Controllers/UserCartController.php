<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BranchProductStock;

class UserCartController extends Controller
{
    public function index()
    {
        return view('user.cart.index');
    }

    public function getCart(Request $request)
    {
        if ($request->wantsJson()) {
            $user = Auth::user();

            // Get cart items with proper company and branch filtering
            $cart = $user->cart()
                ->where('user_cart.company_id', $user->company_id)
                ->where('user_cart.branch_id', $user->branch_id)
                ->get()
                ->each(function ($product) {
                    $customer = Customer::find($product->pivot->customer_id);
                    $product->pivot->user_balance = $customer?->balance ?? 0;
                });

            return response($cart);
        }
        return response('Invalid request', 400);
    }

    public function store(Request $request)
    {
        $request->validate([
            'barcode' => 'required|exists:products,barcode',
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $user = Auth::user();
        $barcode = $request->barcode;
        $customer_id = $request->customer_id;
        $branch_id = $request->branch_id;
        $company_id = $user->company_id;
        $user_id = $user->id;

        // Verify that the branch_id matches the user's branch (for non-admin users)
        if ($user->role !== 'admin' && $branch_id != $user->branch_id) {
            return response([
                'message' => 'You can only add items to your own branch.',
            ], 403);
        }

        // Get product with company filtering
        $product = new Product();
        $product = $product->where('barcode', $barcode)->first();

        if (!$product) {
            return response([
                'message' => 'Product not found or not available for your company.',
            ], 404);
        }

        // Check if product already exists in cart for this user, company, and branch
        $cart = $user->cart()
            ->where('barcode', $barcode)
            ->where('user_cart.branch_id', $branch_id)
            ->where('user_cart.company_id', $company_id)
            ->first();

        if ($cart) {
            // check branch stock quantity
            $stock = BranchProductStock::where('product_id', $product->id)
                ->where('branch_id', $branch_id)
                ->first();
            if ($stock && $stock->quantity <= $cart->pivot->quantity) {
                return response([
                    'message' => __('cart.available', ['quantity' => $stock->quantity]),
                ], 400);
            }
            // update only quantity
            $cart->pivot->quantity = $cart->pivot->quantity + 1;
            $cart->pivot->save();
            if (!$stock || $stock->quantity < 1) {
                return response([
                    'message' => __('cart.outstock'),
                ], 400);
            }
            $user->cart()->attach($product->id, [
                'quantity' => 1,
                'customer_id' => $customer_id,
                'branch_id' => $branch_id,
                'company_id' => $company_id,
                'user_id' => $user_id,
            ]);
        } else {
            if ($product->quantity < 1) {
                return response([
                    'message' => __('cart.outstock'),
                ], 400);
            }

            $user->cart()->attach($product->id, [
                'quantity' => 1,
                'customer_id' => $customer_id,
                'branch_id' => $branch_id,
                'company_id' => $company_id,
                'user_id' => $user_id,
            ]);
        }

        return response('', 204);
    }

    public function changeQty(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'branch_id' => 'required|integer|min:1',
            'customer_id' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $branch_id = $request->branch_id;
        $company_id = $user->company_id;

        // Verify that the branch_id matches the user's branch (for non-admin users)
        if ($user->role !== 'admin' && $branch_id != $user->branch_id) {
            return response([
                'message' => 'You can only modify items in your own branch.',
            ], 403);
        }

        $product = new Product();
        $product = $product->find($request->product_id);

        $cart = $user->cart()
            ->where('id', $request->product_id)
            ->where('user_cart.company_id', $company_id)
            ->where('user_cart.branch_id', $branch_id)
            ->first();

        if ($cart) {
            // check branch stock quantity
            $stock = BranchProductStock::where('product_id', $product->id)
                ->where('branch_id', $branch_id)
                ->first();
            if ($stock && $stock->quantity < $request->quantity) {
                return response([
                    'message' => __('cart.available', ['quantity' => $stock->quantity]),
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
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'branch_id' => 'required|integer|exists:branches,id'
        ]);

        $user = Auth::user();
        $company_id = $user->company_id;
        $branch_id = $request->branch_id;

        // Verify that the branch_id matches the user's branch (for non-admin users)
        if ($user->role !== 'admin' && $branch_id != $user->branch_id) {
            return response([
                'message' => 'You can only delete items from your own branch.',
            ], 403);
        }

        $user->cart()
            ->where('user_cart.company_id', $company_id)
            ->where('user_cart.branch_id', $branch_id)
            ->detach($request->product_id);

        return response('', 204);
    }

    public function empty(Request $request)
    {
        $user = Auth::user();
        $company_id = $user->company_id;

        $user->cart()
            ->where('user_cart.company_id', $company_id)
            ->where('user_cart.branch_id', $user->branch_id)
            ->detach();

        return response('', 204);
    }

    public function loadBranches()
    {
        $user = Auth::user();
        $company_id = $user->company_id;

        // For admin users, show all branches in their company
        // For regular users, show only their assigned branch
        if ($user->role === 'admin') {
            $branches = Branch::where('company_id', $company_id)->get();
        } else {
            $branches = Branch::where('company_id', $company_id)
                ->where('id', $user->branch_id)
                ->get();
        }

        return response()->json($branches);
    }
}
