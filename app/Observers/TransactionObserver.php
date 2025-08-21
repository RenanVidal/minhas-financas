<?php

namespace App\Observers;

use App\Actions\CheckGoalProgressAction;
use App\Models\Transaction;

class TransactionObserver
{
    public function __construct(
        private CheckGoalProgressAction $checkProgressAction
    ) {}

    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        $this->updateRelatedGoals($transaction);
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        $this->updateRelatedGoals($transaction);
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        $this->updateRelatedGoals($transaction);
    }

    /**
     * Update progress for goals related to this transaction.
     */
    private function updateRelatedGoals(Transaction $transaction): void
    {
        $user = $transaction->user;
        
        // Update goals for the specific category
        if ($transaction->category_id) {
            $categoryGoals = $user->goals()
                ->where('category_id', $transaction->category_id)
                ->where('status', 'active')
                ->get();
            
            foreach ($categoryGoals as $goal) {
                $this->checkProgressAction->execute($goal);
            }
        }
        
        // Update goals without specific category (general savings goals)
        $generalGoals = $user->goals()
            ->whereNull('category_id')
            ->where('status', 'active')
            ->get();
        
        foreach ($generalGoals as $goal) {
            $this->checkProgressAction->execute($goal);
        }
    }
}