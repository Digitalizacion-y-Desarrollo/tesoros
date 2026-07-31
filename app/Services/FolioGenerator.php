<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use DomainException;
use OverflowException;

final class FolioGenerator
{
    private const MAX_SEQUENCE = 999999;

    public function __construct(private readonly BaseConnection $db)
    {
    }

    /**
     * Must be called inside the transaction that creates the application.
     *
     * @param array{id: int|string, folio_prefix: string} $category
     */
    public function next(array $category): string
    {
        $categoryId = (int) $category['id'];
        $prefix = strtoupper($category['folio_prefix']);

        if ($categoryId < 1 || ! preg_match('/^[A-Z]{3}$/', $prefix)) {
            throw new DomainException('La categoría no puede generar folios.');
        }

        $query = $this->db->query(
            'SELECT last_sequence FROM folio_counters WHERE category_id = ? FOR UPDATE',
            [$categoryId],
        );

        if ($query === false) {
            $error = $this->db->error();

            throw new DomainException(
                $error['message'] !== '' ? $error['message'] : 'No fue posible bloquear el contador de folios.',
            );
        }

        $counter = $query->getRowArray();

        if ($counter === null) {
            throw new DomainException('No existe un contador para la categoría.');
        }

        $sequence = (int) $counter['last_sequence'] + 1;

        if ($sequence > self::MAX_SEQUENCE) {
            throw new OverflowException('La secuencia de folios de la categoría se agotó.');
        }

        $this->db->table('folio_counters')
            ->where('category_id', $categoryId)
            ->update([
                'last_sequence' => $sequence,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

        return sprintf('TG-2026-%s-%06d', $prefix, $sequence);
    }
}
