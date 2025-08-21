<?php

namespace Tests\Unit\Actions;

use App\Actions\CheckGoalProgressAction;
use App\Models\Category;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckGoalProgressActionTest extends TestCase
{
    use RefreshDatabase;

    private CheckGoalProgressAction $action;
    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->action = new CheckGoalProgressAction();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income'
        ]);
    }

    public function test_calculates_progress_with_category_transactions(): void
    {
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'target_amount' => 1000,
            'current_amount' => 0,
            'status' => 'active',
        ]);

        // Create transactions after goal creation
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 300,
            'type' => 'income',
            'created_at' => $goal->created_at->addHour(),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 200,
            'type' => 'income',
            'created_at' => $goal->created_at->addHours(2),
        ]);

        $result = $this->action->execute($goal);

        $this->assertEquals(500, $result->current_amount);
        $this->assertEquals('active', $result->status);
    }

    public function test_marks_goal_as_completed_when_target_reached(): void
    {
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'target_amount' => 1000,
            'current_amount' => 0,
            'status' => 'active',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 1200,
            'type' => 'income',
            'created_at' => $goal->created_at->addHour(),
        ]);

        $result = $this->action->execute($goal);

        $this->assertEquals(1200, $result->current_amount);
        $this->assertEquals('completed', $result->status);
    }

    public function test_marks_goal_as_cancelled_when_deadline_passed(): void
    {
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'target_amount' => 1000,
            'current_amount' => 500,
            'deadline' => Carbon::yesterday(),
            'status' => 'active',
        ]);

        $result = $this->action->execute($goal);

        $this->assertEquals('cancelled', $result->status);
    }

    public function test_calculates_progress_without_category(): void
    {
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => null,
            'target_amount' => 1000,
            'current_amount' => 0,
            'status' => 'active',
        ]);

        // Create transactions in different categories
        $category1 = Category::factory()->create(['user_id' => $this->user->id, 'type' => 'income']);
        $category2 = Category::factory()->create(['user_id' => $this->user->id, 'type' => 'expense']);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category1->id,
            'amount' => 800,
            'type' => 'income',
            'created_at' => $goal->created_at->addHour(),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category2->id,
            'amount' => 300,
            'type' => 'expense',
            'created_at' => $goal->created_at->addHours(2),
        ]);

        $result = $this->action->execute($goal);

        $this->assertEquals(500, $result->current_amount); // 800 - 300
        $this->assertEquals('active', $result->status);
    }

    public function test_ignores_transactions_before_goal_creation(): void
    {
        // Create transaction before goal
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 500,
            'type' => 'income',
            'created_at' => Carbon::now()->subDay(),
        ]);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'target_amount' => 1000,
            'current_amount' => 0,
            'status' => 'active',
        ]);

        // Create transaction after goal
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 300,
            'type' => 'income',
            'created_at' => $goal->created_at->addHour(),
        ]);

        $result = $this->action->execute($goal);

        $this->assertEquals(300, $result->current_amount); // Only counts transaction after goal creation
    }

    public function test_calculates_progress_percentage(): void
    {
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'target_amount' => 1000,
            'current_amount' => 250,
        ]);

        $percentage = $this->action->calculateProgressPercentage($goal);

        $this->assertEquals(25.0, $percentage);
    }

    public function test_calculates_progress_percentage_over_100(): void
    {
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'target_amount' => 1000,
            'current_amount' => 1200,
        ]);

        $percentage = $this->action->calculateProgressPercentage($goal);

        $this->assertEquals(100.0, $percentage);
    }

    public function test_gets_days_remaining(): void
    {
        $deadline = Carbon::now()->startOfDay()->addDays(15);
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'deadline' => $deadline,
        ]);

        $daysRemaining = $this->action->getDaysRemaining($goal);

        $this->assertTrue($daysRemaining >= 14 && $daysRemaining <= 15);
    }

    public function test_gets_zero_days_for_past_deadline(): void
    {
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'deadline' => Carbon::yesterday(),
        ]);

        $daysRemaining = $this->action->getDaysRemaining($goal);

        $this->assertEquals(0, $daysRemaining);
    }

    public function test_ensures_current_amount_not_negative(): void
    {
        $expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense'
        ]);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'target_amount' => 1000,
            'current_amount' => 0,
            'status' => 'active',
        ]);

        // Create expense transaction (should result in negative amount)
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 500,
            'type' => 'expense',
            'created_at' => $goal->created_at->addHour(),
        ]);

        $result = $this->action->execute($goal);

        $this->assertEquals(0, $result->current_amount); // Should not be negative
    }
}