<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerStoreRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // If it's a JSON request (from React component), return customers using global scope
        if ($request->wantsJson()) {
            $customers = new Customer();
            $customers = $customers->select('id', 'first_name', 'last_name', 'address', 'phone', 'balance')->get();
            return response()->json($customers);
        }

        // For regular view requests, return paginated customers using global scope
        $customers = new Customer();
        $customers = $customers->latest()->paginate(10);
        $viewPath = $user->role === 'admin' ? 'admin.customers.index' : 'user.customers.index';
        return view($viewPath, compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $viewPath = Auth::user()->role === 'admin' ? 'admin.customers.create' : 'user.customers.create';
        return view($viewPath);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CustomerStoreRequest $request)
    {
        $data = $request->validated();
        $data['company_id'] = Auth::user()->company_id;
        $data['branch_id'] = Auth::user()->branch_id;
        $data['user_id'] = Auth::id();
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $customer = Customer::create($data);
        $routeName = Auth::user()->role === 'admin' ? 'admin.customers.index' : 'user.customers.index';
        return redirect()->route($routeName)->with('success', 'Customer saved successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        $viewPath = Auth::user()->role === 'admin' ? 'admin.customers.show' : 'user.customers.show';
        return view($viewPath, compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        $viewPath = Auth::user()->role === 'admin' ? 'admin.customers.edit' : 'user.customers.edit';
        return view($viewPath, compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(CustomerStoreRequest $request, Customer $customer)
    {
        $customer->update($request->validated());
        $routeName = Auth::user()->role === 'admin' ? 'admin.customers.index' : 'user.customers.index';
        return redirect()->route($routeName)->with('success', 'Customer updated successfully!');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        $routeName = Auth::user()->role === 'admin' ? 'admin.customers.index' : 'user.customers.index';
        return redirect()->route($routeName)->with('success', 'Customer deleted successfully!');
    }
}
