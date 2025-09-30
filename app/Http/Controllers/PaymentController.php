<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Payment::where('user_id', $request->user()->id)->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount'   => 'required|numeric|min:0',
            'currency' => 'required|string',
            'status'   => 'required|string',
            'tx_ref'   => 'required|string|unique:payments,tx_ref',
        ]);

        $payment = Payment::create([
            'user_id'  => $request->user()->id,
            'amount'   => $request->amount,
            'currency' => $request->currency,
            'status'   => $request->status,
            'tx_ref'   => $request->tx_ref,
        ]);

        return response()->json($payment, 201);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'tx_ref' => 'required|string|exists:payments,tx_ref',
        ]);

        $payment = Payment::where('tx_ref', $request->tx_ref)->first();

        // For now, just return record (later connect to real gateway)
        return response()->json($payment);
    }
}
