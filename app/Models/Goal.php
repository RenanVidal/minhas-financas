<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'target_amount',
        'current_amount',
        'deadline',
        'status',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'deadline' => 'date',
        'status' => 'string',
    ];

    /**
     * Get the user that owns the goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns the goal.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Calculate the progress percentage of the goal.
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->target_amount <= 0) {
            return 0;
        }
        
        return min(100, ($this->current_amount / $this->target_amount) * 100);
    }

    /**
     * Check if the goal is achieved.
     */
    public function getIsAchievedAttribute(): bool
    {
        return $this->current_amount >= $this->target_amount;
    }

    /**
     * Check if the goal is expiring soon (within 7 days).
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->deadline->diffInDays(now()) <= 7 && $this->deadline->isFuture();
    }

    /**
     * Check if the goal is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->deadline->isPast() && $this->status === 'active';
    }

    /**
     * Scope a query to only include active goals.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include completed goals.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include goals expiring soon.
     */
    public function scopeExpiringSoon($query)
    {
        return $query->where('deadline', '<=', now()->addDays(7))
                    ->where('deadline', '>=', now())
                    ->where('status', 'active');
    }
}
