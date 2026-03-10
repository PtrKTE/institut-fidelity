<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\AuditService;
use App\Services\BarcodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteApiController extends Controller
{
    public function __construct(
        protected AuditService $audit,
        protected BarcodeService $barcode
    ) {}

    /**
     * Recherche cliente par telephone, carte ou code-barres (remplace ajax/recherche_cliente.php)
     */
    public function recherche(Request $request): JsonResponse
    {
        $query = trim($request->input('query', ''));

        if (empty($query)) {
            return response()->json(['status' => 'error', 'message' => 'Recherche vide']);
        }

        $cliente = Cliente::where('telephone', 'LIKE', "%{$query}%")
            ->orWhere('numero_carte', 'LIKE', "%{$query}%")
            ->orWhere('code_barres', 'LIKE', "%{$query}%")
            ->first(['id', 'nom', 'prenom', 'telephone', 'taux_reduction', 'numero_carte', 'code_barres']);

        if (!$cliente) {
            return response()->json(['status' => 'error', 'message' => 'Aucune cliente trouvee']);
        }

        return response()->json(['status' => 'success', 'cliente' => $cliente]);
    }

    /**
     * Recherche autocomplete pour RDV (remplace ajax/recherche_clientes_rdv.php)
     */
    public function rechercheRdv(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $clientes = Cliente::where('nom', 'LIKE', "%{$q}%")
            ->orWhere('prenom', 'LIKE', "%{$q}%")
            ->orWhere('telephone', 'LIKE', "%{$q}%")
            ->limit(10)
            ->get(['id', 'nom', 'prenom', 'telephone', 'email']);

        return response()->json($clientes);
    }

    /**
     * Liste clientes pour agent (remplace ajax/get_clientes_agent.php)
     */
    public function listAgent(): JsonResponse
    {
        $clientes = Cliente::where('lieu_enregistrement', Auth::user()->lieu_affecte)
            ->where('active', true)
            ->orderBy('nom')
            ->get(['id', 'nom', 'prenom', 'telephone', 'date_enregistrement']);

        return response()->json($clientes);
    }

    /**
     * Modifier rapide nom/prenom/tel (remplace ajax/modifier_cliente.php)
     */
    public function modifierRapide(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:clientes,id',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'telephone' => 'nullable|string|max:30',
        ]);

        $cliente = Cliente::findOrFail($validated['id']);
        $before = $cliente->toArray();

        $cliente->update([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'telephone' => $validated['telephone'] ?? $cliente->telephone,
        ]);

        $this->audit->log(
            userId: Auth::id(),
            action: 'EDIT_CLIENTE',
            entityType: 'cliente',
            entityId: $cliente->id,
            details: "Edition rapide: {$cliente->nom} {$cliente->prenom}",
            request: $request,
            beforeState: $before,
            afterState: $cliente->fresh()->toArray()
        );

        return response()->json(['status' => 'success', 'message' => 'Cliente modifiee']);
    }

    /**
     * Mise a jour complete (remplace ajax/maj_cliente.php)
     */
    public function majComplete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:clientes,id',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'telephone' => 'nullable|string|max:30',
            'lieu_enregistrement' => 'nullable|string|max:100',
            'numero_carte' => 'nullable|string|max:50',
            'code_barres' => 'nullable|string|max:50',
        ]);

        $cliente = Cliente::findOrFail($validated['id']);
        $before = $cliente->toArray();

        $cliente->update(collect($validated)->except('id')->toArray());

        $this->audit->log(
            userId: Auth::id(),
            action: 'MAJ_CLIENTE',
            entityType: 'cliente',
            entityId: $cliente->id,
            details: "MAJ complete: {$cliente->nom} {$cliente->prenom}",
            request: $request,
            beforeState: $before,
            afterState: $cliente->fresh()->toArray()
        );

        return response()->json(['status' => 'success', 'message' => 'Cliente mise a jour']);
    }

    /**
     * Supprimer une cliente (remplace ajax/supprimer_cliente.php)
     */
    public function supprimer(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|integer|exists:clientes,id']);

        $cliente = Cliente::findOrFail($request->id);
        $nom = "{$cliente->prenom} {$cliente->nom}";

        $this->audit->log(
            userId: Auth::id(),
            action: 'DELETE_CLIENTE',
            entityType: 'cliente',
            entityId: $cliente->id,
            details: "Suppression: {$nom}",
            request: $request
        );

        $cliente->delete();

        return response()->json(['status' => 'success', 'message' => "Cliente {$nom} supprimee"]);
    }

    /**
     * Supprimer plusieurs clientes (remplace ajax/supprimer_multiples.php)
     */
    public function supprimerMultiples(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:clientes,id',
        ]);

        $count = Cliente::whereIn('id', $validated['ids'])->delete();

        $this->audit->log(
            userId: Auth::id(),
            action: 'DELETE_MULTI_CLIENTE',
            entityType: 'cliente',
            entityId: 0,
            details: "Suppression multiple: {$count} clientes (IDs: " . implode(',', $validated['ids']) . ")",
            request: $request
        );

        return response()->json(['status' => 'success', 'message' => "{$count} clientes supprimees"]);
    }

    /**
     * Mettre a jour le taux de reduction (remplace ajax/maj_taux.php)
     */
    public function majTaux(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:clientes,id',
            'taux' => 'required|numeric|min:0|max:100',
        ]);

        $cliente = Cliente::findOrFail($validated['id']);
        $cliente->update(['taux_reduction' => $validated['taux']]);

        $this->audit->log(
            userId: Auth::id(),
            action: 'TAUX_UPDATE',
            entityType: 'cliente',
            entityId: $cliente->id,
            details: "Taux mis a jour: {$validated['taux']}%",
            request: $request
        );

        return response()->json(['status' => 'success', 'message' => "Taux mis a jour ({$validated['taux']}%)"]);
    }

    /**
     * Mettre a jour le taux en masse (remplace ajax/maj_taux_multiple.php)
     */
    public function majTauxMultiple(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:clientes,id',
            'taux' => 'required|numeric|min:0|max:100',
        ]);

        $count = Cliente::whereIn('id', $validated['ids'])
            ->update(['taux_reduction' => $validated['taux']]);

        $this->audit->log(
            userId: Auth::id(),
            action: 'TAUX_BULK',
            entityType: 'cliente',
            entityId: 0,
            details: "Taux bulk: {$validated['taux']}% applique a {$count} clientes",
            request: $request
        );

        return response()->json(['status' => 'success', 'message' => "Taux {$validated['taux']}% applique a {$count} clientes"]);
    }

    /**
     * Generer identifiants carte (remplace ajax/generer_identifiants.php)
     */
    public function genererIdentifiants(): JsonResponse
    {
        return response()->json([
            'numero' => $this->barcode->generateCardNumber(),
            'code' => $this->barcode->generateBarcode(),
        ]);
    }

    /**
     * Details analytics cliente (remplace ajax/get_cliente_details.php)
     */
    public function details(Request $request): JsonResponse
    {
        $search = trim($request->input('search', ''));

        if (strlen($search) < 3) {
            return response()->json(['status' => 'error', 'message' => 'Minimum 3 caracteres']);
        }

        $cliente = Cliente::where('nom', 'LIKE', "%{$search}%")
            ->orWhere('prenom', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->orWhere('telephone', 'LIKE', "%{$search}%")
            ->first();

        if (!$cliente) {
            return response()->json(['status' => 'error', 'message' => 'Aucune cliente trouvee']);
        }

        // Top prestations
        $topPrestations = \DB::table('facture_prestations')
            ->join('factures', 'factures.id', '=', 'facture_prestations.facture_id')
            ->join('prestations', 'prestations.id', '=', 'facture_prestations.prestation_id')
            ->where('factures.client_id', $cliente->id)
            ->select('prestations.libelle', \DB::raw('COUNT(*) as nb'), \DB::raw('SUM(facture_prestations.montant) as total'))
            ->groupBy('prestations.libelle', 'facture_prestations.prestation_id')
            ->orderByDesc('nb')
            ->limit(3)
            ->get();

        // Top lieux
        $topLieux = \DB::table('factures')
            ->where('client_id', $cliente->id)
            ->select('lieu_prestation', \DB::raw('COUNT(*) as nb'))
            ->groupBy('lieu_prestation')
            ->orderByDesc('nb')
            ->limit(3)
            ->get();

        // Top operatrices
        $topOperatrices = \DB::table('facture_prestations')
            ->join('factures', 'factures.id', '=', 'facture_prestations.facture_id')
            ->join('operatrices', 'operatrices.id', '=', 'facture_prestations.operatrice_id')
            ->where('factures.client_id', $cliente->id)
            ->select('operatrices.nom', \DB::raw('COUNT(*) as nb'))
            ->groupBy('operatrices.nom', 'facture_prestations.operatrice_id')
            ->orderByDesc('nb')
            ->limit(3)
            ->get();

        // Mode paiement favori
        $modePaiement = \DB::table('factures')
            ->where('client_id', $cliente->id)
            ->select('mode_paiement', \DB::raw('COUNT(*) as nb'))
            ->groupBy('mode_paiement')
            ->orderByDesc('nb')
            ->first();

        // Stats economiques
        $econStats = \DB::table('factures')
            ->where('client_id', $cliente->id)
            ->selectRaw('SUM(montant_net) as total, AVG(montant_net) as moyenne, MAX(montant_net) as max_facture, MIN(montant_net) as min_facture, COUNT(*) as nb_factures')
            ->first();

        // Mois en cours
        $moisEnCours = \DB::table('factures')
            ->where('client_id', $cliente->id)
            ->whereMonth('date_facture', now()->month)
            ->whereYear('date_facture', now()->year)
            ->sum('montant_net');

        // Derniers RDV
        $rdvs = \DB::table('rendezvous')
            ->where('client_id', $cliente->id)
            ->orderByDesc('date_rdv')
            ->orderByDesc('heure_rdv')
            ->limit(20)
            ->get();

        return response()->json([
            'status' => 'success',
            'cliente' => $cliente,
            'topPrestations' => $topPrestations,
            'topLieux' => $topLieux,
            'topOperatrices' => $topOperatrices,
            'modePaiement' => $modePaiement,
            'econStats' => $econStats,
            'moisEnCours' => $moisEnCours,
            'rdvs' => $rdvs,
        ]);
    }
}
