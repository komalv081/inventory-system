<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->shops()->exists();
    }

    public function view(User $user, Product $product): bool
    {
        return $user->shops()->whereKey($product->shop_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->shops()->exists();
    }

    public function update(User $user, Product $product): bool
    {
        return $this->view($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->view($user, $product);
    }
}
