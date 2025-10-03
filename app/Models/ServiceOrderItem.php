<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'status',
        'observations',
    ];

    protected $casts = [
        'services' => 'array',
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

    // Constantes para serviços
    const SERVICES = [
        'B' => 'Banhar',
        'RP' => 'Repor pedra',
        'F' => 'Trocar fecho',
        'L' => 'Limpar',
        'S' => 'Soldar',
        'RM' => 'Remontar',
    ];

    const STATUSES = [
        'RP' => 'Reparo realizado',
        'SC' => 'Sem conserto',
        'GTC' => 'Garantia troca física da peça',
        'GCG' => 'Garantia crédito gerado',
        'N' => 'Não é LBF',
    ];

    // Métodos de acesso
    public function getServicesNamesAttribute(): array
    {
        return array_map(function ($service) {
            return self::SERVICES[$service] ?? $service;
        }, $this->services ?? []);
    }

    public function getStatusNameAttribute(): ?string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getCoatingNameAttribute(): ?string
    {
        return Product::COATINGS[$this->coating] ?? $this->coating;
    }
}
