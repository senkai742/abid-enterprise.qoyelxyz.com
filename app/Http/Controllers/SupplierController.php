<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // If it's a JSON request (from React component), return suppliers using global scope
        if ($request->wantsJson() || $request->ajax()) {
            $suppliers = new Supplier();
            $suppliers = $suppliers->latest()->get();
            return response()->json($suppliers);
        }

        // For regular view requests, return paginated suppliers using global scope
        $suppliers = new Supplier();
        $suppliers = $suppliers->latest()->paginate(10);
        $viewPath = $user->role === 'admin' ? 'admin.suppliers.index' : 'user.suppliers.index';
        return view($viewPath, compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $viewPath = Auth::user()->role === 'admin' ? 'admin.suppliers.create' : 'user.suppliers.create';
        return view($viewPath);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $data['company_id'] = Auth::user()->company_id;
        $data['branch_id'] = Auth::user()->branch_id;
        $data['user_id'] = Auth::id();
        $supplier = Supplier::create($data);
        $routeName = Auth::user()->role === 'admin' ? 'admin.suppliers.index' : 'user.suppliers.index';
        return redirect()->route($routeName)->with('success', 'Supplier saved successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function show(Supplier $supplier)
    {
        $viewPath = Auth::user()->role === 'admin' ? 'admin.suppliers.show' : 'user.suppliers.show';
        return view($viewPath, compact('supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function edit(Supplier $supplier)
    {
        $viewPath = Auth::user()->role === 'admin' ? 'admin.suppliers.edit' : 'user.suppliers.edit';
        return view($viewPath, compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($request->all());
        $routeName = Auth::user()->role === 'admin' ? 'admin.suppliers.index' : 'user.suppliers.index';
        return redirect()->route($routeName)->with('success', 'Supplier updated successfully!');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        $routeName = Auth::user()->role === 'admin' ? 'admin.suppliers.index' : 'user.suppliers.index';
        return redirect()->route($routeName)->with('success', 'Supplier deleted successfully!');
    }

    public function showPayForm(Supplier $supplier)
    {
        $viewPath = Auth::user()->role === 'admin' ? 'admin.suppliers.pay' : 'user.suppliers.pay';
        return view($viewPath, compact('supplier'));
    }

    public function pay(Request $request, Supplier $supplier)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);
        // Paying a supplier increases the balance (debt decreases)
        $supplier->balance = $supplier->balance + $request->amount;
        $supplier->save();
        // Optionally, log the payment in SupplierPayment model
        $routeName = Auth::user()->role === 'admin' ? 'admin.suppliers.index' : 'user.suppliers.index';
        return redirect()->route($routeName)->with('success', 'Payment successful!');
    }
}
