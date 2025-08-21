<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rotas protegidas do sistema financeiro
    // Estas rotas serão implementadas nas próximas tarefas
    
    // Categorias
    Route::resource('categories', \App\Http\Controllers\CategoryController::class);
    
    // Transações
    Route::resource('transactions', \App\Http\Controllers\TransactionController::class);
    
    // Relatórios
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [\App\Http\Controllers\ReportController::class, 'generate'])->name('reports.generate');
    Route::post('/reports/export', [\App\Http\Controllers\ReportController::class, 'export'])->name('reports.export');
    
    // Metas
    Route::resource('goals', \App\Http\Controllers\GoalController::class);
    Route::post('/goals/{goal}/update-progress', [\App\Http\Controllers\GoalController::class, 'updateProgress'])->name('goals.update-progress');
    Route::get('/achievements', [\App\Http\Controllers\GoalController::class, 'achievements'])->name('goals.achievements');
});

require __DIR__.'/auth.php';
