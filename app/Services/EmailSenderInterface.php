<?php

namespace App\Services;

interface EmailSenderInterface
{
    public function send(string $recipient, string $subject, string $html, string $plainText): bool;
}
