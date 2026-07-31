<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $returnType = 'array';
    protected $allowedFields = [
        'code',
        'name',
        'folio_prefix',
        'sort_order',
        'is_active',
    ];
    protected $useTimestamps = true;
}
