<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class EnsureFixedCategories extends Migration
{
    public function up(): void
    {
        $now = date('Y-m-d H:i:s');
        foreach ($this->categories() as $category) {
            $existing = $this->db->table('categories')
                ->select('id')
                ->where('code', $category['code'])
                ->get()
                ->getRowArray();

            if ($existing === null) {
                $this->db->table('categories')->insert($category + [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $categoryId = (int) $this->db->insertID();
            } else {
                $categoryId = (int) $existing['id'];
                $this->db->table('categories')->where('id', $categoryId)->update([
                    'name' => $category['name'],
                    'folio_prefix' => $category['folio_prefix'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => 1,
                    'updated_at' => $now,
                ]);
            }

            $counterExists = $this->db->table('folio_counters')
                ->where('category_id', $categoryId)
                ->countAllResults() > 0;
            if (! $counterExists) {
                $this->db->table('folio_counters')->insert([
                    'category_id' => $categoryId,
                    'last_sequence' => $this->highestExistingSequence($categoryId),
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Las categorías son catálogo fijo del dominio. Las migraciones anteriores
        // eliminan sus tablas al revertir completamente el esquema.
    }

    /**
     * @return list<array{code: string, name: string, folio_prefix: string, sort_order: int, is_active: int}>
     */
    private function categories(): array
    {
        return [
            [
                'code' => 'cocineras-cocineros-tradicionales',
                'name' => 'Cocineras y Cocineros Tradicionales',
                'folio_prefix' => 'CCT',
                'sort_order' => 1,
                'is_active' => 1,
            ],
            [
                'code' => 'restaurantes',
                'name' => 'Restaurantes',
                'folio_prefix' => 'RES',
                'sort_order' => 2,
                'is_active' => 1,
            ],
            [
                'code' => 'joven-talento-gastronomia',
                'name' => 'Joven Talento Universitario en Gastronomía',
                'folio_prefix' => 'JTG',
                'sort_order' => 3,
                'is_active' => 1,
            ],
            [
                'code' => 'bebidas-tradicionales-ancestrales',
                'name' => 'Productoras y Productores de Bebidas Tradicionales y Ancestrales',
                'folio_prefix' => 'BTA',
                'sort_order' => 4,
                'is_active' => 1,
            ],
        ];
    }

    private function highestExistingSequence(int $categoryId): int
    {
        $folios = $this->db->table('applications')
            ->select('folio')
            ->where('category_id', $categoryId)
            ->get()
            ->getResultArray();
        $highest = 0;
        foreach ($folios as $row) {
            if (preg_match('/-(\d{6})$/', (string) $row['folio'], $matches) === 1) {
                $highest = max($highest, (int) $matches[1]);
            }
        }

        return $highest;
    }
}
