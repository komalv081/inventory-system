<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case Reviewed = 'reviewed';
    case Sent = 'sent';
    case Cancelled = 'cancelled';
}
