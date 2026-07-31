<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Tesoros extends BaseConfig
{
    public string $privateUploadPath = WRITEPATH . 'private/uploads';

    /**
     * @var array<string, array{name: string, folioPrefix: string}>
     */
    public array $categories = [
        'cocineras-cocineros-tradicionales' => [
            'name'        => 'Cocineras y Cocineros Tradicionales',
            'folioPrefix' => 'CCT',
        ],
        'restaurantes' => [
            'name'        => 'Restaurantes',
            'folioPrefix' => 'RES',
        ],
        'joven-talento-gastronomia' => [
            'name'        => 'Joven Talento Universitario en Gastronomía',
            'folioPrefix' => 'JTG',
        ],
        'bebidas-tradicionales-ancestrales' => [
            'name'        => 'Productoras y Productores de Bebidas Tradicionales y Ancestrales',
            'folioPrefix' => 'BTA',
        ],
    ];
}
