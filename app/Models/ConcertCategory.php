<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ConcertCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'concerts_id', 'ticket_categories_id'
    ];

    public function concert()
    {
        return $this->belongsTo(Concert::class, 'concerts_id');
    }

    public function ticketCategory()
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_categories_id');
    }
}
