<?php

namespace App\Actions;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class UpdateTransactionAction
{
    /**
     * Update a transaction and recalculate balance.
     *
     * @param Transaction $transaction
     * @param array $data
     * @return Transaction
     */
    public function execute(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $transaction->update([
                'category_id' => $data['category_id'],
                'description' => $data['description'],
                'amount' => $data['amount'],
                'type' => $data['type'],
                'date' => $data['date'],
            ]);

            return $transaction->fresh();
        });
    }
}