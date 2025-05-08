<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // Get all customers
    public function customers()
    {
        return Customers::all();
    }

    // Store a new customer
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'required|string|max:20',
        ]);

        $customer = Customers::create($request->only(['name', 'email', 'phone']));
        return response()->json($customer, 201);
    }

    // Update existing customer
    public function update(Request $request, $id)
    {
        $customer = Customers::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'required|string|max:20',
        ]);

        $customer->update($request->only(['name', 'email', 'phone']));
        return response()->json($customer, 200);
    }

    // Delete a customer
    public function delete($id)
    {
        $customer = Customers::findOrFail($id);
        $customer->delete();

        return response()->json(['message' => 'Customer deleted'], 200);
    }

    // public function show($id)
    // {
    //     return Customers::findOrFail($id);
    // }
}

?>
