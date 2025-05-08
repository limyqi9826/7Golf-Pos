<?php
namespace App\Http\Controllers;
use App\Models\Transaction_Items;
use Illuminate\Http\Request;
use App\Models\Transactions;
use App\Models\CardPayment;
use App\Models\Customer;

class ReportController extends Controller
{
    public function transactionitems($id)
    {
        try {
            $transaction = Transactions::with('customer')->findOrFail($id);

            $transactionItems = Transaction_Items::where('transaction_id', $id)
                ->select('id', 'item_id', 'quantity', 'unit_price', 'total_price', 'created_at')
                ->with('item:id,name as item_name')
                ->get();

            return response()->json([
                'success' => true,
                'transaction' => [
                    'transaction_code' => $transaction->transaction_code,
                    'customer_name' => $transaction->customer?->name ?? 'Walk-in',
                    'created_at' => $transaction->created_at,
                    'total' => $transaction->total,
                    'subtotal' => $transaction->subtotal,
                    'tax' => $transaction->tax,
                    'payment_method' => $transaction->payment_method,
                    'hole_number' => $transaction->hole_number,
                    'buggy_number' => $transaction->buggy_number,
                    'players' => $transaction->players
                ],
                'data' => $transactionItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_id' => $item->item_id,
                        'item_name' => $item->item->item_name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'created_at' => $item->created_at
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction items not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function deleteTransaction($id)
    {
        try {
            $transaction = Transactions::findOrFail($id);

            // If it's a card payment, delete card payment details first
            if ($transaction->payment_method === 'Credit/Debit Card') {
                // Delete card payment record if exists
                CardPayment::where('transaction_id', $id)->delete();
            }

            // Delete associated transaction items first
            Transaction_Items::where('transaction_id', $id)->delete();

            // Then delete the transaction
            $transaction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
?>
