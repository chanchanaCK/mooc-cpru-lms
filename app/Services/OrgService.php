<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Program;
use App\Models\ProgramAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrgService
{
    public function __construct(
        private readonly LearningService $learning,
        private readonly CreditBankService $creditBank,
    ) {
    }

    public function createOrganization(User $owner, string $name, int $seats): Organization
    {
        return DB::transaction(function () use ($owner, $name, $seats) {
            $org = Organization::create([
                'name' => $name,
                'slug' => $this->uniqueSlug($name),
                'owner_id' => $owner->id,
                'seats' => $seats,
            ]);

            $org->members()->create([
                'user_id' => $owner->id,
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            return $org;
        });
    }

    /** Add a member and enrol them in everything already assigned to the org. */
    public function addMember(Organization $organization, User $user, string $role = 'member'): OrganizationMember
    {
        return DB::transaction(function () use ($organization, $user, $role) {
            $member = OrganizationMember::firstOrCreate(
                ['organization_id' => $organization->id, 'user_id' => $user->id],
                ['role' => $role, 'joined_at' => now()],
            );

            foreach ($organization->assignedCourses as $course) {
                $this->learning->enroll($user, $course);
            }

            foreach ($organization->assignedPrograms as $program) {
                $this->creditBank->enrollProgram($user, $program);
            }

            return $member;
        });
    }

    public function removeMember(Organization $organization, User $user): void
    {
        $organization->members()->where('user_id', $user->id)->delete();
    }

    /** Assign a course to the org and enrol every current member. */
    public function assignCourse(Organization $organization, Course $course, User $assignedBy, ?string $dueAt = null): CourseAssignment
    {
        return DB::transaction(function () use ($organization, $course, $assignedBy, $dueAt) {
            $assignment = CourseAssignment::firstOrCreate(
                ['organization_id' => $organization->id, 'course_id' => $course->id],
                ['assigned_by' => $assignedBy->id, 'due_at' => $dueAt],
            );

            foreach ($organization->users as $member) {
                $this->learning->enroll($member, $course);
            }

            return $assignment;
        });
    }

    public function unassignCourse(Organization $organization, Course $course): void
    {
        $organization->assignments()->where('course_id', $course->id)->delete();
    }

    /** Assign a program to the org and enrol every current member in it. */
    public function assignProgram(Organization $organization, Program $program, User $assignedBy): ProgramAssignment
    {
        return DB::transaction(function () use ($organization, $program, $assignedBy) {
            $assignment = ProgramAssignment::firstOrCreate(
                ['organization_id' => $organization->id, 'program_id' => $program->id],
                ['assigned_by' => $assignedBy->id],
            );

            foreach ($organization->users as $member) {
                $this->creditBank->enrollProgram($member, $program);
            }

            return $assignment;
        });
    }

    public function unassignProgram(Organization $organization, Program $program): void
    {
        $organization->programAssignments()->where('program_id', $program->id)->delete();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'org-' . Str::lower(Str::random(6));
        $slug = $base;
        $i = 1;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
