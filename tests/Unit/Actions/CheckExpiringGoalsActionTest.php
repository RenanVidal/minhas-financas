<?php

namespace Tests\Unit\Actions;

use App\Actions\CheckExpiringGoalsAction;
use App\Models\Goal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckExpiringGoalsActionTest extends TestCase
{
    use RefreshDatabase;

    private CheckExpiringGoalsAction $action;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->action = new CheckExpiringGoalsAction();
        $this->user = User::factory()->create();
    }

    public function test_returns_goals_expiring_within_specified_days(): void
    {
        // Goal expiring in 3 days
        $expiringGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'deadline' => Carbon::now()->addDays(3),
        ]);

        // Goal expiring in 10 days (outside 7-day window)
        $futureGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'deadline' => Carbon::now()->addDays(10),
        ]);

        // Completed goal
        $completedGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
            'deadline' => Carbon::now()->addDays(2),
        ]);

        $result = $this->action->execute($this->user, 7);

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($expiringGoal));
        $this->assertFalse($result->contains($futureGoal));
        $this->assertFalse($result->contains($completedGoal));
    }

    public function test_returns_goals_expiring_today(): void
    {
        $todayGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'deadline' => Carbon::now()->addHours(12),
        ]);

        $tomorrowGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'deadline' => Carbon::now()->addDays(2),
        ]);

        $result = $this->action->getExpiringToday($this->user);

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($todayGoal));
        $this->assertFalse($result->contains($tomorrowGoal));
    }

    public function test_returns_goals_expiring_this_week(): void
    {
        $thisWeekGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'deadline' => Carbon::now()->addDays(5),
        ]);

        $nextWeekGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'deadline' => Carbon::now()->addDays(10),
        ]);

        $result = $this->action->getExpiringThisWeek($this->user);

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($thisWeekGoal));
        $this->assertFalse($result->contains($nextWeekGoal));
    }

    public function test_returns_overdue_goals(): void
    {
        $overdueGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'deadline' => Carbon::now()->subDays(2),
        ]);

        $futureGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'deadline' => Carbon::now()->addDays(5),
        ]);

        $cancelledGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'cancelled',
            'deadline' => Carbon::now()->subDays(3),
        ]);

        $result = $this->action->getOverdueGoals($this->user);

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($overdueGoal));
        $this->assertFalse($result->contains($futureGoal));
        $this->assertFalse($result->contains($cancelledGoal));
    }

    public function test_checks_if_has_expiring_goals(): void
    {
        // No expiring goals
        $this->assertFalse($this->action->hasExpiringGoals($this->user));

        // Create expiring goal
        Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'deadline' => Carbon::now()->addDays(3),
        ]);

        $this->assertTrue($this->action->hasExpiringGoals($this->user));
    }

    public function test_gets_urgency_level_overdue(): void
    {
        $deadline = Carbon::now()->subDays(1);
        $urgency = $this->action->getUrgencyLevel($deadline);
        
        $this->assertEquals('overdue', $urgency);
    }

    public function test_gets_urgency_level_critical(): void
    {
        $deadline = Carbon::now()->addHours(12);
        $urgency = $this->action->getUrgencyLevel($deadline);
        
        $this->assertEquals('critical', $urgency);
    }

    public function test_gets_urgency_level_high(): void
    {
        $deadline = Carbon::now()->addDays(2);
        $urgency = $this->action->getUrgencyLevel($deadline);
        
        $this->assertEquals('high', $urgency);
    }

    public function test_gets_urgency_level_medium(): void
    {
        $deadline = Carbon::now()->addDays(5);
        $urgency = $this->action->getUrgencyLevel($deadline);
        
        $this->assertEquals('medium', $urgency);
    }

    public function test_gets_urgency_level_low(): void
    {
        $deadline = Carbon::now()->addDays(15);
        $urgency = $this->action->getUrgencyLevel($deadline);
        
        $this->assertEquals('low', $urgency);
    }

    public function test_gets_time_remaining_for_future_deadline(): void
    {
        $deadline = Carbon::now()->addDays(5);
        $timeRemaining = $this->action->getTimeRemaining($deadline);
        
        $this->assertStringContainsString('Vence em', $timeRemaining);
        $this->assertStringContainsString('day', $timeRemaining);
    }

    public function test_gets_time_remaining_for_past_deadline(): void
    {
        $deadline = Carbon::now()->subDays(3);
        $timeRemaining = $this->action->getTimeRemaining($deadline);
        
        $this->assertStringContainsString('Vencida há', $timeRemaining);
        $this->assertStringContainsString('day', $timeRemaining);
    }

    public function test_orders_expiring_goals_by_deadline(): void
    {
        $goal1 = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'deadline' => Carbon::now()->addDays(5),
        ]);

        $goal2 = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'deadline' => Carbon::now()->addDays(2),
        ]);

        $goal3 = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'deadline' => Carbon::now()->addDays(7),
        ]);

        $result = $this->action->execute($this->user, 7);

        $this->assertCount(3, $result);
        $this->assertEquals($goal2->id, $result->first()->id); // Closest deadline first
        $this->assertEquals($goal1->id, $result->get(1)->id);
        $this->assertEquals($goal3->id, $result->last()->id);
    }

    public function test_only_returns_goals_for_specific_user(): void
    {
        $otherUser = User::factory()->create();

        $userGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'deadline' => Carbon::now()->addDays(3),
        ]);

        $otherUserGoal = Goal::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'active',
            'deadline' => Carbon::now()->addDays(3),
        ]);

        $result = $this->action->execute($this->user);

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($userGoal));
        $this->assertFalse($result->contains($otherUserGoal));
    }
}