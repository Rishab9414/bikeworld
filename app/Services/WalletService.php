<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function credit(Wallet $wallet, float $amount, string $description, ?string $refType = null, ?int $refId = null): WalletTransaction
    {
        return DB::transaction(function () use ($wallet, $amount, $description, $refType, $refId) {
            $wallet->refresh();
            $balance = (float) $wallet->current_balance + $amount;
            $wallet->update(['current_balance' => $balance]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => 'credit',
                'amount' => $amount,
                'reference_type' => $refType ?? 'admin',
                'reference_id' => $refId,
                'description' => $description,
                'balance_after' => $balance,
                'transaction_date' => now(),
            ]);
        });
    }

    public function debit(Wallet $wallet, float $amount, string $description, ?string $refType = null, ?int $refId = null): WalletTransaction
    {
        return DB::transaction(function () use ($wallet, $amount, $description, $refType, $refId) {
            $wallet->refresh();
            if ((float) $wallet->current_balance < $amount) {
                throw new \InvalidArgumentException('Insufficient wallet balance.');
            }
            $balance = (float) $wallet->current_balance - $amount;
            $wallet->update(['current_balance' => $balance]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => 'debit',
                'amount' => $amount,
                'reference_type' => $refType ?? 'admin',
                'reference_id' => $refId,
                'description' => $description,
                'balance_after' => $balance,
                'transaction_date' => now(),
            ]);
        });
    }
}
