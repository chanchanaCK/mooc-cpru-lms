<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    /** Registrars (and the program's owner) may author/manage programs. */
    public function manage(User $user, Program $program): bool
    {
        return $user->isRegistrar() || $program->owner_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isRegistrar();
    }

    public function update(User $user, Program $program): bool
    {
        return $this->manage($user, $program);
    }

    public function delete(User $user, Program $program): bool
    {
        return $this->manage($user, $program);
    }
}
