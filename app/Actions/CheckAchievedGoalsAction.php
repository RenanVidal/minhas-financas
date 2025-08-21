<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CheckAchievedGoalsAction
{
    /**
     * Check for recently achieved goals and return them for notifications.
     */
    public function execute(User $user): Collection
    {
        // Get goals that were recently completed (within last 24 hours)
        $recentlyAchieved = $user->goals()
            ->where('status', 'completed')
            ->where('updated_at', '>=', now()->subDay())
            ->get();
        
        return $recentlyAchieved;
    }
    
    /**
     * Get all completed goals for the user.
     */
    public function getAllAchievedGoals(User $user): Collection
    {
        return $user->goals()
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->get();
    }
    
    /**
     * Check if user has any achievements to celebrate.
     */
    public function hasRecentAchievements(User $user): bool
    {
        return $this->execute($user)->isNotEmpty();
    }
    
    /**
     * Get achievement statistics for the user.
     */
    public function getAchievementStats(User $user): array
    {
        $totalGoals = $user->goals()->count();
        $completedGoals = $user->goals()->where('status', 'completed')->count();
        $activeGoals = $user->goals()->where('status', 'active')->count();
        $expiredGoals = $user->goals()->where('status', 'cancelled')->count();
        
        return [
            'total' => $totalGoals,
            'completed' => $completedGoals,
            'active' => $activeGoals,
            'cancelled' => $expiredGoals,
            'completion_rate' => $totalGoals > 0 ? round(($completedGoals / $totalGoals) * 100, 1) : 0,
        ];
    }
}