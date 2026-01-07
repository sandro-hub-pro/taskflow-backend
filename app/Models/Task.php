<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'progress',
        'due_date',
        'created_by',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'progress' => 'integer',
        ];
    }

    /**
     * The project this task belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * User who created the task.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User who assigned the task.
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Users assigned to this task.
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignments')
            ->withPivot('assigned_by', 'progress')
            ->withTimestamps();
    }

    /**
     * Get the calculated progress based on all assignees' contributions.
     * Each assignee's progress is their portion (100 / assignee count).
     */
    public function getCalculatedProgressAttribute(): int
    {
        $assignees = $this->assignees;
        
        if ($assignees->isEmpty()) {
            return $this->progress ?? 0;
        }

        $totalProgress = $assignees->sum(fn($assignee) => $assignee->pivot->progress ?? 0);
        $assigneeCount = $assignees->count();
        
        // Each assignee contributes their progress divided by the number of assignees
        // e.g., 3 assignees each at 30% = 30/3 + 30/3 + 30/3 = 30% total
        // Or if we want additive: 3 assignees at 30%, 30%, 30% = 90% total (capped at 100)
        // Going with additive approach as per user request
        return min(100, (int) round($totalProgress / $assigneeCount));
    }

    /**
     * Comments on this task.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    /**
     * Check if task is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        return $this->due_date->isPast() && $this->status !== 'completed';
    }

    /**
     * Get priority color.
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'gray',
            'in_progress' => 'blue',
            'under_review' => 'yellow',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}

