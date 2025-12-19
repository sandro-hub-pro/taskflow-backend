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
            ->withPivot('assigned_by')
            ->withTimestamps();
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

