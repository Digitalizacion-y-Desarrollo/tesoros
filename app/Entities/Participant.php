<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Participant extends Entity
{
    protected $casts = [
        'id'             => 'integer',
        'application_id' => 'integer',
        'member_number'  => 'integer',
        'is_primary'     => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
