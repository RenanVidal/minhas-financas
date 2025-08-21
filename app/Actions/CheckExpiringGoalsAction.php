<?php

namespace App\Actions;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CheckExpiringGoalsAction
{
    /**
     * Check for goals that are expiring soon (within specified days).
     */
    public function execute(User $user, int $daysAhead = 7): Collection
    {
        $deadline = Carbon::now()->addDays($daysAhead);
        
        return $user->goals()
            ->where('status', 'active')
            ->where('deadline', '<=', $deadline)
            ->where('deadline', '>=', Carbon::now())
            ->orderBy('deadline', 'asc')
            ->get();
    }
    
    /**
     * Get goals expiring within 24 hours.
     */
    public function getExpiringToday(User $user): Collection
    {
        return $this->execute($user, 1);
    }
    
    /**
     * Get goals expiring within a week.
     */
    public function getExpiringThisWeek(User $user): Collection
    {
        return $this->execute($user, 7);
    }
    
    /**
     * Get goals that have already expired but are still active.
     */
    public function getOverdueGoals(User $user): Collection
    {
        return $user->goals()
            ->where('status', 'active')
            ->where('deadline', '<', Carbon::now())
            ->orderBy('deadline', 'desc')
            ->get();
    }
    
    /**
     * Check if user has any expiring goals.
     */
    public function hasExpiringGoals(User $user, int $daysAhead = 7): bool
    {
        return $this->execute($user, $daysAhead)->isNotEmpty();
    }
    
    /**
     * Get urgency level for a goal based on days remaining.
     */
    public function getUrgencyLevel(Carbon $deadline): string
    {
        $daysRemaining = Carbon::now()->diffInDays($deadline, false);
        
        if ($daysRemaining < 0) {
            return 'overdue';
        } elseif ($daysRemaining <= 1) {
            return 'critical';
        } elseif ($daysRemaining <= 3) {
            return 'high';
        } elseif ($daysRemaining <= 7) {
            return 'medium';
        } else {
            return 'low';
        }
    }
    
    /**
     * Get formatted time remaining for a goal.
     */
    public function getTimeRemaining(Carbon $deadline): string
    {
        $now = Carbon::now();
        
        if ($deadline->isPast()) {
            return 'Vencida há ' . $now->diffForHumans($deadline, true);
        }
        
        return 'Vence em ' . $now->diffForHumans($deadline, true);
    }
}