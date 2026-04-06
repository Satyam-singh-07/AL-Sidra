<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['user', 'paymentable']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('razorpay_order_id', 'like', '%' . $request->search . '%')
                    ->orWhere('razorpay_payment_id', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($uq) use ($request) {
                        $uq->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('donation_type')) {
            $query->where('donation_type', $request->donation_type);
        }

        $payments = $query->latest()->paginate(15);

        $stats = [
            'total_amount' => Payment::where('status', 'success')->sum('amount') / 100,
            'total_count' => Payment::count(),
            'success_count' => Payment::where('status', 'success')->count(),
            'pending_count' => Payment::where('status', 'pending')->count(),
        ];

        return view('admin.payments', compact('payments', 'stats'));
    }

    public function show(Payment $payment)
    {
        $payment->load(['user', 'paymentable']);
        return view('admin.payments-show', compact('payment'));
    }
}
