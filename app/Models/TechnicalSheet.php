<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicalSheet extends Model
{
    protected $guarded = ['id'];

    protected $attributes = [
        'status' => 'pending',
        'version' => 0,
        'changes_coating' => false,
        'changes_color' => false,
    ];

    protected $casts = [
        'is_guarantee' => 'boolean',
        'changes_coating' => 'boolean',
        'changes_color' => 'boolean',
        'repair_price' => 'decimal:2',
        'version' => 'integer',
    ];

    public const STATUSES = [
        'pending' => 'Aguardando triagem',
        'in_analysis' => 'Em triagem',
        'awaiting_customer' => 'Aguardando cliente',
        'ready_for_service' => 'Pronta para serviço',
        'in_service' => 'Em serviço',
        'awaiting_return' => 'Aguardando retorno do serviço',
        'inspection' => 'Em inspeção',
        'credit_pending' => 'Aguardando crédito',
        'return_pending' => 'Aguardando finalização da devolução',
        'completed' => 'Item encerrado',
        'sent_to_store' => 'Enviado para loja',
        'delivered' => 'Entregue ao cliente',
        'cancelled' => 'Cancelado / recusado',
    ];

    public const TREATMENTS = [
        'bath' => 'Banho',
        'weld_bath' => 'Solda e banho',
        'assembly' => 'Montagem',
        'gluing' => 'Colagem',
        'replace_stone' => 'Repor pedra',
        'return_without_repair' => 'Devolução sem reparo',
        'other' => 'Outros',
        'exchange' => 'Troca com estoque',
        'credit' => 'Gerar crédito',
    ];

    public const FINISH_TYPES = [
        'matte' => 'Fosco',
        'polished' => 'Polido',
        'component' => 'Componente',
        'tying' => 'Amarração',
    ];

    public const COLORS = [
        'golden' => 'Dourado',
        'white_rhodium' => 'Ródio branco',
        'black_rhodium' => 'Ródio negro',
        'rose' => 'Rosé',
        'mixed_bath' => 'Mix de banho',
    ];

    public const CLOSED_STATUSES = ['completed', 'sent_to_store', 'delivered', 'cancelled'];

    public function item()
    {
        return $this->belongsTo(ServiceOrderItem::class, 'service_order_item_id');
    }

    public function events()
    {
        return $this->hasMany(TechnicalSheetEvent::class)->orderByDesc('id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'Aguardando triagem';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed', 'delivered' => 'success',
            'cancelled' => 'danger',
            'awaiting_customer', 'awaiting_return', 'credit_pending', 'return_pending' => 'warning',
            'pending' => 'secondary',
            default => 'info',
        };
    }

    public function availableActions(): array
    {
        $actions = match ($this->status) {
            'pending' => ['start_analysis' => 'Iniciar triagem'],
            'awaiting_customer' => ['approve' => 'Registrar aprovação do cliente', 'decline' => 'Registrar recusa do cliente'],
            'ready_for_service' => $this->treatment === 'exchange'
                ? ['exchange' => 'Confirmar troca com estoque']
                : ['start_service' => 'Enviar para serviço'],
            'in_service' => in_array($this->treatment, ['bath', 'weld_bath'], true)
                ? ['send_out' => 'Registrar envio e aguardar retorno']
                : ['finish_service' => 'Concluir montagem e enviar para inspeção'],
            'awaiting_return' => ['receive' => 'Receber serviço e enviar para inspeção'],
            'inspection' => ['pass' => 'Aprovar inspeção e encerrar item', 'rework' => 'Reprovar e devolver para serviço'],
            'credit_pending' => ['confirm_credit' => 'Confirmar crédito gerado'],
            'return_pending' => ['complete_return' => 'Finalizar devolução sem reparo'],
            'completed' => ['send_store' => 'Registrar envio para loja', 'deliver' => 'Registrar entrega ao cliente'],
            'sent_to_store' => ['deliver' => 'Registrar entrega ao cliente'],
            default => [],
        };

        if (in_array($this->status, ['awaiting_customer', 'ready_for_service', 'credit_pending', 'return_pending'], true)) {
            $actions['revise_triage'] = 'Reabrir triagem / escolher outra solução';
        }

        return $actions;
    }
}
