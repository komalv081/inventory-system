<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'shop_id' => $this->shop_id,
            'barcode' => $this->barcode,
            'sku' => $this->sku,
            'product_name' => $this->product_name,
            'brand' => $this->brand,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'description' => $this->description,
            'pack_size' => $this->pack_size,
            'unit' => $this->unit,
            'mrp' => $this->mrp,
            'selling_price' => $this->selling_price,
            'purchase_price' => $this->purchase_price,
            'tax_rate' => $this->tax_rate,
            'current_quantity' => $this->current_quantity,
            'minimum_stock_threshold' => $this->minimum_stock_threshold,
            'product_image' => $this->product_image,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
