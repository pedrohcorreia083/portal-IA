<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrder extends Model
{
    use HasFactory;

    public const REGISTRATION_TIMEZONE = 'America/Sao_Paulo';

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
    public function scopeRegisteredDuring(Builder $query, ?string $from, ?string $to): Builder
    {
        $storageTimezone = config('app.timezone');

        if ($from !== null) {
            $start = CarbonImmutable::parse($from, self::REGISTRATION_TIMEZONE)->startOfDay();
            $query->where('created_at', '>=', $start->setTimezone($storageTimezone));
        }

        if ($to !== null) {
            $nextDay = CarbonImmutable::parse($to, self::REGISTRATION_TIMEZONE)->addDay()->startOfDay();
            $query->where('created_at', '<', $nextDay->setTimezone($storageTimezone));
        }

        return $query;
    }

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
            self::STATUS_APPROVED => 'Análise finalizada',
            self::STATUS_UNDER_ANALYSIS => 'Em atendimento',
            self::STATUS_COMPLETED => 'Concluída',
            self::STATUS_CANCELLED => 'Cancelada',
            'pending' => 'Pendente',
            'in_progress' => 'Em atendimento',

        ][$this->status] ?? 'Desconhecido';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return [
            self::STATUS_CREATED => 'badge-warning',
            self::STATUS_APPROVED => 'badge-info',
            self::STATUS_UNDER_ANALYSIS => 'badge-info',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-danger',
            'pending' => 'badge-warning',
            'in_progress' => 'badge-info',
        ][$this->status] ?? 'badge-secondary';
    }

    // Geração automática do número da OS
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->order_number)) {
                $lastOrder = ServiceOrder::orderBy('id', 'desc')->first();
                $nextNumber = $lastOrder
                    ? intval(substr($lastOrder->order_number, -4)) + 1
                    : 1;

                $model->order_number = 'OS'.date('Ymd').str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }

            if (empty($model->status)) {
                $model->status = self::STATUS_CREATED;
            }
        });
    }

    public function products()
    {
        return $this->hasMany(
            ServiceOrderProduct::class,
            'service_order_id'
        );
    }
}
