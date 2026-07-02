<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CustomerSupportTicket extends Model
{
    protected $fillable = ['customer_id', 'ticket_number', 'subject', 'description', 'priority', 'status'];

    protected static function booted(): void
    {
        static::creating(function (CustomerSupportTicket $ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TKT-'.strtoupper(Str::random(8));
            }
        });
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}
