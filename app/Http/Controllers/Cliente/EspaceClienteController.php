<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Facture;
use App\Models\FacturePrestation;
use App\Models\Rendezvous;
use App\Models\Prestation;
use App\Services\OtpService;
use App\Services\BarcodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EspaceClienteController extends Controller
{
    private function cliente()
    {
        return Cliente::findOrFail(session('cliente_id'));
    }

    // ─── Home ────────────────────────────────────────────────
    public function home()
    {
        $cliente = $this->cliente();
        $prochainRdv = Rendezvous::where('cliente_id', $cliente->id)
            ->where('date_rdv', '>=', now()->toDateString())
            ->where('statut', '!=', 'annule')
            ->where('statut', '!=', 'rejete')
            ->orderBy('date_rdv')->orderBy('heure_rdv')
            ->first();

        return view('cliente.home', compact('cliente', 'prochainRdv'));
    }

    // ─── Carte fidélité ──────────────────────────────────────
    public function carte()
    {
        $cliente = $this->cliente();
        return view('cliente.carte', compact('cliente'));
    }

    // ─── Profil ──────────────────────────────────────────────
    public function profil()
    {
        $cliente = $this->cliente();
        return view('cliente.profil', compact('cliente'));
    }

    public function updateEmail(Request $request, OtpService $otpService)
    {
        $request->validate([
            'new_email' => 'required|email|max:150',
            'otp' => 'required|string|size:6',
        ]);

        $cliente = $this->cliente();

        if (!$otpService->verify($cliente->email, $request->otp, 'activation')) {
            return response()->json(['success' => false, 'message' => 'Code OTP invalide ou expire.']);
        }

        if (Cliente::where('email', $request->new_email)->where('id', '!=', $cliente->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Cet email est deja utilise.']);
        }

        $cliente->email = $request->new_email;
        $cliente->save();

        return response()->json(['success' => true]);
    }

    public function updatePhone(Request $request, OtpService $otpService)
    {
        $request->validate([
            'new_telephone' => 'required|string|max:30',
            'otp' => 'required|string|size:6',
        ]);

        $cliente = $this->cliente();

        if (!$otpService->verify($cliente->email, $request->otp, 'activation')) {
            return response()->json(['success' => false, 'message' => 'Code OTP invalide ou expire.']);
        }

        $cliente->telephone = $request->new_telephone;
        $cliente->save();

        return response()->json(['success' => true]);
    }

    // ─── Rendez-vous ─────────────────────────────────────────
    public function rendezvous()
    {
        $cliente = $this->cliente();
        $rdvs = Rendezvous::where('cliente_id', $cliente->id)
            ->orderByDesc('date_rdv')
            ->orderByDesc('heure_rdv')
            ->paginate(20);
        $prestations = Prestation::orderBy('libelle')->get();
        $lieux = ['Pronails Vallon', 'Pronails Riviera', 'Pronails Zone 4', 'Domicile'];

        return view('cliente.rendezvous', compact('cliente', 'rdvs', 'prestations', 'lieux'));
    }

    public function storeRendezvous(Request $request)
    {
        $request->validate([
            'date_rdv' => 'required|date|after_or_equal:today',
            'heure_rdv' => 'required',
            'lieu' => 'required|string',
            'prestation' => 'required|string',
        ]);

        $cliente = $this->cliente();

        // Check slot availability
        $existing = Rendezvous::where('date_rdv', $request->date_rdv)
            ->where('heure_rdv', $request->heure_rdv)
            ->where('lieu', $request->lieu)
            ->whereNotIn('statut', ['annule', 'rejete'])
            ->exists();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Ce creneau est deja pris. Choisissez un autre horaire.']);
        }

        $rdv = new Rendezvous();
        $rdv->cliente_id = $cliente->id;
        $rdv->date_rdv = $request->date_rdv;
        $rdv->heure_rdv = $request->heure_rdv;
        $rdv->lieu = $request->lieu;
        $rdv->prestation = $request->prestation;
        $rdv->notes = $request->notes ?? '';
        $rdv->statut = 'en_attente';
        $rdv->save();

        return response()->json(['success' => true]);
    }

    public function cancelRendezvous(Request $request, $id)
    {
        $cliente = $this->cliente();
        $rdv = Rendezvous::where('id', $id)->where('cliente_id', $cliente->id)->firstOrFail();

        if (!in_array($rdv->statut, ['en_attente', 'valide'])) {
            return response()->json(['success' => false, 'message' => 'Ce RDV ne peut pas etre annule.']);
        }

        $rdv->statut = 'annule';
        $rdv->save();

        return response()->json(['success' => true]);
    }

    // ─── Historique ──────────────────────────────────────────
    public function historique()
    {
        $cliente = $this->cliente();
        $factures = Facture::where('cliente_id', $cliente->id)
            ->orderByDesc('date_facture')
            ->paginate(20);

        return view('cliente.historique', compact('cliente', 'factures'));
    }

    // ─── API endpoints (JSON) ────────────────────────────────
    public function apiStats()
    {
        $clienteId = session('cliente_id');
        $cliente = Cliente::findOrFail($clienteId);

        $nbVisites = Facture::where('cliente_id', $clienteId)->count();
        $totalDepense = Facture::where('cliente_id', $clienteId)->sum('montant_total');
        $rdvEnAttente = Rendezvous::where('cliente_id', $clienteId)
            ->where('statut', 'en_attente')
            ->count();

        return response()->json([
            'nb_visites' => $nbVisites,
            'total_depense' => round($totalDepense),
            'taux_reduction' => $cliente->taux_reduction,
            'rdv_en_attente' => $rdvEnAttente,
            'membre_depuis' => $cliente->cliente_depuis ?? date('Y', strtotime($cliente->date_enregistrement)),
        ]);
    }

    public function apiHistorique(Request $request)
    {
        $clienteId = session('cliente_id');

        $factures = Facture::where('cliente_id', $clienteId)
            ->orderByDesc('date_facture')
            ->paginate(15);

        $items = $factures->map(function ($f) {
            $prestations = FacturePrestation::where('facture_id', $f->id)
                ->pluck('libelle_prestation')
                ->implode(', ');
            return [
                'id' => $f->id,
                'date' => $f->date_facture,
                'prestations' => $prestations ?: 'Prestation',
                'montant' => round($f->montant_total),
                'mode_paiement' => $f->mode_paiement,
            ];
        });

        return response()->json([
            'data' => $items,
            'has_more' => $factures->hasMorePages(),
            'current_page' => $factures->currentPage(),
        ]);
    }

    public function apiDepensesMensuelles()
    {
        $clienteId = session('cliente_id');

        $data = Facture::where('cliente_id', $clienteId)
            ->select(
                DB::raw("DATE_FORMAT(date_facture, '%Y-%m') as mois"),
                DB::raw('SUM(montant_total) as total')
            )
            ->groupBy('mois')
            ->orderBy('mois')
            ->limit(12)
            ->get();

        return response()->json([
            'labels' => $data->pluck('mois'),
            'data' => $data->pluck('total')->map(fn($v) => round($v)),
        ]);
    }

    public function apiTopPrestations()
    {
        $clienteId = session('cliente_id');

        $top = FacturePrestation::join('factures', 'factures.id', '=', 'facture_prestations.facture_id')
            ->where('factures.cliente_id', $clienteId)
            ->select('facture_prestations.libelle_prestation', DB::raw('COUNT(*) as nb'), DB::raw('SUM(facture_prestations.tarif) as total'))
            ->groupBy('facture_prestations.libelle_prestation')
            ->orderByDesc('nb')
            ->limit(5)
            ->get();

        return response()->json($top);
    }
}
