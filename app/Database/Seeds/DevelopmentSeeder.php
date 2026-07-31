<?php

namespace App\Database\Seeds;

use App\Services\DraftApplicationService;
use CodeIgniter\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $fixtures = [
            [
                'category' => 'cocineras-cocineros-tradicionales',
                'email' => 'cocinera.demo@example.test',
                'participants' => [$this->participant('GODE561231HDFBCD09', 'María', 'González')],
            ],
            [
                'category' => 'restaurantes',
                'email' => 'restaurante.demo@example.test',
                'participants' => [$this->participant('MARA850101MMCBCR08', 'Laura', 'Martínez')],
            ],
            [
                'category' => 'joven-talento-gastronomia',
                'email' => 'estudiante.demo@example.test',
                'participants' => [
                    $this->participant('LOPE900202HMCDFR07', 'Carlos', 'López'),
                ],
            ],
            [
                'category' => 'bebidas-tradicionales-ancestrales',
                'email' => 'bebida.demo@example.test',
                'participants' => [$this->participant('BETA910404MMCNRL05', 'Teresa', 'Benítez')],
            ],
        ];
        $service = new DraftApplicationService($this->db);

        foreach ($fixtures as $fixture) {
            $emailHash = hash('sha256', $fixture['email']);
            $exists = $this->db->table('applications')
                ->where('email_hash', $emailHash)
                ->countAllResults() > 0;

            if (! $exists) {
                $service->create($fixture['category'], $fixture['email'], $fixture['participants']);
            }
        }
    }

    /**
     * @return array{curp: string, first_name: string, last_name: string, second_last_name: string}
     */
    private function participant(string $curp, string $firstName, string $lastName): array
    {
        return [
            'curp'             => $curp,
            'first_name'       => $firstName,
            'last_name'        => $lastName,
            'second_last_name' => 'Ejemplo',
        ];
    }
}
