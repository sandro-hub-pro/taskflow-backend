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
        'accepted_at',
        'accepted_by',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'progress' => 'integer',
            'accepted_at' => 'datetime',
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
     * User who accepted the completed task.
     */
    public function accepter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    /**
     * Check if task has been accepted by incharge.
     */
    public function getIsAcceptedAttribute(): bool
    {
        return $this->accepted_at !== null;
    }

    /**
     * Users assigned to this task.
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignments')
            ->withPivot('assigned_by', 'progress', 'status')
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
        return min(100, (int) round($totalProgress / $assigneeCount));
    }

    /**
     * Get the calculated overall status based on all assignees' individual statuses.
     * Task is "completed" only when ALL assignees have status "completed".
     */
    public function getCalculatedStatusAttribute(): string
    {
        $assignees = $this->assignees;
        
        if ($assignees->isEmpty()) {
            return $this->status ?? 'pending';
        }

        $allCompleted = $assignees->every(fn($a) => ($a->pivot->status ?? 'pending') === 'completed');
        if ($allCompleted) {
            return 'completed';
        }

        $anyInProgress = $assignees->contains(fn($a) => 
            in_array($a->pivot->status ?? 'pending', ['in_progress', 'under_review', 'completed'])
        );
        if ($anyInProgress) {
            return 'in_progress';
        }

        return 'pending';
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

