<?php

namespace App\Services\Mlm;

use App\Models\OrderProduct\Order;

class PairingEngine
{
    /**
     * Contoh minimal: anggap PV = base amount,
     * berikan pairing ke sponsor langsung saja (placeholder).
     * Silakan ganti sesuai skema binari Anda (left/right accumulation).
     *
     * @return array<int, array{member_id:int,left_pv:float,right_pv:float,paid_pairs:int,bonus_amount:float}>
     */
    public function consumePVForOrder(Order $order, float $baseAmount): array
    {
        $percent = (float) config('mlm.pairing_percent', 10.0);
        $svc = app(NetworkService::class);
        $sponsorId = $svc->getSponsorId($order->member_id);

        if (!$sponsorId || $baseAmount <= 0) {
            return [];
        }

        // Placeholder sederhana: treat semua PV di satu sisi dan dibayar 1 pair
        $left = $baseAmount;
        $right = $baseAmount;
        $pairs = 1;
        $bonus = ($left > 0 && $right > 0) ? ($baseAmount * ($percent/100)) : 0;

        return [[
            'member_id'    => (int) $sponsorId,
            'left_pv'      => $left,
            'right_pv'     => $right,
            'paid_pairs'   => $pairs,
            'bonus_amount' => $bonus,
        ]];
    }
}
