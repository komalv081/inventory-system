<?php

namespace App\Models;

use App\Enums\CommunicationStatus;
use App\Enums\MessageChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierCommunication extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'supplier_id',
        'purchase_order_id',
        'channel',
        'message',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'channel' => MessageChannel::class,
        'status' => CommunicationStatus::class,
        'sent_at' => 'datetime',
    ];

    public function shop(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
