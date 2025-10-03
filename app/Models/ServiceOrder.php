<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'client_name',
        'client_document',
        'client_phone',
        'consultant_name',
        'client_code',
        'origin',
        'order_date',
        'observations',
        'status',
        'is_guarantee',
    ];

    protected $casts = [
        'order_date' => 'date',
        'is_guarantee' => 'boolean',
    ];

    // Relacionamentos
    public function items(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    // Escopos
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Métodos
    public function getTotalItemsAttribute(): int
    {
        return $this->items->count();
    }

    // Geração automática do número da OS
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->order_number = 'OS' . date('Ymd') . str_pad(ServiceOrder::count() + 1, 4, '0', STR_PAD_LEFT);
        });
    }
}
