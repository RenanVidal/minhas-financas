<?php

namespace Tests\Unit\Actions;

use App\Actions\CheckAchievedGoalsAction;
use App\Models\Goal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckAchievedGoalsActionTest extends TestCase
{
    use RefreshDatabase;

    private CheckAchievedGoalsAction $action;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->action = new CheckAchievedGoalsAction();
        $this->user = User::factory()->create();
    }

    public function test_returns_recently_achieved_goals(): void
    {
        // Create a goal completed within last 24 hours
        $recentGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'updated_at' => Carbon::now()->subHours(12),
        ]);

        // Create a goal completed more than 24 hours ago
        $oldGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        // Create an active goal
        $activeGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        $result = $this->action->execute($this->user);

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($recentGoal));
        $this->assertFalse($result->contains($oldGoal));
        $this->assertFalse($result->contains($activeGoal));
    }

    public function test_returns_empty_collection_when_no_recent_achievements(): void
    {
        // Create goals but none recently completed
        Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        $result = $this->action->execute($this->user);

        $this->assertCount(0, $result);
    }

    public function test_gets_all_achieved_goals(): void
    {
        $completedGoal1 = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'updated_at' => Carbon::now()->subDays(1),
        ]);

        $completedGoal2 = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'updated_at' => Carbon::now()->subDays(5),
        ]);

        $activeGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        $result = $this->action->getAllAchievedGoals($this->user);

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains($completedGoal1));
        $this->assertTrue($result->contains($completedGoal2));
        $this->assertFalse($result->contains($activeGoal));
        
        // Should be ordered by updated_at desc
        $this->assertEquals($completedGoal1->id, $result->first()->id);
    }

    public function test_checks_if_has_recent_achievements(): void
    {
        // No recent achievements
        $this->assertFalse($this->action->hasRecentAchievements($this->user));

        // Create recent achievement
        Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'updated_at' => Carbon::now()->subHours(6),
        ]);

        $this->assertTrue($this->action->hasRecentAchievements($this->user));
    }

    public function test_gets_achievement_statistics(): void
    {
        // Create various goals
        Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
        ]);

        Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
        ]);

        Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'cancelled',
        ]);

        $stats = $this->action->getAchievementStats($this->user);

        $this->assertEquals(4, $stats['total']);
        $this->assertEquals(2, $stats['completed']);
        $this->assertEquals(1, $stats['active']);
        $this->assertEquals(1, $stats['cancelled']);
        $this->assertEquals(50.0, $stats['completion_rate']);
    }

    public function test_gets_zero_completion_rate_when_no_goals(): void
    {
        $stats = $this->action->getAchievementStats($this->user);

        $this->assertEquals(0, $stats['total']);
        $this->assertEquals(0, $stats['completed']);
        $this->assertEquals(0, $stats['active']);
        $this->assertEquals(0, $stats['cancelled']);
        $this->assertEquals(0, $stats['completion_rate']);
    }

    public function test_only_returns_goals_for_specific_user(): void
    {
        $otherUser = User::factory()->create();

        // Create goals for different users
        $userGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'updated_at' => Carbon::now()->subHours(6),
        ]);

        $otherUserGoal = Goal::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'completed',
            'updated_at' => Carbon::now()->subHours(6),
        ]);

        $result = $this->action->execute($this->user);

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($userGoal));
        $this->assertFalse($result->contains($otherUserGoal));
    }
}