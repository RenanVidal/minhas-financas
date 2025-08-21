<?php

namespace Tests\Unit\Observers;

use App\Actions\CheckGoalProgressAction;
use App\Models\Category;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use App\Observers\TransactionObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class TransactionObserverTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;
    private CheckGoalProgressAction $mockAction;
    private TransactionObserver $observer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
        
        $this->mockAction = Mockery::mock(CheckGoalProgressAction::class);
        $this->observer = new TransactionObserver($this->mockAction);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_created_updates_category_goals(): void
    {
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $this->mockAction->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(function ($arg) use ($goal) {
                return $arg->id === $goal->id;
            }))
            ->andReturn($goal);

        $this->observer->created($transaction);
        
        $this->assertTrue(true); // Mock expectations are verified automatically
    }

    public function test_created_updates_general_goals(): void
    {
        $generalGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => null,
            'status' => 'active',
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $this->mockAction->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(function ($arg) use ($generalGoal) {
                return $arg->id === $generalGoal->id;
            }))
            ->andReturn($generalGoal);

        $this->observer->created($transaction);
        
        $this->assertTrue(true);
    }

    public function test_updated_triggers_goal_updates(): void
    {
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $this->mockAction->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(function ($arg) use ($goal) {
                return $arg->id === $goal->id;
            }))
            ->andReturn($goal);

        $this->observer->updated($transaction);
        
        $this->assertTrue(true);
    }

    public function test_deleted_triggers_goal_updates(): void
    {
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $this->mockAction->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(function ($arg) use ($goal) {
                return $arg->id === $goal->id;
            }))
            ->andReturn($goal);

        $this->observer->deleted($transaction);
        
        $this->assertTrue(true);
    }

    public function test_ignores_completed_goals(): void
    {
        Goal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'completed',
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $this->mockAction->shouldNotReceive('execute');

        $this->observer->created($transaction);
        
        $this->assertTrue(true);
    }

    public function test_ignores_cancelled_goals(): void
    {
        Goal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'cancelled',
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $this->mockAction->shouldNotReceive('execute');

        $this->observer->created($transaction);
        
        $this->assertTrue(true);
    }

    public function test_updates_multiple_goals_for_same_category(): void
    {
        $goal1 = Goal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $goal2 = Goal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $this->mockAction->shouldReceive('execute')
            ->twice()
            ->andReturn($goal1, $goal2);

        $this->observer->created($transaction);
        
        $this->assertTrue(true);
    }

    public function test_only_updates_goals_for_transaction_user(): void
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        
        // Goal for other user - should not be updated
        Goal::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherCategory->id,
            'status' => 'active',
        ]);

        // Goal for transaction user - should be updated
        $userGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'active',
        ]);

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $this->mockAction->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(function ($arg) use ($userGoal) {
                return $arg->id === $userGoal->id;
            }))
            ->andReturn($userGoal);

        $this->observer->created($transaction);
        
        $this->assertTrue(true);
    }
}