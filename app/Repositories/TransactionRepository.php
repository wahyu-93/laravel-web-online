<?php

namespace App\Repositories;

use App\Models\Transaction;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function findBookingId(string $bookingId)
    {
        return Transaction::where('booking_trx_id', $bookingId)->first();
    }
    
    public function create(array $data)
    {
        return Transaction::create($data);
    }

    public function getUserTransactions(int $userId)
    {
        return Transaction::with('pricing')
                    ->where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->get();
    }
}