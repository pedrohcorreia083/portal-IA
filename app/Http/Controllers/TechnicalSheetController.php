<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrderItem;
use App\Models\TechnicalSheet;
use App\Services\TechnicalSheetWorkflow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TechnicalSheetController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceOrderItem::with(['serviceOrder', 'product', 'technicalSheet']);
        foreach (['order_number', 'client_name', 'origin'] as $field) {
            if ($request->filled($field)) {
                $query->whereHas('serviceOrder', fn ($q) => $q->where($field, 'like', '%'.$request->string($field).'%'));
            }
        }
        if ($request->filled('piece')) {
            $term = '%'.$request->string('piece').'%';
            $query->where(fn ($q) => $q->where('identified_product_name', 'like', $term)
                ->orWhere('ai_identified_code', 'like', $term)
                ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $term)->orWhere('code', 'like', $term)));
        }
        if ($request->filled('status')) {
            $status = $request->string('status')->toString();
            $query->where(function ($q) use ($status) {
                $q->whereHas('technicalSheet', fn ($s) => $s->where('status', $status));
                if ($status === 'pending') {
                    $q->orWhereDoesntHave('technicalSheet');
                }
            });
        }
        $items = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $statuses = TechnicalSheet::STATUSES;

        return view('technical-sheets.index', compact('items', 'statuses'));
    }

    public function show(ServiceOrderItem $item)
    {
        $item->load(['serviceOrder', 'product', 'technicalSheet.events.user']);
        // Opening a sheet is read-only; a persisted sheet is created on the first action.
        $sheet = $item->technicalSheet ?? new TechnicalSheet([
            'status' => 'pending', 'version' => 0,
            'is_guarantee' => $item->is_guarantee,
            'repair_price' => $item->repair_price,
            'diagnosis' => $item->technical_observations,
        ]);

        return view('technical-sheets.show', compact('item', 'sheet'));
    }

    public function update(Request $request, ServiceOrderItem $item, TechnicalSheetWorkflow $workflow)
    {
        abort_unless($request->user()->isTechnical(), 403);
        $rules = [
            'action' => ['required', 'string', Rule::in([
                'triage', 'start_analysis', 'approve', 'decline', 'exchange',
                'start_service', 'send_out', 'receive', 'finish_service', 'pass',
                'rework', 'confirm_credit', 'complete_return', 'send_store', 'deliver', 'revise_triage',
            ])],
            'version' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
        if ($request->input('action') === 'triage') {
            $rules += [
                'is_guarantee' => ['required', 'boolean'],
                'treatment' => ['required', Rule::in(array_keys(TechnicalSheet::TREATMENTS))],
                'diagnosis' => ['required', 'string', 'max:10000'],
                'repair_price' => ['nullable', 'required_if:is_guarantee,0', 'numeric', 'min:0', 'max:99999999.99'],
                'changes_coating' => ['required', 'boolean'],
                'new_finish' => ['nullable', 'required_if:changes_coating,1', Rule::in(array_keys(TechnicalSheet::FINISH_TYPES))],
                'changes_color' => ['required', 'boolean'],
                'new_color' => ['nullable', 'required_if:changes_color,1', Rule::in(array_keys(TechnicalSheet::COLORS))],
                'payment_method' => ['nullable', 'string', 'max:100'],
            ];
        }
        $workflow->update($item, $request->validate($rules), $request->user()->id);

        return redirect()->route('technical-sheets.show', $item)->with('success', 'Ficha técnica atualizada.');
    }
}
