<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Application extends Entity
{
    protected $casts = [
        'id'          => 'integer',
        'category_id' => 'integer',
        'version'     => 'integer',
    ];

    protected $dates = [
        'submitted_at',
        'cancelled_at',
        'created_at',
        'updated_at',
    ];
}
