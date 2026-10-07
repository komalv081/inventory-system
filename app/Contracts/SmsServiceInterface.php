<?php

namespace App\Contracts;

interface SmsServiceInterface
{
    public function handoff(string $phoneNumber, string $message): array;
}