<?php

namespace App\Actions;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DeleteTransactionAction
{
    /**
     * Delete a transaction and adjust balance.
     *
     * @param Transaction $transaction
     * @return bool
     */
    public function execute(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            return $transaction->delete();
        });
    }
}