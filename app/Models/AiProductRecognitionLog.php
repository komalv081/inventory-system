<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiProductRecognitionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'user_id',
        'product_id',
        'provider',
        'request_payload',
        'response_payload',
        'confidence',
        'status',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'confidence' => 'decimal:2',
    ];

    public function shop(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
