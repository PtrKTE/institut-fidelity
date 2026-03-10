<?php

namespace App\Http\Controllers;

use App\Models\OperationBancaire;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OperationBancaireController extends Controller
{
    public function __construct(protected AuditService $audit) {}

    public function index(Request $request)
    {
        $query = OperationBancaire::with('agent');

        if (Auth::user()->role === 'agent') {
            $query->where('agent_id', Auth::id());
        }

        if ($request->filled('type')) {
            $query->where('type_operation', $request->type);
        }

        if ($request->filled('banque')) {
            $query->where('banque', $request->banque);
        }

        if ($request->filled('date_start')) {
            $query->where('date_operation', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->where('date_operation', '<=', $request->date_end);
        }

        $operations = $query->orderByDesc('date_enregistrement')->paginate(30);

        return view('operations-bancaires.index', compact('operations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.type_operation' => 'required|string|in:versement_especes,remise_cheque',
            'items.*.banque' => 'required|string|in:NSIA,BICICI,SIB',
            'items.*.nom_operateur' => 'nullable|string|max:100',
            'items.*.montant_operation' => 'required|numeric|min:0.01',
            'items.*.date_operation' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $batchId = bin2hex(random_bytes(8));
            $count = 0;

            foreach ($validated['items'] as $item) {
                OperationBancaire::create([
                    'agent_id' => Auth::id(),
                    'batch_id' => $batchId,
                    'type_operation' => $item['type_operation'],
                    'banque' => $item['banque'],
                    'nom_operateur' => $item['nom_operateur'] ?? 'Henri Joel',
                    'montant_operation' => $item['montant_operation'],
                    'date_operation' => $item['date_operation'],
                ]);
                $count++;
            }

            DB::commit();

            $this->audit->log(
                userId: Auth::id(),
                action: 'ADD_OPERATION_BANCAIRE',
                entityType: 'operation_bancaire',
                entityId: 0,
                details: "Batch {$batchId}: {$count} operation(s)",
                request: $request
            );

            return response()->json([
                'status' => 'success',
                'batch_id' => $batchId,
                'inserted' => $count,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return response()->json(['status' => 'error', 'message' => 'Erreur'], 500);
        }
    }
}
