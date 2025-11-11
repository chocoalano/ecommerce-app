<?php

namespace App\Services\Mlm;

use Illuminate\Database\Eloquent\Model;

class NetworkService
{
    protected string $memberModel;
    protected string $memberPk;
    protected string $sponsorFk;

    public function __construct()
    {
        $this->memberModel = (string) config('mlm.member_model', 'App\\Models\\Customer');
        $this->memberPk    = (string) config('mlm.member_pk', 'id');
        $this->sponsorFk   = (string) config('mlm.member_sponsor_fk', 'sponsor_id');
    }

    public function getSponsorId(int|string $memberId): ?int
    {
        /** @var Model $model */
        $model = $this->memberModel::query()
            ->where($this->memberPk, $memberId)
            ->first();

        if (!$model) return null;

        return $model->{$this->sponsorFk} ?? null;
    }

    /**
     * @return array<int> member_id uplines (Level1..N)
     */
    public function getUplines(int|string $memberId, int $levels): array
    {
        $uplines = [];
        $current = $memberId;

        for ($i = 0; $i < $levels; $i++) {
            $sponsorId = $this->getSponsorId($current);
            if (!$sponsorId) break;

            $uplines[] = (int) $sponsorId;
            $current   = $sponsorId;
        }

        return $uplines;
    }
}
