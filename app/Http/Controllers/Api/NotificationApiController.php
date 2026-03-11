<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rendezvous;
use App\Models\Cliente;
use App\Models\Facture;
use Illuminate\Support\Carbon;

class NotificationApiController extends Controller
{
    public function index()
    {
        $items = collect();

        // RDV en attente (recent)
        $rdvs = Rendezvous::where('status', 'en_attente')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        foreach ($rdvs as $rdv) {
            $items->push([
                'type' => 'rdv',
                'message' => "Nouveau RDV de {$rdv->nom_client} — {$rdv->prestation} le {$rdv->date_rdv}",
                'time_ago' => Carbon::parse($rdv->created_at)->diffForHumans(),
                'read' => false,
                'created_at' => $rdv->created_at,
            ]);
        }

        // Nouvelles clientes (7 derniers jours)
        $newClientes = Cliente::where('date_enregistrement', '>=', now()->subDays(7))
            ->orderByDesc('date_enregistrement')
            ->limit(5)
            ->get();

        foreach ($newClientes as $c) {
            $items->push([
                'type' => 'cliente',
                'message' => "Nouvelle cliente : {$c->prenom} {$c->nom} ({$c->lieu_enregistrement})",
                'time_ago' => Carbon::parse($c->date_enregistrement)->diffForHumans(),
                'read' => true,
                'created_at' => $c->date_enregistrement,
            ]);
        }

        // Factures du jour
        $todayCount = Facture::whereDate('date_facture', today())->count();
        $todayTotal = Facture::whereDate('date_facture', today())->sum('montant_total');
        if ($todayCount > 0) {
            $items->push([
                'type' => 'facture',
                'message' => "{$todayCount} facture(s) aujourd'hui — " . number_format($todayTotal, 0, ',', ' ') . " F",
                'time_ago' => "Aujourd'hui",
                'read' => true,
                'created_at' => now(),
            ]);
        }

        // Sort by date desc
        $sorted = $items->sortByDesc('created_at')->values()->take(20);

        return response()->json([
            'count' => $rdvs->count(), // Only unread = pending RDV
            'items' => $sorted,
        ]);
    }

    public function count()
    {
        $count = Rendezvous::where('status', 'en_attente')->count();

        return response()->json(['count' => $count]);
    }
}
