<?php

namespace App\Http\Controllers;

use App\Actions\CheckAchievedGoalsAction;
use App\Actions\CheckExpiringGoalsAction;
use App\Actions\CheckGoalProgressAction;
use App\Http\Requests\StoreGoalRequest;
use App\Http\Requests\UpdateGoalRequest;
use App\Models\Goal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoalController extends Controller
{
    public function __construct(
        private CheckGoalProgressAction $checkProgressAction,
        private CheckAchievedGoalsAction $checkAchievedAction,
        private CheckExpiringGoalsAction $checkExpiringAction
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = auth()->user();
        
        // Get all goals with progress updated
        $goals = $user->goals()->with('category')->orderBy('deadline', 'asc')->get();
        
        // Update progress for all goals
        foreach ($goals as $goal) {
            $this->checkProgressAction->execute($goal);
        }
        
        // Refresh goals after progress update
        $goals = $user->goals()->with('category')->orderBy('deadline', 'asc')->get();
        
        // Get expiring goals for alerts
        $expiringGoals = $this->checkExpiringAction->getExpiringThisWeek($user);
        $overdueGoals = $this->checkExpiringAction->getOverdueGoals($user);
        
        // Get achievement stats
        $stats = $this->checkAchievedAction->getAchievementStats($user);
        
        return view('goals.index', compact('goals', 'expiringGoals', 'overdueGoals', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = auth()->user()->categories()->orderBy('name')->get();
        
        return view('goals.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGoalRequest $request): RedirectResponse
    {
        $goal = Goal::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'target_amount' => $request->target_amount,
            'deadline' => $request->deadline,
            'category_id' => $request->category_id,
            'current_amount' => 0,
            'status' => 'active',
        ]);

        // Update initial progress
        $this->checkProgressAction->execute($goal);

        return redirect()->route('goals.index')
            ->with('success', 'Meta criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Goal $goal): View
    {
        $this->authorize('view', $goal);
        
        // Update progress before showing
        $goal = $this->checkProgressAction->execute($goal);
        
        // Get progress details
        $progressPercentage = $this->checkProgressAction->calculateProgressPercentage($goal);
        $daysRemaining = $this->checkProgressAction->getDaysRemaining($goal);
        $urgencyLevel = $this->checkExpiringAction->getUrgencyLevel($goal->deadline);
        $timeRemaining = $this->checkExpiringAction->getTimeRemaining($goal->deadline);
        
        // Get related transactions if category is specified
        $transactions = collect();
        if ($goal->category_id) {
            $transactions = $goal->user->transactions()
                ->where('category_id', $goal->category_id)
                ->where('created_at', '>=', $goal->created_at)
                ->with('category')
                ->orderBy('date', 'desc')
                ->limit(10)
                ->get();
        }
        
        return view('goals.show', compact(
            'goal', 
            'progressPercentage', 
            'daysRemaining', 
            'urgencyLevel', 
            'timeRemaining',
            'transactions'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Goal $goal): View
    {
        $this->authorize('update', $goal);
        
        $categories = auth()->user()->categories()->orderBy('name')->get();
        
        return view('goals.edit', compact('goal', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGoalRequest $request, Goal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);
        
        $goal->update([
            'name' => $request->name,
            'target_amount' => $request->target_amount,
            'deadline' => $request->deadline,
            'category_id' => $request->category_id,
        ]);

        // Update progress after changes
        $this->checkProgressAction->execute($goal);

        return redirect()->route('goals.show', $goal)
            ->with('success', 'Meta atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Goal $goal): RedirectResponse
    {
        $this->authorize('delete', $goal);
        
        $goal->delete();

        return redirect()->route('goals.index')
            ->with('success', 'Meta excluída com sucesso!');
    }

    /**
     * Update progress for a specific goal.
     */
    public function updateProgress(Goal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);
        
        $this->checkProgressAction->execute($goal);
        
        return redirect()->back()
            ->with('success', 'Progresso da meta atualizado!');
    }

    /**
     * Get achievements for notifications.
     */
    public function achievements(): View
    {
        $user = auth()->user();
        
        $recentAchievements = $this->checkAchievedAction->execute($user);
        $allAchievements = $this->checkAchievedAction->getAllAchievedGoals($user);
        $stats = $this->checkAchievedAction->getAchievementStats($user);
        
        return view('goals.achievements', compact('recentAchievements', 'allAchievements', 'stats'));
    }
}