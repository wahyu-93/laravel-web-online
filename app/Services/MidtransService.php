<?php 

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey      = config('midtrans_server_key');
        Config::$clientKey      = config('midtrans_client_key');
        Config::$isProduction   = config('midtrans_is_production');
        Config::$isSanitized    = config('midtrans_is_sanitized');
        Config::$is3ds          = config('midtrans_is_3ds');
    }

    public function createSnapToken(array $params): string
    {
        try {
            return Snap::getSnapToken($params);
        } catch (\Exception $e) {
            Log::error('Failed to create snap token: ' . $e->getMessage());
            throw $e;
        }
    }

    public function handleNotification(): array
    {
        try {
            $notification = new Notification();
            return [
                'order_id'  => $notification->order_id,
                'transaction_status' => $notification->transaction_status,
                'gross_amount'  => $notification->gross_amount,
                'custom_field1' => $notification->custom_field1,
                'custom_field2' => $notification->custom_field2,
            ];
        } catch (\Exception $e) {
            Log::error('Midtransnotification error : ' . $e->getMessage());
            throw $e;
        }
    }
}