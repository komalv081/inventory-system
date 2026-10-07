<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->shops()->exists();
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $user->shops()->whereKey($supplier->shop_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->shops()->exists();
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $this->view($user, $supplier);
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $this->view($user, $supplier);
    }

    public function deactivate(User $user, Supplier $supplier): bool
    {
        return $this->view($user, $supplier);
    }

    public function reactivate(User $user, Supplier $supplier): bool
    {
        return $this->view($user, $supplier);
    }
}
