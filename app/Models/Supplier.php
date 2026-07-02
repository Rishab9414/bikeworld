<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'gst', 'address', 'city', 'state', 'country',
        'mobile', 'email', 'bank_name', 'bank_account', 'ifsc_code', 'status',
    ];
}
