<?php

namespace App\Services;

interface ParticipantRegistrationMailerInterface
{
    public function send(string $recipient, string $folio, string $categoryName): bool;
}
