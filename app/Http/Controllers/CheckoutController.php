<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;


class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        // validate request
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'email' => 'required|email',
        ]);

        // create a fake transaction id using UUID and simulate creating a checkout session with Coinbase
        $fakeCoinbaseData = [
            'amount' => $request->input('amount'),
            'email' => $request->input('email'),
            'transaction_id' => (string) Str::uuid()

        ];
        $fakePaymentUrl = "https://fake.coinbase.com/pay/{$fakeCoinbaseData['transaction_id']}";

        // Make POST request to external API
        /* commented out fake post request since it will always result in an error
        $response = Http::post('fakePaymentUrl', $fakeCoinbaseData);
        */

        // Check if request was successful
        $response_success = true; // for demo purposes, we'll just set the success status manually
        if ($response_success) {
            // Get JSON response from external API
            /* do this for the actual request
             $data = $response->json(); 
            */

            // save transaction with 'pending' status
            $transaction = Transaction::create([
                'transaction_id' => $fakeCoinbaseData['transaction_id'],
                'email' => $request->email,
                'amount' => $request->amount,
                'status' => 'pending',
            ]);

            return response()->json([
                'message' => 'Transaction successful',
                'data' => $fakeCoinbaseData,
            ]);
        } else {
            return response()->json([
                'error' => 'External API call failed',
                'status' => $response->status(),
            ], 500);
        }
        
    }

    public function webhook(Request $request)
    {

        // validate top-level fields
        $validator = Validator::make($request->all(), [
            'id' => 'required|string',
            'type' => 'required|string|in:checkout.session.completed',
            'created' => 'required|integer',
            'data' => 'required|array',
        ]);

        if ($validator->fails()) {
            Log::error('Webhook payload error:', $request->all());
            return response()->json(['error' => 'Invalid payload structure'], 400);
        }

        // get the customer's data
        $c_data = $request->data;

        // validate customer's data fields
        $dataValidator = Validator::make($c_data, [
            'id' => 'required|string',
            'amount_total' => 'required|integer|min:1',
            'transaction_id' => 'required|uuid',
            'currency' => 'required|string|size:3', // e.g. 'usd'
            'customer_email' => 'required|email',
            'payment_status' => 'required|string|in:confirmed,paid,failed',
        ]);

        if ($dataValidator->fails()) {
            Log::error('Webhook payload error:', $c_data);
            return response()->json(['error' => 'Invalid data fields'], 400);
        }

        // Check from the payload data if a related transaction exists
        $transaction = Transaction::where('transaction_id', $c_data['transaction_id'])->first();

        // update transaction if it exists
        if ($transaction) {
            $transaction->update([
                'status' => $c_data['payment_status'],
                'updated_at' => now()
            ]);
        }
        else {
            return response()->json([
                'message' => 'Error: Customer email not found.',
                'customer_email' => $c_data['customer_email']
            ]);
        }

        Log::info('Webhook payload received:', $request->all());

        return response()->json([
            'message' => 'Webhook processed!',
            'transaction_id' => $transaction['transaction_id'],
            'customer_email' => $transaction['email'],
            'status' => $transaction['status'],
        ]);
    }
}
