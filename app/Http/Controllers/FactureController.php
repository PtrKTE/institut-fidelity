<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\FacturePrestation;
use App\Models\ModePaiement;
use App\Models\Operatrice;
use App\Models\Prestation;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FactureController extends Controller
{
    public function __construct(protected AuditService $audit) {}

    /**
     * Historique des factures (liste)
     */
    public function index()
    {
        $operatrices = Operatrice::orderBy('nom')->get();
        $modesPaiement = ModePaiement::orderBy('nom')->get();

        return view('factures.index', compact('operatrices', 'modesPaiement'));
    }

    /**
     * Caisse (POS) — creation de facture
     */
    public function create()
    {
        $prestations = Prestation::orderBy('libelle')->get();
        $operatrices = Operatrice::orderBy('nom')->get();
        $modesPaiement = ModePaiement::orderBy('nom')->get();

        return view('factures.create', compact('prestations', 'operatrices', 'modesPaiement'));
    }

    /**
     * Enregistrer une facture (remplace ajax/enregistrer_facture.php)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'anonyme' => 'nullable|boolean',
            'client_id' => 'nullable|integer|exists:clientes,id',
            'nom_client' => 'required|string|max:100',
            'telephone_client' => 'nullable|string|max:30',
            'taux_remise' => 'required|numeric|min:0|max:100',
            'mode_paiement' => 'required|string|max:30',
            'lieu_prestation' => 'required|string|max:50',
            'montant_total' => 'required|numeric|min:0',
            'montant_remise' => 'required|numeric|min:0',
            'montant_net' => 'required|numeric|min:0',
            'prestations' => 'required|array|min:1',
            'prestations.*.id' => 'required|integer|exists:prestations,id',
            'prestations.*.tarif' => 'required|numeric|min:0',
            'prestations.*.quantite' => 'required|integer|min:1',
            'prestations.*.montant' => 'required|numeric|min:0',
            'prestations.*.operatrice_id' => 'nullable|integer|exists:operatrices,id',
        ]);

        try {
            DB::beginTransaction();

            $facture = Facture::create([
                'client_id' => $validated['anonyme'] ? null : $validated['client_id'],
                'nom_client' => $validated['nom_client'],
                'telephone_client' => $validated['telephone_client'] ?? '',
                'date_facture' => now(),
                'montant_total' => $validated['montant_total'],
                'taux_remise' => $validated['taux_remise'],
                'montant_remise' => $validated['montant_remise'],
                'montant_net' => $validated['montant_net'],
                'mode_paiement' => $validated['mode_paiement'],
                'lieu_prestation' => $validated['lieu_prestation'],
                'caissiere_id' => Auth::id(),
            ]);

            foreach ($validated['prestations'] as $line) {
                // Recuperer le libelle depuis la DB (securite)
                $presta = Prestation::find($line['id']);

                FacturePrestation::create([
                    'facture_id' => $facture->id,
                    'prestation_id' => $line['id'],
                    'libelle' => $presta->libelle,
                    'tarif' => $line['tarif'],
                    'quantite' => $line['quantite'],
                    'montant' => $line['montant'],
                    'operatrice_id' => $line['operatrice_id'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'invoice_id' => $facture->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return response()->json(['status' => 'error', 'message' => 'Erreur lors de l\'enregistrement'], 500);
        }
    }

    /**
     * Detail d'une facture
     */
    public function show(Facture $facture)
    {
        $facture->load(['cliente', 'caissiere', 'prestations.prestation', 'prestations.operatrice']);

        return view('factures.show', compact('facture'));
    }

    /**
     * Supprimer une facture
     */
    public function destroy(Request $request, Facture $facture)
    {
        $this->audit->log(
            userId: Auth::id(),
            action: 'DELETE_FACTURE',
            entityType: 'facture',
            entityId: $facture->id,
            details: "Suppression facture #{$facture->id} ({$facture->nom_client}, {$facture->montant_net} F)",
            request: $request
        );

        $facture->prestations()->delete();
        $facture->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Facture supprimee']);
        }

        return redirect()->route('factures.index')->with('success', 'Facture supprimee.');
    }
}
