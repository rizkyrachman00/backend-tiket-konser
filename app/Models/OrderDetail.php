<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'orders_id', 'concerts_id', 'concert_categories_id', 'qty', 'price'

    ];

    public function concertCategory()
    {
        return $this->belongsTo(ConcertCategory::class, 'concert_categories_id', 'id');
    }

    

}
