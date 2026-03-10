<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(protected AuditService $audit) {}

    public function index()
    {
        $users = User::where('role', '!=', 'superadmin')
            ->orderBy('nom')
            ->get();

        return view('utilisateurs.index', compact('users'));
    }

    public function create()
    {
        return view('utilisateurs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'username' => 'required|string|max:50|unique:users,username',
            'mot_de_passe' => 'required|string|min:6',
            'role' => 'required|in:admin,agent,compta,comm',
            'lieu_affecte' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'mot_de_passe' => Hash::make($validated['mot_de_passe']),
            'role' => $validated['role'],
            'lieu_affecte' => $validated['lieu_affecte'],
        ]);

        $this->audit->log(
            userId: Auth::id(),
            action: 'ADD_USER',
            entityType: 'user',
            entityId: $user->id,
            details: "Nouvel utilisateur: {$user->prenom} {$user->nom} ({$user->role})",
            request: $request
        );

        return redirect()->route('utilisateurs.index')->with('success', "Utilisateur {$user->prenom} {$user->nom} cree.");
    }

    public function edit(User $utilisateur)
    {
        return view('utilisateurs.edit', compact('utilisateur'));
    }

    public function update(Request $request, User $utilisateur)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => "required|email|max:150|unique:users,email,{$utilisateur->id}",
            'username' => "required|string|max:50|unique:users,username,{$utilisateur->id}",
            'role' => 'required|in:admin,agent,compta,comm',
            'lieu_affecte' => 'nullable|string|max:100',
            'mot_de_passe' => 'nullable|string|min:6',
        ]);

        $before = $utilisateur->toArray();

        $data = collect($validated)->except('mot_de_passe')->toArray();
        if (!empty($validated['mot_de_passe'])) {
            $data['mot_de_passe'] = Hash::make($validated['mot_de_passe']);
        }

        $utilisateur->update($data);

        $this->audit->log(
            userId: Auth::id(),
            action: 'EDIT_USER',
            entityType: 'user',
            entityId: $utilisateur->id,
            details: "MAJ utilisateur: {$utilisateur->prenom} {$utilisateur->nom}",
            request: $request,
            beforeState: $before,
            afterState: $utilisateur->fresh()->toArray()
        );

        return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur mis a jour.');
    }

    public function destroy(Request $request, User $utilisateur)
    {
        if ($utilisateur->role === 'superadmin') {
            return back()->with('error', 'Impossible de supprimer un superadmin.');
        }

        $nom = "{$utilisateur->prenom} {$utilisateur->nom}";

        $this->audit->log(
            userId: Auth::id(),
            action: 'DELETE_USER',
            entityType: 'user',
            entityId: $utilisateur->id,
            details: "Suppression utilisateur: {$nom}",
            request: $request
        );

        $utilisateur->delete();

        return redirect()->route('utilisateurs.index')->with('success', "Utilisateur {$nom} supprime.");
    }
}
