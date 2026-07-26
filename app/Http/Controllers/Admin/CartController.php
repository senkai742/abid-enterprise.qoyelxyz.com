<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\BranchProductStock;

class CartController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $cart = $request->user()->cart->each(function ($product) {
                $customer = Customer::find($product->pivot->customer_id);
                $product->pivot->user_balance = $customer?->balance ?? 0;
            });
            return response()->json($cart);
        }

        $user = auth()->user();
        $branch_id = $user->branch_id;

        // Load customers
        $customers = Customer::select('id', 'first_name', 'last_name', 'address', 'phone', 'balance')
            ->where('company_id', $user->company_id)
            ->get();

        // Load products with branch stock
        $productsQuery = Product::where('company_id', $user->company_id)
            ->with(['branchStocks' => function($query) use ($branch_id) {
                $query->where('branch_id', $branch_id);
            }]);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $productsQuery->where('name', 'like', "%{$search}%");
        }

        $products = $productsQuery->get()->map(function($product) use ($branch_id) {
            $branchStock = $product->branchStocks->first();
            $product->available_stock = $branchStock ? $branchStock->quantity : 0;
            return $product;
        });

        // Load cart items with customer info
        $cart = $request->user()->cart->each(function ($product) {
            $customer = Customer::find($product->pivot->customer_id);
            $product->pivot->user_balance = $customer?->balance ?? 0;
        });

        // Load branches for admin
        $branches = \App\Models\Branch::where('company_id', $user->company_id)->get();
        $settings = \App\Models\Setting::where('company_id', $user->company_id)->get();
    
        // Load salesmen (users with role != admin)
        $salesmen = [];
        if ($user->role === 'admin') {
            // Admin can see all non-admin users
            $salesmen = \App\Models\User::where('role', '!=', 'admin')
                ->select('id', 'first_name', 'last_name')
                ->get();
        } else {
            // Non-admin users see users from same company
            $salesmen = \App\Models\User::where('company_id', $user->company_id)
                ->where('role', '!=', 'admin')
                ->select('id', 'first_name', 'last_name')
                ->get();
        }

        return view('admin.cart.index', compact('branch_id', 'customers', 'products', 'cart', 'branches', 'salesmen','settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barcode' => 'required|exists:products,barcode',
            'customer_id' => 'required|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $barcode = $request->barcode;
        $customer_id = $request->customer_id;
        $branch_id = $request->branch_id;
        $user = Auth::user();
        $company_id = $user->company_id;
        $user_id = $user->id;

        $product = Product::where('barcode', $barcode)->where('products.company_id', $company_id)->first();

        if (!$product) {
            return response([
                'message' => 'Product not found or not available for your company.',
            ], 404);
        }

        $cart = $request->user()->cart()
            ->where('barcode', $barcode)
            ->where('user_cart.branch_id', $branch_id)
            ->where('user_cart.company_id', $company_id)
            ->first();

        $stock = BranchProductStock::where('product_id', $product->id)
            ->where('branch_id', $branch_id)
            ->first();

        if ($cart) {
            // Product already in cart - update quantity
            $newQuantity = $cart->pivot->quantity + 1;

            // check branch stock quantity
            // if ($stock && $stock->quantity < $newQuantity) {
            //     return response([
            //         'message' => __('cart.available', ['quantity' => $stock->quantity]),
            //     ], 400);
            // }

            $cart->pivot->quantity = $newQuantity;
            $cart->pivot->save();
            
        } else {
            // Product not in cart - add it
            // if (!$stock || $stock->quantity < 1) {
            //     return response([
            //         'message' => __('cart.outstock'),
            //     ], 400);
            // }

            $request->user()->cart()->attach($product->id, [
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

        $branch_id = $request->branch_id;
        $company_id = Auth::user()->company_id;
        $user_id = Auth::id();

        $product = Product::find($request->product_id);
        $cart = $request->user()->cart()
            ->where('id', $request->product_id)
            ->where('user_cart.company_id', $company_id)
            ->where('user_cart.branch_id', $branch_id)
            ->first();

        $stock = BranchProductStock::where('product_id', $product->id)
            ->where('branch_id', $branch_id)
            ->first();

        if ($cart) {
            // check branch stock quantity
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

        $company_id = Auth::user()->company_id;

        $request->user()->cart()
            ->where('user_cart.company_id', $company_id)
            ->where('user_cart.branch_id', $request->branch_id)
            ->detach($request->product_id);

        return response('', 204);
    }

    public function empty(Request $request)
    {
        $company_id = Auth::user()->company_id;

        $request->user()->cart()
            ->where('user_cart.company_id', $company_id)
            ->detach();

        return response('', 204);
    }
}
