<?php

namespace App\Models;

use App\Entities\Participant;
use CodeIgniter\Model;

class ParticipantModel extends Model
{
    protected $table = 'participants';
    protected $returnType = Participant::class;
    protected $allowedFields = [
        'application_id',
        'member_number',
        'role',
        'is_primary',
        'curp',
        'first_name',
        'last_name',
        'second_last_name',
    ];
    protected $useTimestamps = true;
}
