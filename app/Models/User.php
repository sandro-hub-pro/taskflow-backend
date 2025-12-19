<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'profile_picture',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        $middleName = $this->middle_name ? " {$this->middle_name}" : '';
        return "{$this->first_name}{$middleName} {$this->last_name}";
    }

    /**
     * Check if user is superadmin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if user is admin (includes superadmin).
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['superadmin', 'admin']);
    }

    /**
     * Check if user is only admin (not superadmin).
     */
    public function isOnlyAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is incharge.
     */
    public function isIncharge(): bool
    {
        return $this->role === 'incharge';
    }

    /**
     * Check if email is verified.
     * Admin and incharge roles skip email verification.
     */
    public function hasVerifiedEmail(): bool
    {
        // Admin and incharge don't need email verification
        if ($this->isAdmin() || $this->isIncharge()) {
            return true;
        }

        return !is_null($this->email_verified_at);
    }

    /**
     * Projects the user is assigned to.
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Projects created by the user.
     */
    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    /**
     * Tasks assigned to the user.
     */
    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_assignments')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    /**
     * Tasks created by the user.
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Task comments by the user.
     */
    public function taskComments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    /**
     * Check if user is incharge of a specific project.
     */
    public function isProjectIncharge(int $projectId): bool
    {
        return $this->projects()
            ->where('project_id', $projectId)
            ->wherePivot('role', 'incharge')
            ->exists();
    }

    /**
     * Check if user is member of a specific project.
     */
    public function isProjectMember(int $projectId): bool
    {
        return $this->projects()->where('project_id', $projectId)->exists();
    }

    /**
     * Send the email verification notification.
     * Only sends for regular users (not admin/incharge).
     */
    public function sendEmailVerificationNotification(): void
    {
        // Admin and incharge don't need email verification
        if ($this->isAdmin() || $this->isIncharge()) {
            return;
        }

        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
