<?php

namespace App\Enums;

enum CommunicationStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Opened = 'opened';
    case Sent = 'sent';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}
