<?php

namespace App\Actions;

use App\Models\Goal;
use Carbon\Carbon;

class CheckGoalProgressAction
{
    /**
     * Check and update goal progress based on related transactions.
     */
    public function execute(Goal $goal): Goal
    {
        // Calculate current amount based on transactions in the goal's category
        $currentAmount = 0;
        
        if ($goal->category_id) {
            // Get transactions from the category since goal creation
            $transactions = $goal->user->transactions()
                ->where('category_id', $goal->category_id)
                ->where('created_at', '>=', $goal->created_at)
                ->get();
            
            foreach ($transactions as $transaction) {
                if ($transaction->type === 'income') {
                    $currentAmount += $transaction->amount;
                } else {
                    $currentAmount -= $transaction->amount;
                }
            }
        } else {
            // If no category specified, calculate based on all transactions since goal creation
            $transactions = $goal->user->transactions()
                ->where('created_at', '>=', $goal->created_at)
                ->get();
            
            foreach ($transactions as $transaction) {
                if ($transaction->type === 'income') {
                    $currentAmount += $transaction->amount;
                } else {
                    $currentAmount -= $transaction->amount;
                }
            }
        }
        
        // Update current amount (ensure it's not negative for savings goals)
        $goal->current_amount = max(0, $currentAmount);
        
        // Update status based on progress
        if ($goal->current_amount >= $goal->target_amount) {
            $goal->status = 'completed';
        } elseif (Carbon::parse($goal->deadline)->isPast() && $goal->status === 'active') {
            $goal->status = 'cancelled';
        } else {
            $goal->status = 'active';
        }
        
        $goal->save();
        
        return $goal->fresh();
    }
    
    /**
     * Calculate progress percentage.
     */
    public function calculateProgressPercentage(Goal $goal): float
    {
        if ($goal->target_amount <= 0) {
            return 0;
        }
        
        return min(100, ($goal->current_amount / $goal->target_amount) * 100);
    }
    
    /**
     * Get days remaining until deadline.
     */
    public function getDaysRemaining(Goal $goal): int
    {
        $deadline = Carbon::parse($goal->deadline);
        $now = Carbon::now();
        
        if ($deadline->isPast()) {
            return 0;
        }
        
        return $now->diffInDays($deadline);
    }
}