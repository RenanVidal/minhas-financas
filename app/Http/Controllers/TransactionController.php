<?php

namespace App\Http\Controllers;

use App\Actions\CreateTransactionAction;
use App\Actions\DeleteTransactionAction;
use App\Actions\UpdateTransactionAction;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $transactions = Transaction::with('category')
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('transactions.index', compact('transactions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = Category::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('transactions.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request, CreateTransactionAction $action): RedirectResponse
    {
        $transaction = $action->execute(auth()->user(), $request->validated());

        return redirect()->route('transactions.index')
            ->with('success', 'Transação criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): View
    {
        $this->authorize('view', $transaction);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        $categories = Category::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return view('transactions.edit', compact('transaction', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction, UpdateTransactionAction $action): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $action->execute($transaction, $request->validated());

        return redirect()->route('transactions.index')
            ->with('success', 'Transação atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction, DeleteTransactionAction $action): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $action->execute($transaction);

        return redirect()->route('transactions.index')
            ->with('success', 'Transação excluída com sucesso!');
    }
}