<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\OtpCode;
use App\Services\OtpService;
use App\Services\BarcodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ClienteAuthController extends Controller
{
    // ─── Login ───────────────────────────────────────────────
    public function showLogin()
    {
        if (session('cliente_id')) {
            return redirect()->route('espace-cliente.home');
        }
        return view('cliente.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $cliente = Cliente::where('email', $request->email)
            ->where('active', 1)
            ->first();

        if (!$cliente || !password_verify($request->password, $cliente->mot_de_passe)) {
            return back()->withErrors(['email' => 'Email ou mot de passe incorrect.'])->withInput();
        }

        session([
            'cliente_id' => $cliente->id,
            'cliente_nom' => $cliente->nom,
            'cliente_prenom' => $cliente->prenom,
        ]);

        return redirect()->route('espace-cliente.home');
    }

    // ─── Vérification email (point d'entrée) ────────────────
    public function showVerifEmail()
    {
        return view('cliente.auth.verif-email');
    }

    public function verifEmail(Request $request, OtpService $otpService)
    {
        $request->validate(['email' => 'required|email']);

        $cliente = Cliente::where('email', $request->email)->first();

        if (!$cliente) {
            return response()->json(['status' => 'absent']);
        }

        if ($cliente->active) {
            return response()->json(['status' => 'active']);
        }

        // Inactive → send OTP for activation
        $otpService->generate($request->email, 'activation');
        $otpService->sendByEmail($request->email, 'activation');

        return response()->json(['status' => 'inactive']);
    }

    // ─── Inscription ─────────────────────────────────────────
    public function showRegister(Request $request)
    {
        return view('cliente.auth.register', ['email' => $request->query('email', '')]);
    }

    public function register(Request $request, OtpService $otpService, BarcodeService $barcodeService)
    {
        $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'indicatif' => 'required|string',
            'telephone' => 'required|string|max:30',
            'lieu_enregistrement' => 'required|string',
        ]);

        // Check duplicate
        if (Cliente::where('email', $request->email)->exists()) {
            return response()->json(['success' => false, 'message' => 'Cet email est deja utilise.']);
        }

        $tel = $request->indicatif . ' ' . preg_replace('/[^0-9]/', '', $request->telephone);

        $cliente = new Cliente();
        $cliente->nom = mb_strtoupper($request->nom);
        $cliente->prenom = ucfirst(mb_strtolower($request->prenom));
        $cliente->email = mb_strtolower($request->email);
        $cliente->telephone = $tel;
        $cliente->lieu_enregistrement = $request->lieu_enregistrement;
        $cliente->date_anniversaire = $request->date_anniversaire;
        $cliente->cliente_depuis = $request->cliente_depuis ?? date('Y');
        $cliente->numero_carte = $barcodeService->generateCardNumber();
        $cliente->code_barres = $barcodeService->generateBarcode();
        $cliente->date_enregistrement = now();
        $cliente->enregistre_par = 0; // auto-inscription
        $cliente->active = 0;
        $cliente->taux_reduction = 0;
        $cliente->save();

        // Send activation OTP
        $otpService->generate($request->email, 'activation');
        $otpService->sendByEmail($request->email, 'activation');

        return response()->json(['success' => true]);
    }

    // ─── Activation OTP ──────────────────────────────────────
    public function showActivation(Request $request)
    {
        return view('cliente.auth.activation', ['email' => $request->query('email', '')]);
    }

    public function activate(Request $request, OtpService $otpService)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!$otpService->verify($request->email, $request->otp, 'activation')) {
            return back()->withErrors(['otp' => 'Code OTP invalide ou expire.'])->withInput();
        }

        Cliente::where('email', $request->email)->update([
            'mot_de_passe' => Hash::make($request->password),
            'active' => 1,
        ]);

        return redirect()->route('espace-cliente.login')
            ->with('success', 'Compte active ! Vous pouvez vous connecter.');
    }

    // ─── Renvoi OTP (AJAX) ──────────────────────────────────
    public function resendOtp(Request $request, OtpService $otpService)
    {
        $request->validate([
            'email' => 'required|email',
            'context' => 'required|in:activation,reset',
        ]);

        $otpService->generate($request->email, $request->context);
        $otpService->sendByEmail($request->email, $request->context);

        return response()->json(['success' => true]);
    }

    // ─── Reset password ──────────────────────────────────────
    public function showResetRequest()
    {
        return view('cliente.auth.reset-request');
    }

    public function sendResetOtp(Request $request, OtpService $otpService)
    {
        $request->validate(['email' => 'required|email']);

        $cliente = Cliente::where('email', $request->email)->where('active', 1)->first();
        if (!$cliente) {
            return response()->json(['success' => false, 'message' => 'Aucun compte actif avec cet email.']);
        }

        $otpService->generate($request->email, 'reset');
        $otpService->sendByEmail($request->email, 'reset');

        return response()->json(['success' => true]);
    }

    public function showResetConfirm(Request $request)
    {
        return view('cliente.auth.reset-confirm', ['email' => $request->query('email', '')]);
    }

    public function resetPassword(Request $request, OtpService $otpService)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!$otpService->verify($request->email, $request->otp, 'reset')) {
            return back()->withErrors(['otp' => 'Code OTP invalide ou expire.'])->withInput();
        }

        Cliente::where('email', $request->email)->update([
            'mot_de_passe' => Hash::make($request->password),
        ]);

        return redirect()->route('espace-cliente.login')
            ->with('success', 'Mot de passe modifie ! Connectez-vous.');
    }

    // ─── Logout ──────────────────────────────────────────────
    public function logout()
    {
        session()->forget(['cliente_id', 'cliente_nom', 'cliente_prenom']);
        return redirect()->route('espace-cliente.login')
            ->with('success', 'A bientot !');
    }
}
