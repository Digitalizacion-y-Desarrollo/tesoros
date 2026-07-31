<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table('categories')->upsertBatch([
            [
                'code'         => 'cocineras-cocineros-tradicionales',
                'name'         => 'Cocineras y Cocineros Tradicionales',
                'folio_prefix' => 'CCT',
                'sort_order'   => 1,
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'code'         => 'restaurantes',
                'name'         => 'Restaurantes',
                'folio_prefix' => 'RES',
                'sort_order'   => 2,
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'code'         => 'joven-talento-gastronomia',
                'name'         => 'Joven Talento Universitario en Gastronomía',
                'folio_prefix' => 'JTG',
                'sort_order'   => 3,
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'code'         => 'bebidas-tradicionales-ancestrales',
                'name'         => 'Productoras y Productores de Bebidas Tradicionales y Ancestrales',
                'folio_prefix' => 'BTA',
                'sort_order'   => 4,
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ], 'code');

        $categories = $this->db->table('categories')
            ->select('id')
            ->get()
            ->getResultArray();

        if ($this->db->tableExists('folio_counters')) {
            foreach ($categories as $category) {
                $exists = $this->db->table('folio_counters')
                    ->where('category_id', $category['id'])
                    ->countAllResults() > 0;
                if (! $exists) {
                    $this->db->table('folio_counters')->insert([
                        'category_id' => $category['id'],
                        'last_sequence' => 0,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
}
