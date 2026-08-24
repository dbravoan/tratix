<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    public function view(User $user, Contract $contract): bool
    {
        return $contract->isOwnedBy($user);
    }

    public function update(User $user, Contract $contract): bool
    {
        return $contract->isOwnedBy($user);
    }

    public function delete(User $user, Contract $contract): bool
    {
        return $contract->isOwnedBy($user);
    }
}
