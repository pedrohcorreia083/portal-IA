<?php

namespace App\Services;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\TechnicalSheet;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TechnicalSheetWorkflow
{
    public function update(ServiceOrderItem $item, array $data, int $userId): TechnicalSheet
    {
        return DB::transaction(function () use ($item, $data, $userId) {
            // Lock the parent first so different pieces cannot close the same OS concurrently.
            $order = ServiceOrder::whereKey($item->service_order_id)->lockForUpdate()->firstOrFail();
            $item = $order->items()->whereKey($item->id)->lockForUpdate()->firstOrFail();
            $this->require($order->status !== 'cancelled', 'Esta OS está cancelada.');
            $sheet = $item->technicalSheet()->firstOrCreate([], [
                'is_guarantee' => $item->is_guarantee,
                'repair_price' => $item->repair_price,
                'diagnosis' => $item->technical_observations,
            ]);
            $this->require((int) $data['version'] === $sheet->version, 'A ficha foi atualizada por outro usuário. Recarregue a página.');
            $from = $sheet->status;
            $action = $data['action'];

            if ($action === 'triage') {
                $this->require(in_array($from, ['pending', 'in_analysis'], true), 'A triagem já foi concluída.');
                $this->require($data['is_guarantee'] || $data['treatment'] !== 'credit', 'Crédito está disponível apenas para garantia.');
                $sheet->fill(collect($data)->only([
                    'is_guarantee', 'treatment', 'diagnosis', 'repair_price',
                    'changes_coating', 'alteration_type', 'payment_method', 'delivery_destination',
                ])->all());
                if ($sheet->is_guarantee) {
                    $sheet->repair_price = null;
                    $sheet->payment_method = null;
                }
                if (!$sheet->changes_coating) {
                    $sheet->alteration_type = null;
                }
                $sheet->status = $sheet->is_guarantee
                    ? ($sheet->treatment === 'credit' ? 'credit_pending' : 'ready_for_service')
                    : 'awaiting_customer';
            } else {
                $this->require(array_key_exists($action, $sheet->availableActions()), 'Esta ação não está disponível na etapa atual.');
                if (in_array($action, ['approve', 'decline', 'rework', 'exchange', 'confirm_credit', 'deliver', 'send_store', 'revise_triage'], true)) {
                    $this->require(!empty(trim($data['notes'] ?? '')), 'Registre a confirmação, referência ou motivo desta ação.');
                }
                if ($action === 'send_store') {
                    $this->require($sheet->delivery_destination === 'store', 'O destino desta ficha é o cliente.');
                }
                $sheet->status = match ($action) {
                    'start_analysis', 'revise_triage' => 'in_analysis',
                    'approve', 'rework' => 'ready_for_service',
                    'decline' => 'cancelled',
                    'start_service' => 'in_service',
                    'send_out' => 'awaiting_return',
                    'receive', 'finish_service', 'exchange' => 'inspection',
                    'pass', 'confirm_credit' => 'completed',
                    'send_store' => 'sent_to_store',
                    'deliver' => 'delivered',
                };
            }

            $sheet->version++;
            $sheet->save();
            $sheet->events()->create([
                'user_id' => $userId,
                'action' => $action,
                'from_status' => $from,
                'to_status' => $sheet->status,
                'notes' => $data['notes'] ?? null,
                'details' => $action === 'triage' ? $sheet->only([
                    'diagnosis', 'treatment', 'is_guarantee', 'repair_price',
                    'changes_coating', 'alteration_type', 'payment_method', 'delivery_destination',
                ]) : null,
            ]);

            // Keep legacy PDFs and exports consistent while the sheet owns the workflow.
            $item->update([
                'is_guarantee' => $sheet->is_guarantee,
                'repair_price' => $sheet->repair_price,
                'technical_observations' => $sheet->diagnosis,
                'technical_status' => match (true) {
                    $sheet->status === 'cancelled' => 'SC',
                    !in_array($sheet->status, TechnicalSheet::CLOSED_STATUSES, true) => null,
                    $sheet->treatment === 'credit' => 'GCG',
                    $sheet->treatment === 'exchange' && $sheet->is_guarantee => 'GTC',
                    default => 'RP',
                },
            ]);

            $items = $order->items()->with('technicalSheet')->get();
            $allClosed = $items->isNotEmpty() && $items->every(fn ($entry) => $entry->technicalSheet
                && in_array($entry->technicalSheet->status, TechnicalSheet::CLOSED_STATUSES, true));
            $order->update(['status' => $allClosed ? ServiceOrder::STATUS_COMPLETED : ServiceOrder::STATUS_UNDER_ANALYSIS]);

            return $sheet;
        });
    }

    private function require(bool $condition, string $message): void
    {
        if (!$condition) {
            throw ValidationException::withMessages(['action' => $message]);
        }
    }
}
