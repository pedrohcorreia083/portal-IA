<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ServiceOrderItem extends Model
{
    use HasFactory;

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
    ];

    // Cast para services
    protected $casts = [
        'services' => 'array',
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

    // Constantes para status técnico
    const TECHNICAL_STATUS_RP = 'RP'; // Reparo realizado
    const TECHNICAL_STATUS_SC = 'SC'; // Sem conserto
    const TECHNICAL_STATUS_GTC = 'GTC'; // Garantia troca física
    const TECHNICAL_STATUS_GCG = 'GCG'; // Garantia crédito gerado
    const TECHNICAL_STATUS_N = 'N'; // Não é LBF

    const TECHNICAL_STATUSES = [
        self::TECHNICAL_STATUS_RP => 'Reparo realizado',
        self::TECHNICAL_STATUS_SC => 'Sem conserto',
        self::TECHNICAL_STATUS_GTC => 'Garantia troca física',
        self::TECHNICAL_STATUS_GCG => 'Garantia crédito gerado',
        self::TECHNICAL_STATUS_N => 'Não é LBF',
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

    // Accessor para services (versão segura)
    protected function services(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (empty($value)) {
                    return [];
                }

                // Se já for array, retorna diretamente
                if (is_array($value)) {
                    return $value;
                }

                // Se for string, tenta decodificar JSON
                if (is_string($value)) {
                    $decoded = json_decode($value, true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $decoded;
                    }

                    // fallback: string separada por vírgula
                    $services = array_filter(array_map('trim', explode(',', $value)));
                    return array_values($services); // Remove chaves numéricas vazias
                }

                return [];
            },
            set: function ($value) {
                if (is_array($value)) {
                    // Remove valores vazios e mantém apenas os válidos
                    $filtered = array_filter($value, function ($item) {
                        return !empty($item) && trim($item) !== '';
                    });
                    return json_encode(array_values($filtered));
                }

                return $value;
            }
        );
    }

    // Accessor para serviços com nomes
    public function getServicesNamesAttribute(): array
    {
        $services = $this->services;

        if (!is_array($services)) {
            return [];
        }

        return array_map(
            fn($service) => self::SERVICES[$service] ?? $service,
            $services
        );
    }

    public function getTechnicalStatusNameAttribute(): ?string
    {
        return self::TECHNICAL_STATUSES[$this->technical_status] ?? $this->technical_status;
    }

    public function getCoatingNameAttribute(): ?string
    {
        return self::COATINGS[$this->coating] ?? $this->coating;
    }

    // Métodos para análise
    public function hasTechnicalAnalysis(): bool
    {
        return !is_null($this->is_guarantee) && !is_null($this->technical_status);
    }

    public function needsPrice(): bool
    {
        return $this->is_guarantee === false && empty($this->repair_price);
    }

    public function isGuaranteeApproved(): bool
    {
        return $this->is_guarantee === true && in_array($this->technical_status, [
            self::TECHNICAL_STATUS_GTC,
            self::TECHNICAL_STATUS_GCG,
            self::TECHNICAL_STATUS_RP
        ]);
    }

    // Accessors para status de análise
    public function getAnalysisStatusAttribute(): string
    {
        if (is_null($this->is_guarantee) || is_null($this->technical_status)) {
            return 'pending';
        }

        if ($this->is_guarantee === false && empty($this->repair_price)) {
            return 'incomplete';
        }

        return 'completed';
    }

    public function getAnalysisStatusLabelAttribute(): string
    {
        return [
            'pending' => 'Pendente',
            'incomplete' => 'Incompleta',
            'completed' => 'Concluída',
        ][$this->analysis_status] ?? 'Desconhecido';
    }

    public function getAnalysisStatusBadgeClassAttribute(): string
    {
        return [
            'pending' => 'badge-warning',
            'incomplete' => 'badge-danger',
            'completed' => 'badge-success',
        ][$this->analysis_status] ?? 'badge-secondary';
    }

    // Escopos para análise
    public function scopePendingAnalysis($query)
    {
        return $query->whereNull('is_guarantee')
            ->orWhereNull('technical_status');
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
            ->whereNotNull('technical_status')
            ->where(function ($q) {
                $q->where('is_guarantee', true)
                    ->orWhere(function ($q2) {
                        $q2->where('is_guarantee', false)
                            ->whereNotNull('repair_price');
                    });
            });
    }
}
