<?php

namespace App\Domain;

enum ApplicationStatus: string
{
    case Draft = 'borrador';
    case Submitted = 'enviada';
    case UnderReview = 'en_revision';
    case Incomplete = 'incompleta';
    case Selected = 'seleccionada';
    case Rejected = 'rechazada';
    case Cancelled = 'cancelada';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return list<string> */
    public static function allowedAdminTransitions(string $from): array
    {
        return match ($from) {
            self::Submitted->value => [
                self::UnderReview->value,
                self::Incomplete->value,
                self::Selected->value,
                self::Rejected->value,
            ],
            self::UnderReview->value => [
                self::Incomplete->value,
                self::Selected->value,
                self::Rejected->value,
            ],
            default => [],
        };
    }

    public static function canParticipantCancel(string $status): bool
    {
        return in_array($status, [
            self::Draft->value,
            self::Submitted->value,
            self::Incomplete->value,
        ], true);
    }
}
