<?php

namespace App\Observers;

use App\Models\OrderProduct\Order;
use App\Services\Mlm\CommissionService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        $trigger = config('mlm.trigger_status', 'PAID');

        // Jalan hanya saat status berubah ke trigger (idempoten akan dijaga di service)
        if ($order->wasChanged('status') && $order->status === $trigger) {
            try {
                app(CommissionService::class)->awardForOrder($order);
            } catch (\Throwable $e) {
                Log::error('OrderObserver: gagal proses komisi', [
                    'order_id' => $order->id,
                    'msg'      => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
