<?php

namespace App\Http\Controllers;

use App\Models\CardPayment;
use Illuminate\Http\Request;

class CardController extends Controller
{
    // Store Card Payment Details
    public function store(Request $request)
    {
        // Validate the incoming data
        $validated = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'card_last_four' => 'required|digits:4',
            'card_network' => 'required|string|max:20',
        ]);

        // Create the card payment record
        $cardPayment = CardPayment::create([
            'transaction_id' => $validated['transaction_id'],
            'card_last_four' => $validated['card_last_four'],
            'card_network' => $validated['card_network'],
        ]);

        // Return a response indicating success
        return response()->json([
            'message' => 'Card payment details stored successfully',
            'data' => $cardPayment,
        ]);
    }

    // Get Card Payment Details by Transaction ID
    public function show($transactionId)
    {
        // Find the card payment details by transaction ID
        $cardPayment = CardPayment::where('transaction_id', $transactionId)->first();

        if ($cardPayment) {
            return response()->json([
                'message' => 'Card payment details found',
                'data' => $cardPayment,
            ]);
        } else {
            return response()->json([
                'message' => 'Card payment details not found',
            ], 404);
        }
    }
}
?>
