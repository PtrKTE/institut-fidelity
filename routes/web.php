<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\RendezvousController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\OperationBancaireController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\Api\ClienteApiController;
use App\Http\Controllers\Api\FactureApiController;
use App\Http\Controllers\Api\StatsApiController;

// Auth routes
Route::get('/', fn() => redirect()->route('login'));
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth', 'multi_session', 'session_timeout', 'anti_hijack', 'audit'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // ─── CLIENTES ──────────────────────────────────────
        Route::middleware('role:superadmin,admin,agent,comm')->group(function () {
            Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
            Route::get('/clientes/create', [ClienteController::class, 'create'])->name('clientes.create');
            Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
            Route::get('/clientes/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');
        });
        Route::middleware('role:superadmin,admin')->group(function () {
            Route::get('/clientes/{cliente}/edit', [ClienteController::class, 'edit'])->name('clientes.edit');
            Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
            Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
        });

        // ─── FACTURES / CAISSE ─────────────────────────────
        Route::middleware('role:superadmin,admin,agent,compta')->group(function () {
            Route::get('/caisse', [FactureController::class, 'create'])->name('caisse');
            Route::get('/factures', [FactureController::class, 'index'])->name('factures.index');
            Route::post('/factures', [FactureController::class, 'store'])->name('factures.store');
            Route::get('/factures/{facture}', [FactureController::class, 'show'])->name('factures.show');
            Route::delete('/factures/{facture}', [FactureController::class, 'destroy'])->name('factures.destroy');
        });

        // ─── RENDEZ-VOUS ───────────────────────────────────
        Route::middleware('role:superadmin,admin,agent,comm')->group(function () {
            Route::get('/rendezvous', [RendezvousController::class, 'index'])->name('rendezvous.index');
            Route::get('/rendezvous/create', [RendezvousController::class, 'create'])->name('rendezvous.create');
            Route::post('/rendezvous', [RendezvousController::class, 'store'])->name('rendezvous.store');
            Route::put('/rendezvous/{rendezvou}', [RendezvousController::class, 'update'])->name('rendezvous.update');
            Route::delete('/rendezvous/{rendezvou}', [RendezvousController::class, 'destroy'])->name('rendezvous.destroy');
        });

        // ─── DEPENSES ──────────────────────────────────────
        Route::middleware('role:superadmin,admin,agent')->group(function () {
            Route::get('/depenses', [DepenseController::class, 'index'])->name('depenses.index');
            Route::post('/depenses', [DepenseController::class, 'store'])->name('depenses.store');
            Route::delete('/depenses/{depense}', [DepenseController::class, 'destroy'])->name('depenses.destroy');
        });

        // ─── OPERATIONS BANCAIRES ──────────────────────────
        Route::middleware('role:superadmin,admin,agent')->group(function () {
            Route::get('/operations-bancaires', [OperationBancaireController::class, 'index'])->name('operations-bancaires.index');
            Route::post('/operations-bancaires', [OperationBancaireController::class, 'store'])->name('operations-bancaires.store');
        });

        // ─── UTILISATEURS (superadmin only) ────────────────
        Route::middleware('role:superadmin')->group(function () {
            Route::get('/utilisateurs', [UserController::class, 'index'])->name('utilisateurs.index');
            Route::get('/utilisateurs/create', [UserController::class, 'create'])->name('utilisateurs.create');
            Route::post('/utilisateurs', [UserController::class, 'store'])->name('utilisateurs.store');
            Route::get('/utilisateurs/{utilisateur}/edit', [UserController::class, 'edit'])->name('utilisateurs.edit');
            Route::put('/utilisateurs/{utilisateur}', [UserController::class, 'update'])->name('utilisateurs.update');
            Route::delete('/utilisateurs/{utilisateur}', [UserController::class, 'destroy'])->name('utilisateurs.destroy');
        });

        // ─── COMMUNICATIONS (superadmin + comm) ────────────
        Route::middleware('role:superadmin,comm')->group(function () {
            Route::get('/communications', [CommunicationController::class, 'index'])->name('communications.index');
            Route::post('/communications/send', [CommunicationController::class, 'send'])->name('communications.send');
        });

        // ─── API INTERNES (AJAX) ───────────────────────────
        // Clientes API
        Route::prefix('api/clientes')->group(function () {
            Route::post('/recherche', [ClienteApiController::class, 'recherche'])->name('api.clientes.recherche');
            Route::get('/recherche-rdv', [ClienteApiController::class, 'rechercheRdv'])->name('api.clientes.recherche-rdv');
            Route::get('/agent', [ClienteApiController::class, 'listAgent'])->name('api.clientes.agent');
            Route::get('/generer-identifiants', [ClienteApiController::class, 'genererIdentifiants'])->name('api.clientes.generer-identifiants');

            Route::middleware('role:superadmin,admin')->group(function () {
                Route::post('/modifier-rapide', [ClienteApiController::class, 'modifierRapide']);
                Route::post('/maj-complete', [ClienteApiController::class, 'majComplete']);
                Route::post('/supprimer', [ClienteApiController::class, 'supprimer']);
                Route::post('/supprimer-multiples', [ClienteApiController::class, 'supprimerMultiples']);
                Route::post('/maj-taux', [ClienteApiController::class, 'majTaux']);
                Route::post('/maj-taux-multiple', [ClienteApiController::class, 'majTauxMultiple']);
            });

            Route::middleware('role:superadmin')->group(function () {
                Route::post('/details', [ClienteApiController::class, 'details']);
            });
        });

        // Factures API
        Route::prefix('api/factures')->group(function () {
            Route::get('/list', [FactureApiController::class, 'list'])->name('api.factures.list');
            Route::get('/prestations', [FactureApiController::class, 'prestations'])->name('api.factures.prestations');
            Route::get('/{facture}', [FactureApiController::class, 'view'])->name('api.factures.view');
        });

        // Stats API
        Route::prefix('api/stats')->group(function () {
            Route::get('/main', [StatsApiController::class, 'main'])->name('api.stats.main');
            Route::get('/ca-jour', [StatsApiController::class, 'caJour'])->name('api.stats.ca-jour');
            Route::get('/ca-lieu', [StatsApiController::class, 'caLieu'])->name('api.stats.ca-lieu');
            Route::get('/compta', [StatsApiController::class, 'compta'])->name('api.stats.compta');
        });
    });
