<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ServiceOrderProduct;

class ServiceOrderItem extends Model
{
    use HasFactory;

    protected $table = 'service_order_items';

    protected $fillable = [
        'service_order_id',
        'product_id',
        'product_image',
        'identified_product_name',
        'services',
        'coating',
        'observations',
        'is_guarantee',
        'repair_price',
        'technical_status',
        'technical_observations',
        'ai_identified_code',
        'item_status',
        'analysis_started_at',
        // 'analysis_completed_at',
        'repair_started_at',
        'repair_completed_at',
        'quality_started_at',
        'quality_completed_at',
        'analyzed_by',
        'repaired_by',
        'quality_checked_by',
    ];

    protected $casts = [
        'services' => 'array',
        'is_guarantee' => 'boolean',
        'analysis_started_at' => 'datetime',
        // 'analysis_completed_at' => 'datetime',
        'repair_started_at' => 'datetime',
        'repair_completed_at' => 'datetime',
        'quality_started_at' => 'datetime',
        'quality_completed_at' => 'datetime',
    ];

    // Constantes para status técnico
    const TECH_STATUS_RP = 'RP';
    const TECH_STATUS_SC = 'SC';
    const TECH_STATUS_GTC = 'GTC';
    const TECH_STATUS_GCG = 'GCG';
    const TECH_STATUS_N = 'N';

    const TECHNICAL_STATUSES = [
        self::TECH_STATUS_RP => 'Reparo realizado',
        self::TECH_STATUS_SC => 'Sem conserto',
        self::TECH_STATUS_GTC => 'Garantia troca física',
        self::TECH_STATUS_GCG => 'Garantia crédito gerado',
        self::TECH_STATUS_N => 'Não é LBF',
    ];

    // Constantes para status do item
    const ITEM_STATUS_PENDING = 'pending';
    const ITEM_STATUS_IN_ANALYSIS = 'in_analysis';
    const ITEM_STATUS_WAITING_PARTS = 'waiting_parts';
    const ITEM_STATUS_IN_REPAIR = 'in_repair';
    const ITEM_STATUS_QUALITY_CHECK = 'quality_check';
    const ITEM_STATUS_COMPLETED = 'completed';
    const ITEM_STATUS_CANCELLED = 'cancelled';

    const ITEM_STATUSES = [
        self::ITEM_STATUS_PENDING => 'Pendente',
        self::ITEM_STATUS_IN_ANALYSIS => 'Em Análise Técnica',
        self::ITEM_STATUS_WAITING_PARTS => 'Aguardando Peças',
        self::ITEM_STATUS_IN_REPAIR => 'Em Reparo',
        self::ITEM_STATUS_QUALITY_CHECK => 'Controle de Qualidade',
        self::ITEM_STATUS_COMPLETED => 'Concluído',
        self::ITEM_STATUS_CANCELLED => 'Cancelado',
    ];

    // Cores para badges
    const STATUS_COLORS = [
        'pending' => 'secondary',
        'in_analysis' => 'info',
        'waiting_parts' => 'warning',
        'in_repair' => 'primary',
        'quality_check' => 'dark',
        'completed' => 'success',
        'cancelled' => 'danger',
        'RP' => 'success',
        'SC' => 'danger',
        'GTC' => 'info',
        'GCG' => 'warning',
        'N' => 'secondary',
    ];

    // Constantes para serviços
    const SERVICES = [
        'B' => 'Banhar',
        'RP' => 'Repor pedra',
        'F' => 'Trocar fecho',
        'L' => 'Limpar',
        'S' => 'Soldar',
        'RM' => 'Remontar',
    ];

    // Constantes para banhos
    const COATINGS = [
        'DO' => 'Dourado',
        'RH' => 'Ródio branco',
        'RO' => 'Rose',
        'NE' => 'Ródio negro',
        'MIX' => 'Mix de banhos',
        'CL' => 'Colorido',
    ];

    // Relacionamentos
    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function analyzedBy()
    {
        return $this->belongsTo(User::class, 'analyzed_by');
    }

    public function repairedBy()
    {
        return $this->belongsTo(User::class, 'repaired_by');
    }

    public function qualityCheckedBy()
    {
        return $this->belongsTo(User::class, 'quality_checked_by');
    }

    // Accessors (com tratamento para null)
    // public function getServicesAttribute($value): array
    // {
    //     if (empty($value)) {
    //         return [];
    //     }

    //     if (is_string($value)) {
    //         $decoded = json_decode($value, true);
    //         if (json_last_error() === JSON_ERROR_NONE) {
    //             return $decoded;
    //         }
    //         return array_filter(array_map('trim', explode(',', $value)));
    //     }

    //     return is_array($value) ? $value : [];
    // }

