<?php

namespace Database\Seeders;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\User;
use App\Services\TechnicalSheetWorkflow;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Dados de demonstração para apresentações comerciais.
 *
 * Nunca roda em produção. Cria um cenário com ordens em todos os estágios
 * do fluxo, para que o painel e as listagens tenham conteúdo realista.
 *
 * Uso: php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    private TechnicalSheetWorkflow $workflow;

    private int $adminId;

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->error('O DemoSeeder não pode rodar em produção.');

            return;
        }

        $this->workflow = app(TechnicalSheetWorkflow::class);

        $admin = User::updateOrCreate(
            ['email' => 'admin@demo.local'],
            [
                'name' => 'Marina', 'last_name' => 'Albuquerque',
                'password' => Hash::make('senha123'),
                'type' => 'admin', 'is_active' => true, 'email_verified_at' => now(),
            ]
        );

        $this->adminId = $admin->id;

        User::updateOrCreate(
            ['email' => 'tecnico@demo.local'],
            [
                'name' => 'Rafael', 'last_name' => 'Nunes',
                'password' => Hash::make('senha123'),
                'type' => 'technical', 'is_active' => true, 'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'consulta@demo.local'],
            [
                'name' => 'Beatriz', 'last_name' => 'Lima',
                'password' => Hash::make('senha123'),
                'type' => 'user', 'is_active' => true, 'email_verified_at' => now(),
            ]
        );

        $this->seedOrders();

        $this->command->info('Demo criada. Acesse com admin@demo.local / senha123');
    }

    private function seedOrders(): void
    {
        $clients = [
            ['Helena Prado', 'SHOWROOM SP', 'Carla Menezes'],
            ['Studio Aurora', 'SHOWROOM LIMEIRA', 'Denise Rocha'],
            ['Renata Vasques', 'SHOWROOM ON-LINE', 'Carla Menezes'],
            ['Luiza Ferraz', 'E-COMMERCE', 'Paula Simões'],
            ['Ateliê Cor', 'EVENTOS', 'Denise Rocha'],
            ['Sofia Andrade', 'SHOWROOM SP', 'Paula Simões'],
            ['Marina Duarte', 'SHOWROOM LIMEIRA', 'Carla Menezes'],
            ['Boutique Lume', 'E-COMMERCE', 'Denise Rocha'],
        ];

        $pieces = [
            ['Anel Solitário', ['B'], 'DO', 189.90],
            ['Brinco Argola', ['B', 'L'], 'DO', 129.90],
            ['Colar Corrente', ['B'], 'RH', 249.90],
            ['Pulseira Tennis', ['RP'], 'RH', 349.90],
            ['Pingente Coração', ['B', 'L'], 'DO', 99.90],
            ['Conjunto Riviera', ['B'], 'RH', 459.90],
            ['Anel Pavê', ['RP'], 'RH', 279.90],
            ['Brinco Pedra', ['RP'], 'DO', 159.90],
        ];

        // [mês atrás, qtd de peças, cenário de triagem]
        $scenarios = [
            [6, 2, 'completed'],
            [5, 1, 'delivered'],
            [4, 3, 'completed'],
            [3, 2, 'cancelled'],
            [2, 2, 'in_service'],
            [1, 3, 'awaiting_customer'],
            [1, 1, 'credit_pending'],
            [0, 2, 'pending'],
            [0, 2, 'inspection'],
            [0, 1, 'awaiting_return'],
        ];

        foreach ($scenarios as $index => [$monthsAgo, $pieceCount, $stage]) {
            $client = $clients[$index % count($clients)];
            $date = now()->subMonths($monthsAgo)->subDays($index % 12);

            $order = ServiceOrder::create([
                'client_name' => $client[0],
                'client_document' => '12.' . (300 + $index) . '.456/0001-0' . $index,
                'client_phone' => '(11) 9' . (8000 + $index * 7) . '-' . (1000 + $index * 13),
                'consultant_name' => $client[2],
                'client_code' => 'CL-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'origin' => $client[1],
                'order_date' => $date->copy()->startOfDay(),
                'observations' => $index % 3 === 0 ? 'Peça recebida com embalagem original.' : null,
                'status' => ServiceOrder::STATUS_CREATED,
            ]);

            $order->forceFill(['created_at' => $date, 'updated_at' => $date])->saveQuietly();

            for ($piece = 0; $piece < $pieceCount; $piece++) {
                $data = $pieces[($index + $piece) % count($pieces)];

                $item = $order->items()->create([
                    'identified_product_name' => $data[0],
                    'services' => $data[1],
                    'coating' => $data[2],
                    'purchase_date' => $date->copy()->subMonths(8)->startOfDay(),
                    'piece_value' => $data[3],
                    'ai_identified_code' => 'AA' . (20260000 + $index * 10 + $piece) . 'BR',
                    'observations' => $piece === 0 ? 'Cliente relata escurecimento na região do fecho.' : null,
                ]);

                $this->applyStage($item, $stage, $index + $piece);
            }

            // Ordens ainda sem ficha permanecem em triagem (status "created").
            if ($order->items()->whereHas('technicalSheet')->exists()) {
                $order->forceFill(['status' => $this->orderStatusFor($order)])->saveQuietly();
            }
        }
    }

    private function applyStage(ServiceOrderItem $item, string $stage, int $seed): void
    {
        $paid = $seed % 3 === 0;

        // O tratamento define quais ações o workflow permite em seguida.
        $treatment = match ($stage) {
            'credit_pending' => 'credit',
            'awaiting_return' => 'bath',
            'inspection' => 'bath',
            default => ['bath', 'assembly', 'gluing', 'weld_bath'][$seed % 4],
        };

        $isBath = in_array($treatment, ['bath', 'weld_bath'], true);
        $guarantee = $stage === 'credit_pending' ? 1 : ($paid ? 0 : 1);

        if ($stage === 'pending') {
            return;
        }

        if ($stage === 'cancelled') {
            $this->act($item, 'triage', [
                'is_guarantee' => 0, 'treatment' => 'assembly',
                'diagnosis' => 'Peça fora do prazo de garantia. Orçamento enviado à cliente.',
                'repair_price' => 180.00, 'changes_coating' => 0, 'changes_color' => 0,
                'payment_method' => 'Cartão de crédito',
            ]);
            $this->act($item, 'decline', [], 'Cliente recusou o orçamento por telefone.');

            return;
        }

        $this->act($item, 'triage', [
            'is_guarantee' => $guarantee,
            'treatment' => $treatment,
            'diagnosis' => $guarantee
                ? 'Falha de fabricação identificada no fecho. Coberto pela garantia.'
                : 'Desgaste natural do banho. Necessário novo banho e polimento.',
            'repair_price' => $guarantee ? null : 120.00,
            'changes_coating' => $stage === 'inspection' ? 1 : 0,
            'new_finish' => $stage === 'inspection' ? 'polished' : null,
            'changes_color' => 0,
            'payment_method' => $guarantee ? null : 'PIX',
        ]);

        $serviceSteps = $isBath
            ? ['start_service', 'send_out', 'receive']
            : ['start_service', 'finish_service'];

        // Serviço pago só avança depois da aprovação do cliente.
        $approval = $guarantee ? [] : ['approve'];

        $steps = match ($stage) {
            'in_service' => ['start_service'],
            'awaiting_return' => ['start_service', 'send_out'],
            'inspection' => $serviceSteps,
            'completed' => [...$serviceSteps, 'pass'],
            'delivered' => [...$serviceSteps, 'pass', 'send_store', 'deliver'],
            default => [],
        };

        foreach ($approval as $action) {
            $this->act($item, $action, [], 'Orçamento aprovado pela cliente por WhatsApp.');
        }

        foreach ($steps as $action) {
            $this->act($item, $action);
        }
    }

    private function act(ServiceOrderItem $item, string $action, array $extra = [], ?string $notes = null): void
    {
        $sheet = $item->technicalSheet()->first();

        $this->workflow->update(
            $item,
            $extra + [
                'action' => $action,
                'version' => $sheet?->version ?? 0,
                'notes' => $notes ?? 'Registro de demonstração.',
            ],
            $this->adminId
        );
    }

    private function orderStatusFor(ServiceOrder $order): string
    {
        $items = $order->items()->with('technicalSheet')->get();

        $allClosed = $items->isNotEmpty() && $items->every(
            fn ($item) => $item->technicalSheet
                && in_array($item->technicalSheet->status, \App\Models\TechnicalSheet::CLOSED_STATUSES, true)
        );

        return $allClosed ? ServiceOrder::STATUS_COMPLETED : ServiceOrder::STATUS_UNDER_ANALYSIS;
    }
}
