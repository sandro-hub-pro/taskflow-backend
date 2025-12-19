<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * User who created the project.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * All users in this project.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get incharges of this project.
     */
    public function incharges(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users')
            ->wherePivot('role', 'incharge')
            ->withTimestamps();
    }

    /**
     * Get members of this project.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users')
            ->wherePivot('role', 'member')
            ->withTimestamps();
    }

    /**
     * Tasks in this project.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the project progress based on tasks.
     */
    public function getProgressAttribute(): int
    {
        $tasks = $this->tasks;
        
        if ($tasks->isEmpty()) {
            return 0;
        }

        return (int) round($tasks->avg('progress'));
    }

    /**
     * Get completed tasks count.
     */
    public function getCompletedTasksCountAttribute(): int
    {
        return $this->tasks()->where('status', 'completed')->count();
    }

    /**
     * Get total tasks count.
     */
    public function getTotalTasksCountAttribute(): int
    {
        return $this->tasks()->count();
    }
}

