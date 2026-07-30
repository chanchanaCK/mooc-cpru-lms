<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = ['name', 'slug', 'owner_id', 'seats'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CourseAssignment::class);
    }

    public function assignedCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_assignments')
            ->withPivot(['due_at', 'assigned_by'])
            ->withTimestamps();
    }

    public function programAssignments(): HasMany
    {
        return $this->hasMany(ProgramAssignment::class);
    }

    public function assignedPrograms(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_assignments')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    /** The membership role of a user in this org, or null. */
    public function memberRole(?User $user): ?string
    {
        return $user ? $this->members()->where('user_id', $user->id)->value('role') : null;
    }

    public function canManage(?User $user): bool
    {
        return in_array($this->memberRole($user), ['owner', 'manager'], true);
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }
}
