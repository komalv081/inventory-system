<?php

namespace App\Policies;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->shops()->exists();
    }

    public function create(User $user): bool
    {
        return $user->shops()->exists();
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->shops()->whereKey($purchaseOrder->shop_id)->exists();
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->view($user, $purchaseOrder)
            && in_array($purchaseOrder->status, [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Reviewed], true);
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->view($user, $purchaseOrder)
            && in_array($purchaseOrder->status, [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Reviewed], true);
    }
}