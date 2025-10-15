<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrder extends Model
{
    use HasFactory;

    // Status da OS
    const STATUS_CREATED = 'created';
    const STATUS_UNDER_ANALYSIS = 'under_analysis';
    const STATUS_APPROVED = 'approved';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

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
        // REMOVIDO: 'is_guarantee' (agora é por produto)
        // NOVOS CAMPOS para controle da análise
        'technical_analyst_id',
        'analysis_date',
    ];

    protected $casts = [
        'order_date' => 'date',
        'analysis_date' => 'datetime',
        // REMOVIDO: 'is_guarantee' => 'boolean',
    ];

    // Relacionamentos
    public function items(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    public function technicalAnalyst(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technical_analyst_id');
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

    // NOVOS ESCOPOS para análise técnica
    public function scopeNeedsAnalysis($query)
    {
        return $query->where('status', self::STATUS_CREATED);
    }

    public function scopeUnderAnalysis($query)
    {
        return $query->where('status', self::STATUS_UNDER_ANALYSIS);
    }

    // Métodos
    public function getTotalItemsAttribute(): int
    {
        return $this->items->count();
    }

    // NOVOS MÉTODOS para análise de garantia
    public function getGuaranteeItemsCountAttribute(): int
    {
        return $this->items->where('is_guarantee', true)->count();
    }

    public function getPaidItemsCountAttribute(): int
    {
        return $this->items->where('is_guarantee', false)->count();
    }

    public function getPendingAnalysisCountAttribute(): int
    {
        return $this->items->where('is_guarantee', null)->count();
    }

    public function getTotalRepairPriceAttribute(): float
    {
        return $this->items->where('is_guarantee', false)->sum('repair_price');
    }

    public function canBeAnalyzed(): bool
    {
        return $this->status === self::STATUS_CREATED;
    }

    public function isUnderAnalysis(): bool
    {
        return $this->status === self::STATUS_UNDER_ANALYSIS;
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_CREATED => 'Criada',
            self::STATUS_UNDER_ANALYSIS => 'Em Análise',
            self::STATUS_APPROVED => 'Aprovada',
            self::STATUS_COMPLETED => 'Concluída',
            self::STATUS_CANCELLED => 'Cancelada',
        ][$this->status] ?? 'Desconhecido';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return [
            self::STATUS_CREATED => 'badge-warning',
            self::STATUS_UNDER_ANALYSIS => 'badge-info',
            self::STATUS_APPROVED => 'badge-primary',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-danger',
        ][$this->status] ?? 'badge-secondary';
    }

    // Geração automática do número da OS
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->order_number)) {
                $model->order_number = 'OS' . date('Ymd') . str_pad(ServiceOrder::count() + 1, 4, '0', STR_PAD_LEFT);
            }
            
            // Status inicial automático
            if (empty($model->status)) {
                $model->status = self::STATUS_CREATED;
            }
        });
    }
}