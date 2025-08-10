<?php

namespace App\Services;

use App\Helper\TransactionHelper;
use App\Models\Pricing;
use App\Models\User;
use App\Repositories\PricingRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\TransactionRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $midtransService;
    protected $pricingRepository;
    protected $transactionRepository;

    public function __construct(MidtransService $midtransService, PricingRepository $pricingRepository, TransactionRepositoryInterface $transactionRepository)
    {
        $this->midtransService = $midtransService;
        $this->pricingRepository = $pricingRepository;
        $this->$transactionRepository = $transactionRepository;
    }

    public function createPayment(int $pricingId)
    {
        // cek user dan cek pricing yg di pilih
        $user = Auth::user();

        // cara biasa
        $pricing = Pricing::findOrFail($pricingId);

        // menggunakan repository
        // $pricing = $this->pricingRepository->findById($pricingId);

        $tax = 0.11;
        $totalTax = $pricing->price * $tax;
        $grandTotal = $pricing->price + $totalTax;

        // midtrans params
        $params = [
            'transaction_detail' => [
                'order_id' => TransactionHelper::generateUniqueId(),
                'grass_amount' => (int) $grandTotal,
            ],
            'customer_details' => [
                'first_name' => $user->name, 
                'email' => $user->phone,
                'phone' => '082353089050',
            ],
            'item_details' => [
                [
                    'id' => $pricing->id,
                    'price' => (int) $pricing->price,
                    'quantity' => '1',
                    'name' => $pricing->name,
                ],
                [
                    'id' => 'tax',
                    'price' => (int) $totalTax,
                    'quantity' => '1',
                    'name' => 'PPN 11%',
                ],
            ],
            'custom_field1' => $user->id,
            'custom_field2' => $pricingId,
        ];

        return $this->midtransService->createSnapToken($params);
    }

    protected function createTransaction(array $notification, Pricing $pricing)
    {
        $startedAt = now();
        $endedAt = $startedAt->copy()->addMonths($pricing->duration);

        $transactionData = [
            'user_id' => $notification['custom_field1'],
            'pricing_id' => $notification['custom_field2'],
            'sub_total_amount' => $pricing->price,
            'total_tax_amount' => $pricing->price * 0.11,
            'grand_total_amount' => $notification['gross_amount'],
            'payment_type' => 'Midtrans',
            'is_paid' => true,
            'booking_trx_id' => $notification['order_id'],
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
        ];

        $this->transactionRepository->create($transactionData);

        Log::info('Transaction successfully created: ' . $notification['order_id']);
    }

    public function handlePaymentNotification()
    {
        $notification = $this->midtransService->handleNotification();

        if(in_array($notification['transaction_details'],['capture','settlement'])) {
            $pricing = Pricing::findOrFail($notification['custom_field2']);
            // $pricing = $this->pricingRepository->findById($notification['custom_field2']);

            $this->createTransaction($notification, $pricing);

            return $notification['transation_status'];
        }
    }
}