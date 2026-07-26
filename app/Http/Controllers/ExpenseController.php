<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Salesreturn;
use App\Models\DamageItem;
use App\Models\Expense;
use App\Models\ExpenseHead;
use App\Models\Purchase;
use App\Models\Payment;
use App\Models\SupplierPayment;
use App\Models\PurchaseReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = new Expense();

        // Get expense heads without global scope to include legacy records
        $expense_heads = ExpenseHead::withoutGlobalScope('branch')->get();

        // Filter by current user's company and branch if they exist
        $user = Auth::user();
        if ($user && $user->company_id) {
            if ($user->role === 'admin') {
                $expense_heads = $expense_heads->where('company_id', $user->company_id);
            } else {
                $expense_heads = $expense_heads->where('company_id', $user->company_id)
                    ->where('branch_id', $user->branch_id);
            }
        }

        // Apply filters
        if ($request->start_date) {
            $expenses = $expenses->where('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $expenses = $expenses->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        if ($request->expense_head) {
            $expenses = $expenses->where('expense_head', $request->expense_head);
        }

        $expenses = $expenses->latest()->paginate(10);

        $total = $expenses->sum('expense_amount');

        // --- Report Tab Calculations ---
        // Use date range from request, or default to current month
        if ($request->report_start_date && $request->report_end_date) {
            $reportStartDate = $request->report_start_date . ' 00:00:00';
            $reportEndDate = $request->report_end_date . ' 23:59:59';
        } else {
            $reportStartDate = now()->startOfMonth();
            $reportEndDate = now()->endOfMonth();
        }

        // Total Sales (sum of all order items' sell_price * quantity for orders created in date range)
        $totalSales = \App\Models\Sale::whereBetween('created_at', [$reportStartDate, $reportEndDate])
            ->with('items')
            ->get()
            ->flatMap(function ($sale) {
                return $sale->items;
            })
            ->reduce(function ($carry, $item) {
                return $carry + ($item->sell_price * $item->quantity);
            }, 0);

        // Total Purchase (sum of all purchases gr_total for date range)
        $totalPurchase = \App\Models\Purchase::whereBetween('created_at', [$reportStartDate, $reportEndDate])
            ->sum('gr_total');

        // Total Expenses (sum of all expenses for date range)
        $totalExpenses = \App\Models\Expense::whereBetween('created_at', [$reportStartDate, $reportEndDate])
            ->sum('expense_amount');

        // Total Profit (sum of all orders' profit_amount for date range)
        $totalProfit = \App\Models\Sale::whereBetween('created_at', [$reportStartDate, $reportEndDate])
            ->sum('profit_amount');

        // Sales Returns (for date range)
        $returnsTotal = \App\Models\Salesreturn::whereBetween('created_at', [$reportStartDate, $reportEndDate])
            ->sum('total_amount');
        $returnsProfit = \App\Models\Salesreturn::whereBetween('created_at', [$reportStartDate, $reportEndDate])
            ->sum('profit_amount');

        // Purchase Returns (for date range)
        $purchaseReturnsTotal = PurchaseReturn::whereBetween('created_at', [$reportStartDate, $reportEndDate])
            ->sum('total_amount');
        $purchaseReturnsProfit = PurchaseReturn::whereBetween('created_at', [$reportStartDate, $reportEndDate])
            ->sum('profit_amount');

        // Damages (for date range)
        $damagesTotal = DamageItem::whereBetween('created_at', [$reportStartDate, $reportEndDate])
            ->sum(DB::raw('purchase_price * qnty'));

        // Net values after returns, purchase returns, and damages
        $netSales = $totalSales - $returnsTotal - $purchaseReturnsTotal;
        $netProfit = $totalProfit - $returnsProfit - $purchaseReturnsProfit - $damagesTotal;
        $cashInHand = $netSales - $totalPurchase - $totalExpenses - $damagesTotal;

        // Total purchase due (unpaid/partially paid purchases)
        $totalPurchaseDue = \App\Models\Purchase::whereBetween('created_at', [$reportStartDate, $reportEndDate])
            ->whereColumn('paid_amount', '<', 'gr_total')
            ->sum(DB::raw('gr_total - paid_amount'));

        $viewPath = Auth::user()->role === 'admin' ? 'admin.expense.index' : 'user.expense.index';
        return view($viewPath, compact(
            'expenses',
            'expense_heads',
            'total',
            'totalSales',
            'totalPurchase',
            'totalExpenses',
            'totalProfit',
            'cashInHand',
            'returnsTotal',
            'returnsProfit',
            'purchaseReturnsTotal',
            'purchaseReturnsProfit',
            'damagesTotal',
            'netSales',
            'netProfit',
            'reportStartDate',
            'reportEndDate',
            'totalPurchaseDue'
        ));
    }


    public function create(Request $request)
    {
        // Get expense heads without global scope to include legacy records
        $expense_heads = ExpenseHead::withoutGlobalScope('branch')->get();

        // Filter by current user's company and branch if they exist
        $user = Auth::user();
        if ($user && $user->company_id) {
            if ($user->role === 'admin') {
                $expense_heads = $expense_heads->where('company_id', $user->company_id);
            } else {
                $expense_heads = $expense_heads->where('company_id', $user->company_id)
                    ->where('branch_id', $user->branch_id);
            }
        }

        $viewPath = Auth::user()->role === 'admin' ? 'admin.expense.create' : 'user.expense.create';
        return view($viewPath, compact('expense_heads'));
    }

    public function createExpenseHead(Request $request)
    {
        // Get expense heads without global scope to include legacy records
        $expense_heads = ExpenseHead::withoutGlobalScope('branch')->get();

        // Filter by current user's company and branch if they exist
        $user = Auth::user();
        if ($user && $user->company_id) {
            if ($user->role === 'admin') {
                $expense_heads = $expense_heads->where('company_id', $user->company_id);
            } else {
                $expense_heads = $expense_heads->where('company_id', $user->company_id)
                    ->where('branch_id', $user->branch_id);
            }
        }

        $viewPath = Auth::user()->role === 'admin' ? 'admin.expense.create-head' : 'user.expense.create-head';
        return view($viewPath, compact('expense_heads'));
    }

    public function storeExpenseHead(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'expense_head' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_heads')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                }),
            ],
        ]);

        $expense_head = new ExpenseHead();
        $expense_head->expense_head = $request->expense_head;
        $expense_head->user_id = $user->id;
        $expense_head->company_id = $user->company_id;
        $expense_head->branch_id = $user->branch_id;
        $expense_head->save();

        $routeName = $user->role === 'admin' ? 'admin.expense.head.create' : 'user.expense.head.create';
        return redirect()->route($routeName)->with('success', 'Expense head created successfully!');
    }

    public function deleteExpenseHead($exp_head_id)
    {
        $user = Auth::user();

        // Find expense head and ensure it belongs to the user's company
        $expense_head = ExpenseHead::where('id', $exp_head_id)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        $expense_head->delete();

        $routeName = $user->role === 'admin' ? 'admin.expense.head.create' : 'user.expense.head.create';
        return redirect()->route($routeName)->with('success', 'Expense head deleted successfully!');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $expense = Expense::create([
            'expense_head' => $request->expense_head,
            'expense_description' => $request->expense_description,
            'expense_amount' => $request->expense_amount,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'branch_id' => $user->branch_id,
        ]);

        $routeName = $user->role === 'admin' ? 'admin.expense.index' : 'user.expense.index';
        return redirect()->route($routeName)->with('success', 'Expense saved successfully!');
    }




    public function show(DamageItem $damage)
    {
        // Removed dd('show') debug statement
    }

    public function destroy(DamageItem $damage)
    {
        $damage->delete();
        return back()->with('success', 'Damage deleted successfully');
    }

    // Sales Details Page
    public function salesDetails(Request $request)
    {
        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date . ' 00:00:00';
            $endDate = $request->end_date . ' 23:59:59';
        } else {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        }
        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->with(['items.product', 'customer'])
            ->latest()
            ->paginate(15);
        $totalSales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->with('items')
            ->get()
            ->flatMap(function ($sale) {
                return $sale->items;
            })
            ->reduce(function ($carry, $item) {
                return $carry + ($item->sell_price);
            }, 0);
        $returnsTotal = Salesreturn::whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');
        $netSales = $totalSales - $returnsTotal;
        $viewPath = Auth::user()->role === 'admin' ? 'admin.expense.sales-details' : 'user.expense.sales-details';
        return view($viewPath, compact('sales', 'totalSales', 'returnsTotal', 'netSales', 'startDate', 'endDate'));
    }

    // Purchase Details Page
    public function purchaseDetails(Request $request)
    {
        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date . ' 00:00:00';
            $endDate = $request->end_date . ' 23:59:59';
        } else {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        }
        $purchases = Purchase::whereBetween('created_at', [$startDate, $endDate])
            ->with(['items.product', 'supplier'])
            ->latest()
            ->paginate(15);
        $totalPurchase = Purchase::whereBetween('created_at', [$startDate, $endDate])
            ->sum('gr_total');
        return view('admin.expense.purchase-details', compact('purchases', 'totalPurchase', 'startDate', 'endDate'));
    }

    // Expense Details Page
    public function expenseDetails(Request $request)
    {
        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date . ' 00:00:00';
            $endDate = $request->end_date . ' 23:59:59';
        } else {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        }
        $expenses = Expense::whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->paginate(15);
        $totalExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])
            ->sum('expense_amount');
        $viewPath = Auth::user()->role === 'admin' ? 'admin.expense.expense-details' : 'user.expense.expense-details';
        return view($viewPath, compact('expenses', 'totalExpenses', 'startDate', 'endDate'));
    }

    // Profit Details Page
    public function profitDetails(Request $request)
    {
        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date . ' 00:00:00';
            $endDate = $request->end_date . ' 23:59:59';
        } else {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        }
        $sales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->with(['items.product', 'customer'])
            ->latest()
            ->paginate(15);
        $totalProfit = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->sum('profit_amount');
        $returnsProfit = Salesreturn::whereBetween('created_at', [$startDate, $endDate])
            ->sum('profit_amount');
        $purchaseReturnsProfit = PurchaseReturn::whereBetween('created_at', [$startDate, $endDate])
            ->sum('profit_amount');
        $damagesTotal = DamageItem::whereBetween('created_at', [$startDate, $endDate])
            ->sum(DB::raw('purchase_price * qnty'));
        $netProfit = $totalProfit - $returnsProfit - $purchaseReturnsProfit - $damagesTotal;
        return view('admin.expense.profit-details', compact('sales', 'totalProfit', 'returnsProfit', 'purchaseReturnsProfit', 'damagesTotal', 'netProfit', 'startDate', 'endDate'));
    }

    // Cash Details Page
    public function cashDetails(Request $request)
    {
        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date . ' 00:00:00';
            $endDate = $request->end_date . ' 23:59:59';
        } else {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        }
        $totalSales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->with('items')
            ->get()
            ->flatMap(function ($sale) {
                return $sale->items;
            })
            ->reduce(function ($carry, $item) {
                return $carry + ($item->sell_price);
            }, 0);
        $totalPurchase = Purchase::whereBetween('created_at', [$startDate, $endDate])
            ->sum('gr_total');
        $totalExpenses = Expense::whereBetween('created_at', [$startDate, $endDate])
            ->sum('expense_amount');
        $returnsTotal = Salesreturn::whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');
        $purchaseReturnsTotal = PurchaseReturn::whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');
        $damagesTotal = DamageItem::whereBetween('created_at', [$startDate, $endDate])
            ->sum(DB::raw('purchase_price * qnty'));
        $netSales = $totalSales - $returnsTotal - $purchaseReturnsTotal;
        $cashInHand = $netSales - $totalPurchase - $totalExpenses - $damagesTotal;
        $customerPayments = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->with(['sale.customer'])
            ->latest()
            ->paginate(15);
        $supplierPayments = SupplierPayment::whereBetween('created_at', [$startDate, $endDate])
            ->with(['purchase.supplier'])
            ->latest()
            ->paginate(15);
        return view('admin.expense.cash-details', compact(
            'totalSales',
            'totalPurchase',
            'totalExpenses',
            'returnsTotal',
            'purchaseReturnsTotal',
            'damagesTotal',
            'netSales',
            'cashInHand',
            'customerPayments',
            'supplierPayments',
            'startDate',
            'endDate'
        ));
    }
}
