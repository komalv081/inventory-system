<?php

namespace App\Contracts;

interface WhatsAppServiceInterface
{
    public function handoff(string $phoneNumber, string $message): string;
}