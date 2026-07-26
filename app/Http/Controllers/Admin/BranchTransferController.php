<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\BranchTransfer;
use App\Models\BranchProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin_guard']);
    }

    public function index()
    {
        $transfers = BranchTransfer::with(['product', 'fromBranch', 'toBranch', 'admin'])->latest()->paginate(20);
        return view('admin.branch_transfer.index', compact('transfers'));
    }

    public function create()
    {
        $products = Product::all();
        $branches = Branch::all();
        return view('admin.branch_transfer.create', compact('products', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_branch_id' => 'required|exists:branches,id|different:to_branch_id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'quantity' => 'required|integer|min:1',
        ]);

        $productId = $request->product_id;
        $fromBranchId = $request->from_branch_id;
        $toBranchId = $request->to_branch_id;
        $quantity = $request->quantity;

        // Check source branch stock
        $fromStock = BranchProductStock::where('product_id', $productId)
            ->where('branch_id', $fromBranchId)
            ->first();
        if (!$fromStock || $fromStock->quantity < $quantity) {
            return back()->withErrors(['quantity' => 'Not enough stock in source branch.']);
        }

        // Decrease stock in source branch
        $fromStock->quantity -= $quantity;
        $fromStock->save();

        // Increase stock in destination branch
        $toStock = BranchProductStock::firstOrCreate(
            [
                'product_id' => $productId,
                'branch_id' => $toBranchId,
            ],
            [
                'quantity' => 0,
            ]
        );
        $toStock->quantity += $quantity;
        $toStock->save();

        // Log the transfer
        BranchTransfer::create([
            'product_id' => $productId,
            'from_branch_id' => $fromBranchId,
            'to_branch_id' => $toBranchId,
            'transferred_by' => Auth::id(),
            'quantity' => $quantity,
        ]);

        return redirect()->route('admin.branch-transfer.index')->with('success', 'Product transferred successfully!');
    }
}
