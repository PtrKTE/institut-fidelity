<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Prestation;
use App\Models\Rendezvous;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RendezvousController extends Controller
{
    public function __construct(protected AuditService $audit) {}

    public function index(Request $request)
    {
        $query = Rendezvous::with(['cliente', 'agent']);

        if (Auth::user()->role === 'agent') {
            $query->where('agent_id', Auth::id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('date_rdv', $request->date);
        }

        if ($request->filled('lieu')) {
            $query->where('lieu', $request->lieu);
        }

        $rendezvous = $query->orderByDesc('date_rdv')->orderByDesc('heure_rdv')->paginate(25);
        $prestations = Prestation::orderBy('libelle')->get();

        return view('rendezvous.index', compact('rendezvous', 'prestations'));
    }

    public function create()
    {
        $prestations = Prestation::orderBy('libelle')->get();
        return view('rendezvous.create', compact('prestations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|integer|exists:clientes,id',
            'nom_client' => 'required|string|max:100',
            'telephone' => 'required|string|max:30',
            'email' => 'nullable|email|max:150',
            'date_rdv' => 'required|date|after_or_equal:today',
            'heure_rdv' => 'required|string|max:10',
            'lieu' => 'required|string|max:100',
            'prestation' => 'required|string|max:255',
            'commentaire' => 'nullable|string|max:500',
        ]);

        // Verification disponibilite creneau
        $conflict = Rendezvous::where('date_rdv', $validated['date_rdv'])
            ->where('heure_rdv', $validated['heure_rdv'])
            ->where('lieu', $validated['lieu'])
            ->whereNotIn('status', ['annule', 'rejete'])
            ->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'Ce creneau est deja pris pour ce lieu.');
        }

        $rdv = Rendezvous::create(array_merge($validated, [
            'agent_id' => Auth::id(),
            'status' => 'en_attente',
        ]));

        $this->audit->log(
            userId: Auth::id(),
            action: 'ADD_RDV',
            entityType: 'rendezvous',
            entityId: $rdv->id,
            details: "RDV: {$validated['nom_client']} le {$validated['date_rdv']} a {$validated['heure_rdv']}",
            request: $request
        );

        return redirect()->route('rendezvous.index')->with('success', 'Rendez-vous enregistre.');
    }

    public function update(Request $request, Rendezvous $rendezvou)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:valide,rejete,annule,termine',
            'date_rdv' => 'sometimes|date',
            'heure_rdv' => 'sometimes|string|max:10',
            'lieu' => 'sometimes|string|max:100',
            'commentaire' => 'nullable|string|max:500',
        ]);

        $before = $rendezvou->toArray();
        $rendezvou->update($validated);

        $this->audit->log(
            userId: Auth::id(),
            action: 'EDIT_RDV',
            entityType: 'rendezvous',
            entityId: $rendezvou->id,
            details: "MAJ RDV #{$rendezvou->id}: " . json_encode($validated),
            request: $request,
            beforeState: $before,
            afterState: $rendezvou->fresh()->toArray()
        );

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Rendez-vous mis a jour']);
        }

        return redirect()->route('rendezvous.index')->with('success', 'Rendez-vous mis a jour.');
    }

    public function destroy(Request $request, Rendezvous $rendezvou)
    {
        $this->audit->log(
            userId: Auth::id(),
            action: 'DELETE_RDV',
            entityType: 'rendezvous',
            entityId: $rendezvou->id,
            details: "Suppression RDV #{$rendezvou->id}",
            request: $request
        );

        $rendezvou->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Rendez-vous supprime']);
        }

        return redirect()->route('rendezvous.index')->with('success', 'Rendez-vous supprime.');
    }
}
