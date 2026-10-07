<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;

class ShopPolicy
{
    public function view(User $user, Shop $shop): bool
    {
        return $user->belongsToShop($shop);
    }

    public function update(User $user, Shop $shop): bool
    {
        return $user->belongsToShop($shop);
    }
}
