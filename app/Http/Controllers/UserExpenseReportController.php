<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Salesreturn;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\PurchaseReturn;
use App\Models\DamageItem;
use App\Models\Payment;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;

class UserExpenseReportController extends Controller
{
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
        return view('user.expense.sales-details', compact('sales', 'totalSales', 'returnsTotal', 'netSales', 'startDate', 'endDate'));
    }

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
        return view('user.expense.profit-details', [
            'orders' => $sales,
            'totalProfit' => $totalProfit,
            'returnsProfit' => $returnsProfit,
            'purchaseReturnsProfit' => $purchaseReturnsProfit,
            'damagesTotal' => $damagesTotal,
            'netProfit' => $netProfit,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

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
                return $carry + ($item->sell_price * $item->quantity);
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
        return view('user.expense.cash-details', compact(
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

    public function damageDetails(Request $request)
    {
        if ($request->start_date && $request->end_date) {
            $startDate = $request->start_date . ' 00:00:00';
            $endDate = $request->end_date . ' 23:59:59';
        } else {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        }
        $damages = DamageItem::whereBetween('created_at', [$startDate, $endDate])
            ->with('product')
            ->latest()
            ->paginate(15);
        return view('user.expense.damage-details', compact('damages', 'startDate', 'endDate'));
    }
}
