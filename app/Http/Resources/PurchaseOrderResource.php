<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value,
            'channel' => $this->channel?->value,
            'message' => $this->message,
            'sent_at' => $this->sent_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'supplier' => $this->whenLoaded('supplier', fn () => [
                'id' => $this->supplier?->id,
                'name' => $this->supplier?->name,
                'phone' => $this->supplier?->phone,
                'whatsapp_phone' => $this->supplier?->whatsapp_phone,
            ]),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name_snapshot ?? $item->product?->product_name,
                'barcode' => $item->barcode_snapshot,
                'unit' => $item->unit_snapshot,
                'quantity' => $item->quantity,
            ])),
            'communications' => $this->whenLoaded('communications', fn () => $this->communications->map(fn ($communication) => [
                'id' => $communication->id,
                'channel' => $communication->channel->value,
                'status' => $communication->status->value,
                'message' => $communication->message,
                'sent_at' => $communication->sent_at,
                'created_at' => $communication->created_at,
            ])),
        ];
    }
}