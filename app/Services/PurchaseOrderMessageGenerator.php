<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Shop;
use App\Models\Supplier;

class PurchaseOrderMessageGenerator
{
    public function generate(Supplier $supplier, PurchaseOrder $order, Shop $shop): string
    {
        $productList = $order->items
            ->values()
            ->map(fn ($item, int $index) => sprintf(
                '%d. %s - %d %s',
                $index + 1,
                $item->product_name_snapshot,
                $item->quantity,
                $item->unit_snapshot ?: 'pcs'
            ))
            ->implode("\n");

        return strtr((string) config('purchase_orders.message_template'), [
            '{supplier_name}' => $supplier->name,
            '{product_list}' => $productList,
            '{shop_name}' => $shop->name,
        ]);
    }
}