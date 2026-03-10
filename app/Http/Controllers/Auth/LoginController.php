<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifiant' => 'required|string',
            'mot_de_passe' => 'required|string',
        ]);

        $identifiant = $request->input('identifiant');
        $motDePasse = $request->input('mot_de_passe');

        // Recherche par username OU email OU nom (comme l'app actuelle)
        $user = User::where('username', $identifiant)
            ->orWhere('email', $identifiant)
            ->orWhere('nom', $identifiant)
            ->first();

        if (!$user || !password_verify($motDePasse, $user->mot_de_passe)) {
            app(AuditService::class)->log(
                userId: null,
                action: 'LOGIN_FAILED',
                details: "Tentative échouée pour: {$identifiant}",
                request: $request,
                success: false
            );

            return back()
                ->withInput($request->only('identifiant'))
                ->with('error', 'Identifiants incorrects.');
        }

        // Exclure le role cliente du back-office
        if ($user->role === 'cliente') {
            return back()->with('error', 'Accès non autorisé.');
        }

        // Generer un session token unique (anti-multisession)
        $sessionToken = Str::random(64);
        $user->session_token = $sessionToken;
        $user->save();

        // Enregistrer la session utilisateur
        UserSession::updateOrCreate(
            ['user_id' => $user->id],
            ['session_id' => session()->getId(), 'last_activity' => now()]
        );

        // Connexion Laravel
        Auth::login($user);

        // Stocker les infos de securite en session
        session([
            'session_token' => $sessionToken,
            'last_activity' => time(),
            'auth_ip' => $request->ip(),
            'auth_user_agent' => $request->userAgent(),
        ]);

        $request->session()->regenerate();

        app(AuditService::class)->log(
            userId: $user->id,
            action: 'LOGIN',
            details: 'Connexion réussie',
            request: $request
        );

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();

        app(AuditService::class)->log(
            userId: $userId,
            action: 'LOGOUT',
            details: 'Déconnexion manuelle',
            request: $request
        );

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
