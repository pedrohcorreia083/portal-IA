<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'purchase_date',
        'piece_value',
        'is_guarantee',
        'repair_price',
        'technical_status',
        'technical_observations',
        'ai_identified_code',
    ];

    protected $casts = [
        'services' => 'array',
        'purchase_date' => 'date',
        'piece_value' => 'decimal:2',
        'is_guarantee' => 'boolean',
    ];

    // Resultado da análise técnica
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

    // Cores para badges
    const STATUS_COLORS = [
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
    public function technicalSheet()
    {
        return $this->hasOne(TechnicalSheet::class);
    }

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getServicesNamesAttribute(): array
    {
        $services = $this->services ?? [];

        // Garantia extra caso venha string
        if (is_string($services)) {
            $services = json_decode($services, true) ?? [];
        }

        if (! is_array($services)) {
            $services = [];
        }

        return array_map(
            fn ($service) => self::SERVICES[$service] ?? $service,
            $services
        );
    }

    public function getTechnicalStatusNameAttribute(): string
    {
        return self::TECHNICAL_STATUSES[$this->technical_status] ?? $this->technical_status ?? 'Pendente';
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->technical_status] ?? 'secondary';
    }

    public function getCoatingNameAttribute(): ?string
    {
        if (empty($this->coating)) {
            return null;
        }

        return self::COATINGS[$this->coating] ?? $this->coating;
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
}
