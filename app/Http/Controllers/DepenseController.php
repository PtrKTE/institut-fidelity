<?php

namespace App\Http\Controllers;

use App\Models\Depense;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepenseController extends Controller
{
    public function __construct(protected AuditService $audit) {}

    public function index(Request $request)
    {
        $query = Depense::with('agent');

        // Agents ne voient que leurs propres depenses
        if (Auth::user()->role === 'agent') {
            $query->where('agent_id', Auth::id());
        }

        if ($request->filled('date_start')) {
            $query->where('date_depense', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->where('date_depense', '<=', $request->date_end);
        }

        $depenses = $query->orderByDesc('date_depense')->paginate(30);

        return view('depenses.index', compact('depenses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.libelle' => 'required|string|max:255',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantite' => 'required|integer|min:1',
            'items.*.montant' => 'required|numeric|min:0.01',
        ]);

        try {
            // Utiliser la connexion mysql_latin1
            $conn = DB::connection('mysql_latin1');
            $conn->beginTransaction();

            $count = 0;
            foreach ($validated['items'] as $item) {
                Depense::create([
                    'agent_id' => Auth::id(),
                    'libelle' => $item['libelle'],
                    'description' => $item['description'] ?? '',
                    'quantite' => $item['quantite'],
                    'montant' => $item['montant'],
                ]);
                $count++;
            }

            $conn->commit();

            $this->audit->log(
                userId: Auth::id(),
                action: 'ADD_DEPENSE',
                entityType: 'depense',
                entityId: 0,
                details: "{$count} depense(s) enregistree(s)",
                request: $request
            );

            return response()->json(['status' => 'success', 'message' => "{$count} depense(s) enregistree(s)"]);
        } catch (\Exception $e) {
            DB::connection('mysql_latin1')->rollBack();
            report($e);
            return response()->json(['status' => 'error', 'message' => 'Erreur'], 500);
        }
    }

    public function destroy(Request $request, Depense $depense)
    {
        $this->audit->log(
            userId: Auth::id(),
            action: 'DELETE_DEPENSE',
            entityType: 'depense',
            entityId: $depense->id,
            details: "Suppression depense: {$depense->libelle}",
            request: $request
        );

        $depense->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Depense supprimee']);
        }

        return redirect()->route('depenses.index')->with('success', 'Depense supprimee.');
    }
}
