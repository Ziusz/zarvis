<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BusinessStaff extends Pivot
{
    protected $casts = [
        'specialties' => 'array',
        'languages' => 'array',
    ];
} 