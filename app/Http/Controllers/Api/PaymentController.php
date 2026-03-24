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
     * Verify Razorpay Payment using Polling Loop
     */
    public function verifyPayment(Request $request)
    {
        // Increase execution time for the loop (1 minute)
        set_time_limit(60);

        $orderId = $request->razorpay_order_id ?: json_decode($request->getContent(), true)['razorpay_order_id'] ?? null;

        if (!$orderId) {
            return response()->json([
                'success' => false,
                'message' => 'razorpay_order_id is required',
            ], 400);
        }

        $maxAttempts = 10; // Total 50 seconds (10 * 5s)
        $attempt = 0;

        try {
            while ($attempt < $maxAttempts) {
                $attempt++;

                // Fetch the order and its payments
                $order = $this->razorpayApi->order->fetch($orderId);
                $payments = $order->payments();

                if (count($payments['items']) > 0) {
                    // Find a successful (captured) or authorized payment
                    $razorpayPayment = collect($payments['items'])->first(function ($p) {
                        return in_array($p['status'], ['captured', 'authorized']);
                    });

                    if ($razorpayPayment) {
                        // If authorized but not captured, capture it now
                        if ($razorpayPayment['status'] === 'authorized') {
                            $razorpayPayment->capture(['amount' => $razorpayPayment['amount'], 'currency' => 'INR']);
                        }

                        $payment = Payment::where('razorpay_order_id', $orderId)->first();

                        if ($payment) {
                            $payment->update([
                                'razorpay_payment_id' => $razorpayPayment['id'],
                                'status'              => 'success',
                                'razorpay_signature'  => hash_hmac('sha256', $orderId . '|' . $razorpayPayment['id'], config('razorpay.secret')),
                            ]);

                            return response()->json([
                                'success' => true,
                                'message' => 'Payment verified and recorded successfully',
                                'razorpay_payment_id' => $razorpayPayment['id'],
                                'attempts' => $attempt
                            ]);
                        }
                    }
                }

                // Wait for 5 seconds before the next attempt
                if ($attempt < $maxAttempts) {
                    sleep(5);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment verification timed out. No successful payment found after ' . ($maxAttempts * 5) . ' seconds.',
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
