<?php

namespace App\Actions;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTransactionAction
{
    /**
     * Create a new transaction and update user's balance.
     *
     * @param User $user
     * @param array $data
     * @return Transaction
     */
    public function execute(User $user, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $data) {
            $transaction = new Transaction([
                'user_id' => $user->id,
                'category_id' => $data['category_id'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'type' => $data['type'],
                'date' => $data['date'],
            ]);

            $transaction->save();

            return $transaction;
        });
    }
}