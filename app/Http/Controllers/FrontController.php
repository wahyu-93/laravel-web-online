<?php

namespace App\Http\Controllers;

use App\Models\Pricing;
use App\Services\PaymentService;
use App\Services\PricingService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FrontController extends Controller
{
    protected $transactionService;
    protected $paymentService;
    protected $pricingService;

    public function __construct(PaymentService $paymentService, TransactionService $transactionService, PricingService $pricingService)
    {
        $this->paymentService = $paymentService;
        $this->transactionService = $transactionService;
        $this->pricingService = $pricingService;
    }

    public function index()
    {
        dd('adadadad');
        return view('front.index');
    }

    public function pricing()
    {
        $pricing_packages = $this->pricingService->getAllPackages();
        $user = Auth::user();

        return view('front.pricing', compact('pricng_packages', 'user'));
    }

    public function checkout(Pricing $pricing)
    {
        $checkoutData = $this->transactionService->prepareCheckout($pricing);
        
        if($checkoutData['alreadySubscribed']) {
            return redirect()->route('front.pricing')->with('error', 'You are already subscribed to this plan');
        };

        return view('font.checkout', $checkoutData);
    }

    public function paymentStoreMidtrans()
    {
        try {
            $pricingId = session()->get('pricing_id');

            if(!$pricingId){
                return response()->json(['error' => 'No pricing data not found in the session.'], 400);
            };

            $snapToken = $this->paymentService->createPayment($pricingId);

            if(!$snapToken){
                return response()->json(['error' => 'Failed to create Midtrans transaction']);
            };   

        } catch (\Exception $e) {
            return response()->json(['error' => 'Payment Failed : ' . $e->getMessage()], 500);
        }
    }

    public function PaymentMidtransNotifications(Request $request)
    {
        try {
            // Process the Midtrans notification through the service
            $transactionStatus = $this->paymentService->handlePaymentNotification();

            if (!$transactionStatus) {
                return response()->json(['error' => 'Invalid notification data.'], 400);
            }

            // Respond with the status of the transaction
            return response()->json(['status' => $transactionStatus]);
        } catch (\Exception $e) {
            Log::error('Failed to handle Midtrans notification:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to process notification.'], 500);
        }
    }

    public function checkoutSuccess()
    {
        $pricing = $this->transactionService->getRecentPricing();

        if(!$pricing){
            return redirect()->route('front.pricing')->with('error', 'No recent subscribed found.');
        };

        return view('front.checkout_success', compact('pricing'));
    }
}
