<?php

namespace App\Actions;

use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeleteCategoryAction
{
    /**
     * Delete a category safely, checking for associated transactions.
     *
     * @param Category $category
     * @return array
     * @throws ModelNotFoundException
     */
    public function execute(Category $category): array
    {
        // Check if category has associated transactions
        if ($category->transactions()->exists()) {
            return [
                'success' => false,
                'message' => 'Não é possível excluir uma categoria que possui transações associadas.',
                'error_type' => 'has_transactions'
            ];
        }

        try {
            $categoryName = $category->name;
            $category->delete();

            return [
                'success' => true,
                'message' => "Categoria '{$categoryName}' excluída com sucesso!",
                'deleted_category' => $categoryName
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao excluir a categoria. Tente novamente.',
                'error_type' => 'deletion_failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if a category can be safely deleted.
     *
     * @param Category $category
     * @return bool
     */
    public function canDelete(Category $category): bool
    {
        return !$category->transactions()->exists();
    }

    /**
     * Get the count of transactions associated with the category.
     *
     * @param Category $category
     * @return int
     */
    public function getTransactionCount(Category $category): int
    {
        return $category->transactions()->count();
    }
}