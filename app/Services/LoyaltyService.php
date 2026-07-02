<?php

namespace App\Services;

use App\Models\LoyaltyPoint;
use App\Models\LoyaltyTransaction;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    public function earn(LoyaltyPoint $account, int $points, string $remarks, ?string $refType = null, ?int $refId = null): LoyaltyTransaction
    {
        return DB::transaction(function () use ($account, $points, $remarks, $refType, $refId) {
            $account->refresh();
            $account->update([
                'total_points' => $account->total_points + $points,
                'lifetime_points' => $account->lifetime_points + $points,
            ]);

            return LoyaltyTransaction::create([
                'loyalty_id' => $account->id,
                'transaction_type' => 'earn',
                'points' => $points,
                'reference_type' => $refType ?? 'admin',
                'reference_id' => $refId,
                'expiry_date' => now()->addMonths(config('loyalty.expiry_months', 12)),
                'remarks' => $remarks,
            ]);
        });
    }

    public function redeem(LoyaltyPoint $account, int $points, string $remarks, ?string $refType = null, ?int $refId = null): LoyaltyTransaction
    {
        return DB::transaction(function () use ($account, $points, $remarks, $refType, $refId) {
            $account->refresh();
            if ($account->total_points < $points) {
                throw new \InvalidArgumentException('Insufficient loyalty points.');
            }
            $account->update([
                'total_points' => $account->total_points - $points,
                'redeemed_points' => $account->redeemed_points + $points,
            ]);

            return LoyaltyTransaction::create([
                'loyalty_id' => $account->id,
                'transaction_type' => 'redeem',
                'points' => -$points,
                'reference_type' => $refType ?? 'admin',
                'reference_id' => $refId,
                'remarks' => $remarks,
            ]);
        });
    }

    public function adjust(LoyaltyPoint $account, int $points, string $remarks): LoyaltyTransaction
    {
        return $points >= 0
            ? $this->earn($account, $points, $remarks, 'admin')
            : $this->redeem($account, abs($points), $remarks, 'admin');
    }

    public function resolveTier(float $totalSpend): string
    {
        $tier = 'bronze';
        foreach (config('loyalty.tiers', []) as $key => $data) {
            if ($totalSpend >= ($data['min_spend'] ?? 0)) {
                $tier = $key;
            }
        }

        return $tier;
    }
}
