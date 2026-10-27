<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicalSheetEvent extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['details' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
