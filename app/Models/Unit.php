<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'sam_unit';

    public $timestamps = false;
    
    protected $fillable = [
        'unit',
        'registered_by',
    ];
}
