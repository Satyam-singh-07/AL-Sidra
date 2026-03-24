<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private $razorpayApi;

    public function __construct()
    {
        $this->razorpayApi = new Api(
            config('razorpay.key'),
            config('razorpay.secret')
        );
    }

    /**
     * Create a Razorpay Order
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'paymentable_id' => 'nullable|integer',
            'paymentable_type' => 'nullable|string',
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
                'user_id'           => auth()->id(),
                'amount'            => $amountInPaisa,
                'currency'          => 'INR',
                'razorpay_order_id' => $razorpayOrder['id'],
                'status'            => 'pending',
                'paymentable_id'    => $request->paymentable_id,
                'paymentable_type'  => $request->paymentable_type,
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
     * Verify Razorpay Payment Signature
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
        ]);

        try {
            $secret = config('razorpay.secret');
            
            // Step 1: Generate signature ourselves on the backend
            $signatureData = $request->razorpay_order_id . '|' . $request->razorpay_payment_id;
            $generatedSignature = hash_hmac('sha256', $signatureData, $secret);

            $payment = Payment::where('razorpay_order_id', $request->razorpay_order_id)->first();

            if ($payment) {
                $payment->update([
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature'  => $generatedSignature,
                    'status'              => 'success',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment recorded successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment record not found',
            ], 404);

        } catch (Exception $e) {
            Log::error('Razorpay Verification Error: ' . $e->getMessage());
            
            Payment::where('razorpay_order_id', $request->razorpay_order_id)
                ->update(['status' => 'failed']);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage(),
            ], 400);
        }
    }
}
