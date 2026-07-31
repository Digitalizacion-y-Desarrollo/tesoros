<?php

namespace App\Services;

interface ParticipantCodeMailerInterface
{
    public function send(string $recipient, string $folio, string $code, string $expiresAt): bool;
}
