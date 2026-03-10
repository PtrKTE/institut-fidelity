<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Services\AuditService;
use App\Services\BarcodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    public function __construct(
        protected AuditService $audit,
        protected BarcodeService $barcode
    ) {}

    public function index(Request $request)
    {
        $query = Cliente::with('enregistrePar');

        // Filtre par lieu pour les agents
        if (Auth::user()->role === 'agent') {
            $query->where('lieu_enregistrement', Auth::user()->lieu_affecte)
                  ->where('active', true);
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('active', $request->statut === 'actif');
        }

        // Filtre par lieu
        if ($request->filled('lieu')) {
            $query->where('lieu_enregistrement', $request->lieu);
        }

        // Recherche
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nom', 'LIKE', "%{$s}%")
                  ->orWhere('prenom', 'LIKE', "%{$s}%")
                  ->orWhere('telephone', 'LIKE', "%{$s}%")
                  ->orWhere('email', 'LIKE', "%{$s}%")
                  ->orWhere('numero_carte', 'LIKE', "%{$s}%");
            });
        }

        $clientes = $query->orderByDesc('id')->paginate(25);

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'telephone' => 'required|string|max:30',
            'date_anniversaire' => 'nullable|date',
            'lieu_enregistrement' => 'required|string|max:100',
        ]);

        // Verification doublons
        $duplicate = Cliente::where(function ($q) use ($validated) {
            $q->where(function ($q2) use ($validated) {
                $q2->where('nom', $validated['nom'])->where('prenom', $validated['prenom']);
            });
            if (!empty($validated['email'])) {
                $q->orWhere('email', $validated['email']);
            }
            $q->orWhere('telephone', $validated['telephone']);
        })->first();

        if ($duplicate) {
            return back()->withInput()->with('error', 'Une cliente avec ces informations existe deja (doublon detecte).');
        }

        $cliente = Cliente::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'] ?? null,
            'telephone' => $validated['telephone'],
            'date_anniversaire' => $validated['date_anniversaire'] ?? null,
            'cliente_depuis' => date('Y'),
            'lieu_enregistrement' => $validated['lieu_enregistrement'],
            'numero_carte' => $this->barcode->generateCardNumber(),
            'code_barres' => $this->barcode->generateBarcode(),
            'date_enregistrement' => now(),
            'taux_reduction' => 0,
            'enregistre_par' => Auth::id(),
            'active' => 1,
        ]);

        $this->audit->log(
            userId: Auth::id(),
            action: 'ADD_CLIENTE',
            entityType: 'cliente',
            entityId: $cliente->id,
            details: "Nouvelle cliente: {$cliente->nom} {$cliente->prenom}",
            request: $request
        );

        return redirect()->route('clientes.index')->with('success', "Cliente {$cliente->prenom} {$cliente->nom} ajoutee avec succes.");
    }

    public function show(Cliente $cliente)
    {
        $cliente->load('enregistrePar');

        // Stats factures
        $stats = [
            'total_factures' => $cliente->factures()->count(),
            'total_depense' => $cliente->factures()->sum('montant_net'),
            'moyenne_facture' => $cliente->factures()->avg('montant_net') ?? 0,
            'derniere_visite' => $cliente->factures()->max('date_facture'),
        ];

        // Top prestations
        $topPrestations = \DB::table('facture_prestations')
            ->join('factures', 'factures.id', '=', 'facture_prestations.facture_id')
            ->join('prestations', 'prestations.id', '=', 'facture_prestations.prestation_id')
            ->where('factures.client_id', $cliente->id)
            ->select('prestations.libelle', \DB::raw('COUNT(*) as nb'), \DB::raw('SUM(facture_prestations.montant) as total'))
            ->groupBy('prestations.libelle', 'facture_prestations.prestation_id')
            ->orderByDesc('nb')
            ->limit(5)
            ->get();

        // Derniers RDV
        $rendezvous = $cliente->rendezvous()->orderByDesc('date_rdv')->limit(10)->get();

        return view('clientes.show', compact('cliente', 'stats', 'topPrestations', 'rendezvous'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'telephone' => 'required|string|max:30',
            'date_anniversaire' => 'nullable|date',
            'lieu_enregistrement' => 'required|string|max:100',
            'numero_carte' => 'nullable|string|max:50',
            'code_barres' => 'nullable|string|max:50',
        ]);

        $before = $cliente->toArray();

        $cliente->update($validated);

        $this->audit->log(
            userId: Auth::id(),
            action: 'MAJ_CLIENTE',
            entityType: 'cliente',
            entityId: $cliente->id,
            details: "Mise a jour cliente: {$cliente->nom} {$cliente->prenom}",
            request: $request,
            beforeState: $before,
            afterState: $cliente->fresh()->toArray()
        );

        return redirect()->route('clientes.show', $cliente)->with('success', 'Cliente mise a jour avec succes.');
    }

    public function destroy(Request $request, Cliente $cliente)
    {
        $nom = "{$cliente->prenom} {$cliente->nom}";

        $this->audit->log(
            userId: Auth::id(),
            action: 'DELETE_CLIENTE',
            entityType: 'cliente',
            entityId: $cliente->id,
            details: "Suppression cliente: {$nom}",
            request: $request
        );

        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', "Cliente {$nom} supprimee.");
    }
}
