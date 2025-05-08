<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use Illuminate\Http\Request;
use App\Models\Items;
use App\Models\Transactions;
use App\Models\Transaction_Items;

class POSController extends Controller
{
    // Get Items
    public function items()
    {
        $items = Items::all();
        return response()->json([
            'items' => $items
        ], 200);
    }

    // Get Customers
    // public function customers()
    // {
    //     $customers = Customers::all();
    //     return response()->json([
    //         'customers' => $customers
    //     ], 200);
    // }

    // Get Transactions
    public function transactions()
    {
        $transactions = Transactions::with('customer')->get();

        $formatted = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'transaction_code' => $transaction->transaction_code,
                'customer_name' => $transaction->customer?->name ?? 'Walk-in',
                'hole_number' => $transaction->hole_number,
                'buggy_number' => $transaction->buggy_number,
                'players' => $transaction->players,
                'subtotal' => $transaction->subtotal,
                'tax' => $transaction->tax,
                'total' => $transaction->total,
                'payment_method' => $transaction->payment_method,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
            ];
        });

        return response()->json([
            'transactions' => $formatted
        ], 200);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'barcode' => 'nullable|string|max:255',
        ]);

        $item = Items::create($data);

        return response()->json([
            'success' => true,
            'item' => $item
        ], 201);
    }

    // Store transaction
    public function storeTransaction(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|integer',
            'hole_number' => 'nullable|integer',
            'buggy_number' => 'nullable|string',
            'players' => 'nullable|integer',
            'cart' => 'required|array',
            'payment_method' => 'required|string',
            'signature' => 'nullable|string',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'total' => 'required|numeric',
        ]);

        // First create the transaction in the transactions table
        $transaction = Transactions::create([
            'customer_id' => $data['customer_id'],
            'transaction_code' => strtoupper(uniqid('TXN-')),
            'hole_number' => $data['hole_number'],
            'buggy_number' => $data['buggy_number'],
            'players' => $data['players'],
            'subtotal' => $data['subtotal'],
            'tax' => $data['tax'],
            'total' => $data['total'],
            'payment_method' => $data['payment_method'],
            'special_request' => '',
        ]);

        // Then create the transaction items in the transaction_items table
        foreach ($data['cart'] as $item) {
            Transaction_Items::create([
                'transaction_id' => $transaction->id,
                'item_id' => $item['id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);

            // Reduce inventory
            $product = Items::find($item['id']);
            if ($product) {
                $product->stock -= $item['quantity'];
                $product->save();
            }
        }

        return response()->json(['success' => true, 'transaction_id' => $transaction->id]);
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'barcode' => 'nullable|string|max:255',
        ]);

        try {
            // Find the item
            $item = Items::findOrFail($id);

            // Update the item
            $item->update($data);

            return response()->json([
                'success' => true,
                'item' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update item',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function delete($id)
    {
        try {
            // Find the item
            $item = Items::findOrFail($id);

            // Delete the item
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete item',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
?>
