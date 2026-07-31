<?php

namespace App\Models;

use CodeIgniter\Model;

class FolioCounterModel extends Model
{
    protected $table = 'folio_counters';
    protected $primaryKey = 'category_id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'category_id',
        'last_sequence',
        'updated_at',
    ];
    protected $useAutoIncrement = false;
    protected $useTimestamps = false;
}
