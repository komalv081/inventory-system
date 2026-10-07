<?php

namespace App\Enums;

enum MessageChannel: string
{
    case WhatsApp = 'whatsapp';
    case Sms = 'sms';
}
