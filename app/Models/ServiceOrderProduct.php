<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOrderProduct extends Model
{
    protected $table = 'service_order_items';

    public function serviceOrder()
    {
        return $this->belongsTo(
            ServiceOrder::class,
            'service_order_id'
        );
    }
}