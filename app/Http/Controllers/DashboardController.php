<?php

namespace App\Http\Controllers;

use App\Actions\CalculateDashboardDataAction;
use App\Actions\CheckAchievedGoalsAction;
use App\Actions\CheckExpiringGoalsAction;
use App\Actions\GenerateFinancialChartAction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private CalculateDashboardDataAction $calculateDashboardDataAction,
        private GenerateFinancialChartAction $generateFinancialChartAction,
        private CheckAchievedGoalsAction $checkAchievedGoalsAction,
        private CheckExpiringGoalsAction $checkExpiringGoalsAction
    ) {}

    /**
     * Display the dashboard with financial overview.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get dashboard data
        $dashboardData = $this->calculateDashboardDataAction->execute($user);
        
        // Get chart data for 6 months
        $chartData = $this->generateFinancialChartAction->execute($user, 6);
        
        // Get goal notifications
        $recentAchievements = $this->checkAchievedGoalsAction->execute($user);
        $expiringGoals = $this->checkExpiringGoalsAction->getExpiringThisWeek($user);
        $overdueGoals = $this->checkExpiringGoalsAction->getOverdueGoals($user);
        
        return view('dashboard', [
            'dashboardData' => $dashboardData,
            'chartData' => $chartData,
            'recentAchievements' => $recentAchievements,
            'expiringGoals' => $expiringGoals,
            'overdueGoals' => $overdueGoals,
        ]);
    }
}