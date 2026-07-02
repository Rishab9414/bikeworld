<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = ['invoice_no', 'order_id', 'invoice_pdf', 'invoice_date'];

    protected function casts(): array
    {
        return ['invoice_date' => 'date'];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