    public function getServicesNamesAttribute(): array
    {
        $services = $this->services ?? [];

        // Garantia extra caso venha string
        if (is_string($services)) {
            $services = json_decode($services, true) ?? [];
        }

        if (!is_array($services)) {
            $services = [];
        }

        return array_map(
            fn($service) => self::SERVICES[$service] ?? $service,
            $services
        );
    }

    public function getTechnicalStatusNameAttribute(): string
    {
        return self::TECHNICAL_STATUSES[$this->technical_status] ?? $this->technical_status ?? 'Pendente';
    }

    // CORREÇÃO AQUI - Trata valores nulos
    public function getItemStatusNameAttribute(): string
    {
        // Se for null, retorna o status padrão
        if (is_null($this->item_status)) {
            return 'Pendente';
        }

        return self::ITEM_STATUSES[$this->item_status] ?? $this->item_status;
    }

    // CORREÇÃO AQUI - Trata valores nulos
    public function getStatusColorAttribute(): string
    {
        // Primeiro tenta o item_status
        if (!is_null($this->item_status)) {
            return self::STATUS_COLORS[$this->item_status] ?? 'secondary';
        }

        // Se não tem item_status, tenta o technical_status
        if (!is_null($this->technical_status)) {
            return self::STATUS_COLORS[$this->technical_status] ?? 'secondary';
        }

        return 'secondary';
    }

    public function getCoatingNameAttribute(): ?string
    {
        if (empty($this->coating)) {
            return null;
        }
        return self::COATINGS[$this->coating] ?? $this->coating;
    }

    // Métodos de análise técnica
    public function needsAnalysis(): bool
    {
        return is_null($this->is_guarantee) && is_null($this->technical_status);
    }

    public function hasTechnicalAnalysis(): bool
    {
        return !is_null($this->is_guarantee) && !is_null($this->technical_status);
    }

    public function needsPrice(): bool
    {
        return $this->is_guarantee === false && empty($this->repair_price);
    }

    public function canBeRepaired(): bool
    {
        return $this->hasTechnicalAnalysis() &&
            !$this->is_guarantee &&
            !empty($this->repair_price) &&
            $this->item_status === self::ITEM_STATUS_PENDING;
    }

    public function startAnalysis()
    {
        $this->update([
            'item_status' => self::ITEM_STATUS_IN_ANALYSIS,
            'analysis_started_at' => now(),
        ]);
    }

    public function completeAnalysis($technicalStatus, $isGuarantee = null, $repairPrice = null, $observations = null)
    {
        $data = [
            'technical_status' => $technicalStatus,
            'technical_observations' => $observations,
            // 'analysis_completed_at' => now(),
            // 'analyzed_by' => auth()->id(),
        ];

        if (!is_null($isGuarantee)) {
            $data['is_guarantee'] = $isGuarantee;
        }

        if (!is_null($repairPrice) && !$isGuarantee) {
            $data['repair_price'] = $repairPrice;
        }

        // Define o status inicial do item após análise
        if (
            $technicalStatus === self::TECH_STATUS_RP ||
            $technicalStatus === self::TECH_STATUS_SC ||
            $technicalStatus === self::TECH_STATUS_GTC ||
            $technicalStatus === self::TECH_STATUS_GCG ||
            $technicalStatus === self::TECH_STATUS_N
        )

            $this->update($data);
    }

    public function startRepair()
    {
        $this->update([
            'item_status' => self::ITEM_STATUS_IN_REPAIR,
            'repair_started_at' => now(),
        ]);
    }

    public function completeRepair()
    {
        $this->update([
            'item_status' => self::ITEM_STATUS_QUALITY_CHECK,
            'repair_completed_at' => now(),
            'repaired_by' => auth()->id(),
        ]);
    }

    public function approveQuality()
    {
        $this->update([
            'item_status' => self::ITEM_STATUS_COMPLETED,
            'quality_completed_at' => now(),
            'quality_checked_by' => auth()->id(),
        ]);
    }

    public function rejectQuality($observations = null)
    {
        $this->update([
            'item_status' => self::ITEM_STATUS_IN_REPAIR,
            'technical_observations' => $observations,
        ]);
    }

    // Escopos
    public function scopePendingAnalysis($query)
    {
        return $query->whereNull('is_guarantee')->orWhereNull('technical_status');
    }

    public function scopeGuarantee($query)
    {
        return $query->where('is_guarantee', true);
    }

    public function scopePaid($query)
    {
        return $query->where('is_guarantee', false);
    }

    public function scopeWithCompleteAnalysis($query)
    {
        return $query->whereNotNull('is_guarantee')
            ->whereNotNull('technical_status');
    }

    public function products()
    {
        return $this->hasMany(
            ServiceOrderProduct::class,
            'service_order_id'
        );
    }
}
