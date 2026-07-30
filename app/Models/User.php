<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'headline',
        'bio',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isInstructor(): bool
    {
        return in_array($this->role, ['instructor', 'admin'], true);
    }

    /** Registrar (นายทะเบียน) — manages programs & approves credit transfers. */
    public function isRegistrar(): bool
    {
        return in_array($this->role, ['registrar', 'admin'], true);
    }

    public function isEnrolledIn(Course $course): bool
    {
        return $this->enrollments()->where('course_id', $course->id)->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /** Courses this user teaches (as instructor). */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /** Courses this user is enrolled in (as student). */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments')
            ->withPivot(['progress_percent', 'completed_at'])
            ->withTimestamps();
    }

    public function lessonCompletions(): HasMany
    {
        return $this->hasMany(LessonCompletion::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /** The learner's credit bank ledger (คลังหน่วยกิต). */
    public function creditRecords(): HasMany
    {
        return $this->hasMany(CreditRecord::class);
    }

    /** Credit-transfer / RPL requests submitted by this learner. */
    public function transferRequests(): HasMany
    {
        return $this->hasMany(CreditTransferRequest::class);
    }

    /** Total earned credits currently in the bank. */
    public function getTotalCreditsAttribute(): float
    {
        return (float) $this->creditRecords()->where('status', 'earned')->sum('credits');
    }

    /** Programs this user is enrolled in (คลังหน่วยกิต → หลักสูตร). */
    public function programEnrollments(): HasMany
    {
        return $this->hasMany(ProgramEnrollment::class);
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_enrollments')
            ->withPivot(['status', 'credits_earned', 'completed_at', 'certificate_number'])
            ->withTimestamps();
    }

    /** Programs this user administers (as registrar/owner). */
    public function ownedPrograms(): HasMany
    {
        return $this->hasMany(Program::class, 'owner_id');
    }

    public function ownedOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }

    /** Organizations this user belongs to (any role). */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    /** Organizations this user can manage (owner or manager). */
    public function manageableOrganizations()
    {
        return $this->organizations()->wherePivotIn('role', ['owner', 'manager']);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    /** Whether the course is already in the user's cart. */
    public function hasInCart(Course $course): bool
    {
        return $this->cartItems()->where('course_id', $course->id)->exists();
    }
}
