<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsApiController extends Controller
{
    /**
     * Stats principales (remplace ajax/stats_data.php)
     */
    public function main(Request $request): JsonResponse
    {
        $dateStart = $request->input('date_start', now()->startOfMonth()->format('Y-m-d'));
        $dateEnd = $request->input('date_end', now()->format('Y-m-d'));
        $jour = $request->input('jour', now()->format('Y-m-d'));

        // Stats periode
        $period = DB::table('factures')
            ->whereBetween('date_facture', [$dateStart . ' 00:00:00', $dateEnd . ' 23:59:59'])
            ->selectRaw('COUNT(*) as nb_factures, COALESCE(SUM(montant_total), 0) as ca_total, COALESCE(AVG(montant_total), 0) as ticket_moyen')
            ->first();

        // Stats du jour
        $daily = DB::table('factures')
            ->whereDate('date_facture', $jour)
            ->selectRaw('COUNT(*) as nb_factures, COALESCE(SUM(montant_total), 0) as ca_total, COALESCE(AVG(montant_total), 0) as ticket_moyen')
            ->first();

        // Meilleure operatrice (periode)
        $topOp = DB::table('facture_prestations')
            ->join('factures', 'factures.id', '=', 'facture_prestations.facture_id')
            ->join('operatrices', 'operatrices.id', '=', 'facture_prestations.operatrice_id')
            ->whereBetween('factures.date_facture', [$dateStart . ' 00:00:00', $dateEnd . ' 23:59:59'])
            ->select('operatrices.nom', 'operatrices.fonction', DB::raw('SUM(facture_prestations.quantite) as total_prestations'))
            ->groupBy('operatrices.nom', 'operatrices.fonction', 'facture_prestations.operatrice_id')
            ->orderByDesc('total_prestations')
            ->first();

        // Nombre clientes actives
        $nbClientes = DB::table('clientes')->where('active', 1)->count();

        // RDV en attente
        $rdvEnAttente = DB::table('rendezvous')
            ->where('status', 'en_attente')
            ->where('date_rdv', '>=', now()->format('Y-m-d'))
            ->count();

        return response()->json([
            'periode' => [
                'nb_factures' => $period->nb_factures,
                'ca_total' => round($period->ca_total),
                'ticket_moyen' => round($period->ticket_moyen),
            ],
            'jour' => [
                'nb_factures' => $daily->nb_factures,
                'ca_total' => round($daily->ca_total),
                'ticket_moyen' => round($daily->ticket_moyen),
            ],
            'meilleure_operatrice' => $topOp ? $topOp->nom . ' (' . $topOp->fonction . ')' : '—',
            'nb_clientes' => $nbClientes,
            'rdv_en_attente' => $rdvEnAttente,
        ]);
    }

    /**
     * CA par jour (remplace ajax/ca_jour.php)
     */
    public function caJour(): JsonResponse
    {
        $data = DB::table('factures')
            ->selectRaw('DATE(date_facture) as jour, SUM(montant_total) as ca')
            ->groupBy('jour')
            ->orderBy('jour')
            ->get();

        return response()->json([
            'labels' => $data->pluck('jour'),
            'data' => $data->pluck('ca'),
        ]);
    }

    /**
     * CA par lieu (remplace ajax/ca_lieu.php)
     */
    public function caLieu(Request $request): JsonResponse
    {
        $jour = $request->input('jour', now()->format('Y-m-d'));

        $data = DB::table('factures')
            ->select('lieu_prestation')
            ->selectRaw("SUM(CASE WHEN DATE(date_facture) = ? THEN montant_total ELSE 0 END) as ca_jour", [$jour])
            ->selectRaw("SUM(montant_total) as ca_total")
            ->groupBy('lieu_prestation')
            ->orderByDesc('ca_total')
            ->get();

        return response()->json($data);
    }

    /**
     * Stats comptabilite (remplace ajax/compta_stats.php)
     */
    public function compta(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $month = date('m', strtotime($date));
        $year = date('Y', strtotime($date));

        $lieux = ['Pronails Vallon', 'Pronails Riviera', 'Pronails Zone 4'];
        $result = [];

        foreach ($lieux as $lieu) {
            $key = str_replace(['Pronails ', ' '], ['', '_'], strtolower($lieu));

            // CA jour
            $caJour = DB::table('factures')
                ->whereDate('date_facture', $date)
                ->where('lieu_prestation', $lieu)
                ->sum('montant_net');

            // CA mois
            $caMois = DB::table('factures')
                ->whereMonth('date_facture', $month)
                ->whereYear('date_facture', $year)
                ->where('lieu_prestation', $lieu)
                ->sum('montant_net');

            // Modes paiement
            $modes = DB::table('factures')
                ->whereDate('date_facture', $date)
                ->where('lieu_prestation', $lieu)
                ->select('mode_paiement', DB::raw('SUM(montant_net) as total'))
                ->groupBy('mode_paiement')
                ->pluck('total', 'mode_paiement');

            $result[$key] = [
                'ca_jour' => round($caJour),
                'ca_mois' => round($caMois),
                'modes_paiement' => $modes,
            ];
        }

        // Totaux globaux
        $totaux = DB::table('factures')
            ->whereDate('date_facture', $date)
            ->selectRaw('COALESCE(SUM(montant_net), 0) as ca_total, COUNT(*) as nb_factures, COALESCE(AVG(montant_net), 0) as ticket_moyen')
            ->first();

        // Coffre (especes - depenses)
        $especes = DB::table('factures')
            ->whereDate('date_facture', $date)
            ->where('mode_paiement', 'Espèces')
            ->sum('montant_net');

        $depenses = DB::table('depenses')
            ->whereDate('date_depense', $date)
            ->sum(DB::raw('quantite * montant'));

        $result['totaux'] = [
            'ca_total' => round($totaux->ca_total),
            'nb_factures' => $totaux->nb_factures,
            'ticket_moyen' => round($totaux->ticket_moyen),
            'coffre' => round($especes - $depenses),
        ];

        return response()->json($result);
    }
}
