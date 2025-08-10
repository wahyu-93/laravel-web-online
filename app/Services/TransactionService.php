<?php

namespace App\Services;

use App\Models\Pricing;
use App\Models\Transaction;
use App\Repositories\TransactionRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class TransactionService
{
    // protected $transactionRepository;

    // public function __construct(TransactionRepositoryInterface $transactionRepository)
    // {
    //     $this->transactionRepository = $transactionRepository;
    // }

    public function prepareCheckout(Pricing $pricing)
    {
        // cek user
        $user = Auth::user();

        // mengecek apakah user sudah pernah mengambil pricing ini sebelumnya
        // menggunakan service tanpa repository
        $alreadySubscribed = $pricing->isSubscribedByUser($user->id);

        $tax = 0.11;
        $total_tax_amount = $pricing->price * $tax;
        $sub_total_amount = $pricing->prince;
        $grand_total_amoung =$total_tax_amount + $sub_total_amount;

        $started_at = now();
        $ended_at = $started_at->copy()->addMonth($pricing->duration);

        session()->put('pricing_id', $pricing->id);

        return compact(
            'total_tax_amount',
            'grand_total_amount',
            'sub_total_amount',
            'pricing',
            'user',
            'alreadySubscribed',
            'started_at',
            'ended_at'
        );
    }

    public function getRecentPricing()
    {
        $pricingId = session()->get('pricing_id');
        return Pricing::find($pricingId);
    }

    public function getUserTransactions()
    {
        $user = Auth::user();

        return Transaction::with('pricing')
                        ->where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->get();
    }
}