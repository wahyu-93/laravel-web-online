<?php

namespace App\Helper;

use App\Models\Transaction;

class TransactionHelper 
{
   public static function generateUniqueId(): string
   {
        $prefix = 'TRN';

        do {
            $randomString = $prefix . mt_rand(10000,99999);
        } while (Transaction::where('booking_trx_id', $randomString)->exists());

        return $randomString;
   }
}