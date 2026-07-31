<?php

namespace App\Models;

use CodeIgniter\Model;

class ApplicationHistoryModel extends Model
{
    protected $table = 'application_histories';
    protected $returnType = 'array';
    protected $allowedFields = [
        'application_id',
        'action',
        'from_status',
        'to_status',
        'actor_type',
        'actor_reference',
        'metadata',
        'created_at',
    ];
    protected $useTimestamps = false;
}
