<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Payment;
use App\Models\SupplierPayment;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\BranchProductStock;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $company_id = $user->company_id;
        $branch_id = $user->branch_id;

        $today_sales = Sale::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->with('items')
            ->get()
            ->flatMap(function ($order) {
                return $order->items;
            })
            ->reduce(function ($carry, $item) {
                return $carry + ($item->sell_price);
            }, 0);
        $customer_payment = Payment::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->sum('amount');

        $supplier_payment = SupplierPayment::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->sum('amount');

        $today_purchase = Purchase::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->sum('gr_total');

        $today_purchase_due = Purchase::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->whereColumn('paid_amount', '<', 'gr_total')
            ->sum(DB::raw('gr_total - paid_amount'));

        $today_profit = Sale::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->sum('profit_amount');

        $orders = Sale::with(['items', 'payments'])->get();
        $customers_count = Customer::count();

        $low_stock_products = BranchProductStock::where('branch_id', $branch_id)
            ->where('quantity', '<', 20)
            ->with('product')
            ->get();

        $bestSellingProducts = DB::table('branch_product_stock')
            ->join('products', 'branch_product_stock.product_id', '=', 'products.id')
            ->join('branches', 'branch_product_stock.branch_id', '=', 'branches.id')
            ->where('branch_product_stock.branch_id', $branch_id)
            ->where('branches.company_id', $company_id)
            ->orderByDesc('branch_product_stock.quantity')
            ->limit(10)
            ->select('products.*', 'branch_product_stock.quantity as branch_quantity')
            ->get();





        $currentMonthBestSelling = DB::table('products')
            ->where('products.company_id', $company_id)
            ->where('products.branch_id', $branch_id)
            ->joinSub(
                DB::table('sale_items')
                    ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                    ->select('sale_items.product_id', DB::raw('SUM(sale_items.quantity) as total_sold'))
                    ->whereYear('sales.created_at', date('Y'))
                    ->whereMonth('sales.created_at', date('m'))
                    ->groupBy('sale_items.product_id')
                    ->having('total_sold', '>', 500),
                'totals',
                'products.id',
                '=',
                'totals.product_id'
            )
            ->select('products.*', 'totals.total_sold')
            ->get();





        return view('user.dashboard', [
            'orders_count' => $orders->count(),
            'income' => $orders->map(function ($i) {
                return $i->receivedAmount() > $i->total() ? $i->total() : $i->receivedAmount();
            })->sum(),
            'income_today' => $orders->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')->map(function ($i) {
                return $i->receivedAmount() > $i->total() ? $i->total() : $i->receivedAmount();
            })->sum(),
            'customers_count' => $customers_count,
            'low_stock_products' => $low_stock_products,
            'best_selling_products' => $bestSellingProducts,
            'current_month_products' => $currentMonthBestSelling,

            'payment_customer_today' => $customer_payment,
            'payment_supplier_today' => $supplier_payment,
            'today_sales' => $today_sales,
            'today_purchase' => $today_purchase,
            'today_purchase_due' => $today_purchase_due,
            'today_profit' => $today_profit,
        ]);
    }
}
