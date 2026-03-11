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
use App\Http\Controllers\Cliente\ClienteAuthController;
use App\Http\Controllers\Cliente\EspaceClienteController;

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
        Route::middleware('role:superadmin,admin,agent,compta,comm')->group(function () {
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
        Route::middleware('role:superadmin,admin,agent,compta,comm')->group(function () {
            Route::get('/rendezvous', [RendezvousController::class, 'index'])->name('rendezvous.index');
            Route::get('/rendezvous/create', [RendezvousController::class, 'create'])->name('rendezvous.create');
            Route::post('/rendezvous', [RendezvousController::class, 'store'])->name('rendezvous.store');
            Route::put('/rendezvous/{rendezvou}', [RendezvousController::class, 'update'])->name('rendezvous.update');
            Route::delete('/rendezvous/{rendezvou}', [RendezvousController::class, 'destroy'])->name('rendezvous.destroy');
        });

        // ─── DEPENSES ──────────────────────────────────────
        Route::middleware('role:superadmin,admin,agent,compta')->group(function () {
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

        // Notifications API
        Route::get('/api/notifications', [\App\Http\Controllers\Api\NotificationApiController::class, 'index']);
        Route::get('/api/notifications/count', [\App\Http\Controllers\Api\NotificationApiController::class, 'count']);

        // Exports
        Route::prefix('exports')->group(function () {
            Route::get('/clientes/excel', [\App\Http\Controllers\ExportController::class, 'clientesExcel'])->name('exports.clientes.excel');
            Route::get('/clientes/pdf', [\App\Http\Controllers\ExportController::class, 'clientesPdf'])->name('exports.clientes.pdf');
            Route::get('/factures/excel', [\App\Http\Controllers\ExportController::class, 'facturesExcel'])->name('exports.factures.excel');
            Route::get('/factures/pdf', [\App\Http\Controllers\ExportController::class, 'facturesPdf'])->name('exports.factures.pdf');
            Route::get('/facture/{facture}/pdf', [\App\Http\Controllers\ExportController::class, 'facturePdf'])->name('exports.facture.pdf');
            Route::get('/depenses/excel', [\App\Http\Controllers\ExportController::class, 'depensesExcel'])->name('exports.depenses.excel');
            Route::get('/operations/excel', [\App\Http\Controllers\ExportController::class, 'operationsExcel'])->name('exports.operations.excel');
        });
    });

// ─── Barcode Generator (public) ──────────────────────────────
Route::get('/barcode/{code}', function (string $code) {
    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    $png = $generator->getBarcode($code, $generator::TYPE_CODE_128, 2, 40);
    return response($png)->header('Content-Type', 'image/png')->header('Cache-Control', 'public, max-age=86400');
})->where('code', '[A-Za-z0-9]+')->name('barcode');

// ═══════════════════════════════════════════════════════════════
// ESPACE CLIENTE (PWA)
// ═══════════════════════════════════════════════════════════════
Route::prefix('espace-cliente')->group(function () {

    // Offline page
    Route::get('/offline', fn() => view('cliente.offline'))->name('espace-cliente.offline');

    // Auth cliente (public)
    Route::get('/login', [ClienteAuthController::class, 'showLogin'])->name('espace-cliente.login');
    Route::post('/login', [ClienteAuthController::class, 'login']);
    Route::get('/verif-email', [ClienteAuthController::class, 'showVerifEmail'])->name('espace-cliente.verif-email');
    Route::post('/verif-email', [ClienteAuthController::class, 'verifEmail']);
    Route::get('/inscription', [ClienteAuthController::class, 'showRegister'])->name('espace-cliente.register');
    Route::post('/inscription', [ClienteAuthController::class, 'register']);
    Route::get('/activation', [ClienteAuthController::class, 'showActivation'])->name('espace-cliente.activation');
    Route::post('/activation', [ClienteAuthController::class, 'activate']);
    Route::post('/resend-otp', [ClienteAuthController::class, 'resendOtp'])->name('espace-cliente.resend-otp');
    Route::get('/reset-password', [ClienteAuthController::class, 'showResetRequest'])->name('espace-cliente.reset-request');
    Route::post('/reset-password/send', [ClienteAuthController::class, 'sendResetOtp'])->name('espace-cliente.reset-send');
    Route::get('/reset-password/confirm', [ClienteAuthController::class, 'showResetConfirm'])->name('espace-cliente.reset-confirm');
    Route::post('/reset-password/confirm', [ClienteAuthController::class, 'resetPassword']);

    // Pages protégées (cliente connectée)
    Route::middleware('cliente_auth')->group(function () {
        Route::get('/', [EspaceClienteController::class, 'home'])->name('espace-cliente.home');
        Route::get('/carte', [EspaceClienteController::class, 'carte'])->name('espace-cliente.carte');
        Route::get('/profil', [EspaceClienteController::class, 'profil'])->name('espace-cliente.profil');
        Route::post('/profil/email', [EspaceClienteController::class, 'updateEmail'])->name('espace-cliente.update-email');
        Route::post('/profil/telephone', [EspaceClienteController::class, 'updatePhone'])->name('espace-cliente.update-phone');
        Route::get('/rendezvous', [EspaceClienteController::class, 'rendezvous'])->name('espace-cliente.rendezvous');
        Route::post('/rendezvous', [EspaceClienteController::class, 'storeRendezvous'])->name('espace-cliente.store-rdv');
        Route::post('/rendezvous/{id}/annuler', [EspaceClienteController::class, 'cancelRendezvous'])->name('espace-cliente.cancel-rdv');
        Route::get('/historique', [EspaceClienteController::class, 'historique'])->name('espace-cliente.historique');
        Route::post('/logout', [ClienteAuthController::class, 'logout'])->name('espace-cliente.logout');

        // API cliente (JSON)
        Route::get('/api/stats', [EspaceClienteController::class, 'apiStats'])->name('espace-cliente.api.stats');
        Route::get('/api/historique', [EspaceClienteController::class, 'apiHistorique'])->name('espace-cliente.api.historique');
        Route::get('/api/depenses-mensuelles', [EspaceClienteController::class, 'apiDepensesMensuelles'])->name('espace-cliente.api.depenses-mensuelles');
        Route::get('/api/top-prestations', [EspaceClienteController::class, 'apiTopPrestations'])->name('espace-cliente.api.top-prestations');
    });
});
