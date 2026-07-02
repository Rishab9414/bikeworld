<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{
    protected $fillable = [
        'name', 'address', 'gst_number', 'email', 'phone', 'contact_person', 'status',
    ];
}
