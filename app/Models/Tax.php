<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $fillable = ['name', 'percentage', 'hsn_code', 'description', 'status'];

    protected function casts(): array
    {
        return ['percentage' => 'decimal:2'];
    }
}
