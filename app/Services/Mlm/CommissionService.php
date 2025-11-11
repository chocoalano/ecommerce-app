<?php

namespace App\Services\Mlm;

use Illuminate\Support\Facades\DB;

use App\Models\OrderProduct\Order;
use App\Models\Mlm\TblBonus;
use App\Models\Mlm\TblBonusSponsor;
use App\Models\Mlm\TblBonusMatching;
use App\Models\Mlm\TblBonusPairing;
use App\Models\Mlm\TblBonusPairingDetail;

class CommissionService
{
    public function __construct(
        protected NetworkService $network,
        protected PairingEngine $pairing
    ) {}

    public function awardForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $baseAmount = $this->resolveBaseAmount($order);
            $scale      = (int) config('mlm.scale', 2);

            // 1) Sponsor bonus
            $this->awardSponsor($order, $baseAmount, $scale);

            // 2) Matching bonus (multi-level)
            $this->awardMatching($order, $baseAmount, $scale);

            // 3) Pairing bonus (opsional)
            if (config('mlm.enable_pairing')) {
                $this->awardPairing($order, $baseAmount, $scale);
            }
        });
    }

    protected function resolveBaseAmount(Order $order): float
    {
        $source = config('mlm.base_amount_source', 'grand_total');

        // Ambil dari order langsung
        $map = [
            'grand_total' => fn() => (float) ($order->grand_total ?? 0),
            'subtotal'    => fn() => (float) ($order->subtotal ?? 0),
            'paid_amount' => fn() => (float) ($order->payments()->where('status', 'success')->sum('amount') ?? 0),
        ];

        $amount = ($map[$source] ?? $map['grand_total'])();

        // fallback ke sum(items) bila kosong
        if ($amount <= 0 && method_exists($order, 'items')) {
            $amount = (float) $order->items()->sum(DB::raw('(price * quantity) - COALESCE(discount,0)'));
        }

        return max(0, $amount);
    }

    protected function awardSponsor(Order $order, float $base, int $scale): void
    {
        $sponsorId = $this->network->getSponsorId($order->member_id);
        if (!$sponsorId || $base <= 0) return;

        $percent = (float) config('mlm.sponsor_percent', 5.0);
        $amount  = round($base * ($percent/100), $scale);

        if ($amount <= 0) return;

        // Idempoten: cek sudah ada bonus sponsor utk order ini?
        $exists = TblBonusSponsor::where('order_id', $order->id)
            ->where('member_id', $sponsorId)
            ->exists();

        if ($exists) return;

        TblBonusSponsor::create([
            'order_id'  => $order->id,
            'member_id' => $sponsorId,
            'amount'    => $amount,
            'percent'   => $percent,
            'note'      => $this->idempotencyNote($order, 'SPONSOR'),
        ]);

        // (Opsional) juga catat ke TblBonus agregat
        TblBonus::create([
            'order_id'  => $order->id,
            'member_id' => $sponsorId,
            'type'      => 'sponsor',
            'amount'    => $amount,
            'note'      => $this->idempotencyNote($order, 'SPONSOR'),
        ]);
    }

    protected function awardMatching(Order $order, float $base, int $scale): void
    {
        $percents = array_map('floatval', (array) config('mlm.matching_percents', []));
        if (empty($percents) || $base <= 0) return;

        $uplines = $this->network->getUplines($order->member_id, count($percents));
        // $uplines: array of member_id berurutan level 1..N
        foreach ($percents as $idx => $percent) {
            $level = $idx + 1;
            $memberId = $uplines[$idx] ?? null;
            if (!$memberId) break;

            $amount = round($base * ($percent/100), $scale);
            if ($amount <= 0) continue;

            $exists = TblBonusMatching::where('order_id', $order->id)
                ->where('member_id', $memberId)
                ->where('level', $level)
                ->exists();

            if ($exists) continue;

            TblBonusMatching::create([
                'order_id'  => $order->id,
                'member_id' => $memberId,
                'level'     => $level,
                'amount'    => $amount,
                'percent'   => $percent,
                'note'      => $this->idempotencyNote($order, "MATCHING-L{$level}"),
            ]);

            TblBonus::create([
                'order_id'  => $order->id,
                'member_id' => $memberId,
                'type'      => 'matching',
                'amount'    => $amount,
                'note'      => $this->idempotencyNote($order, "MATCHING-L{$level}"),
            ]);
        }
    }

    protected function awardPairing(Order $order, float $base, int $scale): void
    {
        if ($base <= 0) return;

        // Delegasikan ke PairingEngine supaya bisa diganti sesuai skema PV/binari
        $pairs = $this->pairing->consumePVForOrder($order, $base);

        // Simpan ringkasan pairing bila ada pair terbentuk
        foreach ($pairs as $pair) {
            // $pair: ['member_id'=>, 'left_pv'=>, 'right_pv'=>, 'paid_pairs'=>, 'bonus_amount'=>]
            $exists = TblBonusPairing::where('order_id', $order->id)
                ->where('member_id', $pair['member_id'])
                ->exists();

            if ($exists) continue;

            $pairRow = TblBonusPairing::create([
                'order_id'    => $order->id,
                'member_id'   => $pair['member_id'],
                'left_pv'     => $pair['left_pv'],
                'right_pv'    => $pair['right_pv'],
                'pairs_count' => $pair['paid_pairs'],
                'amount'      => round($pair['bonus_amount'], $scale),
                'percent'     => (float) config('mlm.pairing_percent', 10.0),
                'note'        => $this->idempotencyNote($order, 'PAIRING'),
            ]);

            // Detail konsumsi PV (opsional)
            TblBonusPairingDetail::create([
                'bonus_pairing_id' => $pairRow->id,
                'order_id'         => $order->id,
                'left_pv_used'     => $pair['left_pv'],
                'right_pv_used'    => $pair['right_pv'],
                'note'             => 'Auto pairing via order',
            ]);

            TblBonus::create([
                'order_id'  => $order->id,
                'member_id' => $pair['member_id'],
                'type'      => 'pairing',
                'amount'    => round($pair['bonus_amount'], $scale),
                'note'      => $this->idempotencyNote($order, 'PAIRING'),
            ]);
        }
    }

    protected function idempotencyNote(Order $order, string $kind): string
    {
        $prefix = (string) config('mlm.idempotency_note_prefix', 'ORDER-BONUS-');
        return "{$prefix}{$kind}-ORDER#{$order->id}";
    }
}
