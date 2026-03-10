<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Models\Prestation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FactureApiController extends Controller
{
    /**
     * Liste des factures filtree (remplace ajax/factures_list.php)
     */
    public function list(Request $request): JsonResponse
    {
        $query = Facture::with(['caissiere', 'prestations.operatrice'])
            ->select('factures.*');

        if ($request->filled('caissiere')) {
            $query->where('caissiere_id', $request->caissiere);
        }

        if ($request->filled('mode')) {
            $query->where('mode_paiement', $request->mode);
        }

        if ($request->filled('lieu')) {
            $query->where('lieu_prestation', $request->lieu);
        }

        if ($request->filled('date_start')) {
            $query->where('date_facture', '>=', $request->date_start . ' 00:00:00');
        }

        if ($request->filled('date_end')) {
            $query->where('date_facture', '<=', $request->date_end . ' 23:59:59');
        }

        if ($request->filled('operatrice')) {
            $query->whereHas('prestations', function ($q) use ($request) {
                $q->where('operatrice_id', $request->operatrice);
            });
        }

        $factures = $query->orderByDesc('date_facture')->get();

        $data = $factures->map(function ($f) {
            $prestas = $f->prestations->pluck('libelle')->unique()->implode(', ');
            $ops = $f->prestations->map(fn($p) => $p->operatrice?->nom)->filter()->unique()->implode(', ');

            return [
                'id' => $f->id,
                'date_heure' => $f->date_facture ? date('d/m/Y H:i', strtotime($f->date_facture)) : '—',
                'client' => $f->nom_client ?: '—',
                'prestations' => $prestas ?: '—',
                'montant_total' => number_format($f->montant_total, 0, ',', ' ') . ' F',
                'remise' => $f->taux_remise . '%',
                'montant_net' => number_format($f->montant_net, 0, ',', ' ') . ' F',
                'mode_paiement' => $f->mode_paiement ?: '—',
                'lieu' => $f->lieu_prestation ?: '—',
                'caissiere' => $f->caissiere ? $f->caissiere->prenom . ' ' . $f->caissiere->nom : '—',
                'operatrice' => $ops ?: '—',
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Detail d'une facture (remplace ajax/facture_view.php)
     */
    public function view(Facture $facture): JsonResponse
    {
        $facture->load(['cliente', 'caissiere', 'prestations.operatrice']);

        return response()->json([
            'status' => 'success',
            'facture' => $facture,
            'prestations' => $facture->prestations,
        ]);
    }

    /**
     * Liste des prestations (remplace ajax/get_prestations.php)
     */
    public function prestations(): JsonResponse
    {
        $prestations = Prestation::orderBy('libelle')->get(['id', 'libelle', 'tarif']);

        return response()->json(['status' => 'success', 'data' => $prestations]);
    }
}
