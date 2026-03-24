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
     * Verify Razorpay Payment using Order ID only
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
        ]);

        try {
            // Fetch all payments for this order from Razorpay
            $payments = $this->razorpayApi->order->fetch($request->razorpay_order_id)->payments();

            if (count($payments['items']) > 0) {
                // Get the first successful payment
                $razorpayPayment = collect($payments['items'])->firstWhere('status', 'captured');

                if ($razorpayPayment) {
                    $payment = Payment::where('razorpay_order_id', $request->razorpay_order_id)->first();

                    if ($payment) {
                        $payment->update([
                            'razorpay_payment_id' => $razorpayPayment['id'],
                            'status'              => 'success',
                            // Generate and save signature internally for record-keeping
                            'razorpay_signature'  => hash_hmac('sha256', $request->razorpay_order_id . '|' . $razorpayPayment['id'], config('razorpay.secret')),
                        ]);

                        return response()->json([
                            'success' => true,
                            'message' => 'Payment verified and recorded successfully',
                            'razorpay_payment_id' => $razorpayPayment['id']
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'No successful payment found for this order ID',
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
