<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Exception;
use Illuminate\Support\Facades\Log;

class DonationController extends Controller
{
    private $razorpayApi;

    public function __construct()
    {
        $this->razorpayApi = new Api(
            config('razorpay.key'),
            config('razorpay.secret')
        );
    }

    public function index()
    {
        return view('donation');
    }

    /**
     * Create a Razorpay Order
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'donation_type' => 'nullable|string',
        ]);

        try {
            $amountInPaisa = $request->amount * 100;

            $orderData = [
                'receipt'         => 'rcpt_' . uniqid(),
                'amount'          => $amountInPaisa,
                'currency'        => 'INR',
            ];

            $razorpayOrder = $this->razorpayApi->order->create($orderData);

            Payment::create([
                'amount'            => $amountInPaisa,
                'currency'          => 'INR',
                'razorpay_order_id' => $razorpayOrder['id'],
                'status'            => 'pending',
                'donation_type'     => $request->donation_type,
            ]);

            return response()->json([
                'success' => true,
                'order_id' => $razorpayOrder['id'],
                'amount' => $amountInPaisa,
                'key' => config('razorpay.key'),
            ]);

        } catch (Exception $e) {
            Log::error('Razorpay Order Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Could not create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify Razorpay Payment
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature' => 'required',
        ]);

        try {
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ];

            $this->razorpayApi->utility->verifyPaymentSignature($attributes);

            $payment = Payment::where('razorpay_order_id', $request->razorpay_order_id)->first();

            if ($payment) {
                $payment->update([
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'status'              => 'success',
                    'razorpay_signature'  => $request->razorpay_signature,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment record not found',
            ], 404);

        } catch (Exception $e) {
            Log::error('Razorpay Verification Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage(),
            ], 400);
        }
    }
}
