<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#c4226e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pronails Fidelity')</title>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/icon-192.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/design-system.css') }}" rel="stylesheet">
    <link href="{{ asset('css/components.css') }}" rel="stylesheet">
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
    <link href="{{ asset('css/cliente.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    {{-- Content (auth pages handle their own full-screen layout) --}}
    @hasSection('full-page')
        @yield('content')
    @else
        <div class="cliente-app">
            <div class="cliente-content">
                @yield('content')
            </div>
        </div>
    @endif

    {{-- Bottom Navigation (authenticated pages only) --}}
    @auth_cliente
    @hasSection('show-nav')
    <nav class="cliente-bottom-nav">
        <a href="{{ route('espace-cliente.home') }}" class="{{ request()->routeIs('espace-cliente.home') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Accueil</span>
        </a>
        <a href="{{ route('espace-cliente.carte') }}" class="{{ request()->routeIs('espace-cliente.carte') ? 'active' : '' }}">
            <i class="fas fa-id-card"></i>
            <span>Carte</span>
        </a>
        <a href="{{ route('espace-cliente.rendezvous') }}" class="{{ request()->routeIs('espace-cliente.rendezvous') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i>
            <span>RDV</span>
            <span class="nav-badge" id="badgeRdv" style="display:none"></span>
        </a>
        <a href="{{ route('espace-cliente.historique') }}" class="{{ request()->routeIs('espace-cliente.historique') ? 'active' : '' }}">
            <i class="fas fa-receipt"></i>
            <span>Historique</span>
        </a>
        <a href="{{ route('espace-cliente.profil') }}" class="{{ request()->routeIs('espace-cliente.profil') ? 'active' : '' }}">
            <i class="fas fa-user"></i>
            <span>Profil</span>
        </a>
    </nav>
    @endif
    @endauth_cliente

    {{-- PWA Install Banner --}}
    <div class="pwa-install-banner" id="pwaBanner">
        <img src="{{ asset('img/icon-192.png') }}" class="pwa-icon" alt="Pronails Fidelity">
        <div class="pwa-text">
            <strong>Installer Pronails Fidelity</strong>
            Acces rapide depuis votre ecran
        </div>
        <button class="btn-install" id="btnInstall">Installer</button>
        <button class="btn-dismiss" id="btnDismiss">&times;</button>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // Service Worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('{{ asset("sw.js") }}')
            .then(r => console.log('SW registered', r.scope))
            .catch(e => console.warn('SW failed', e));
    }

    // PWA Install Banner
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', e => {
        e.preventDefault();
        deferredPrompt = e;
        if (!localStorage.getItem('pwa_dismissed')) {
            $('#pwaBanner').addClass('show');
        }
    });
    $('#btnInstall').on('click', () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(() => { deferredPrompt = null; });
        }
        $('#pwaBanner').removeClass('show');
    });
    $('#btnDismiss').on('click', () => {
        $('#pwaBanner').removeClass('show');
        localStorage.setItem('pwa_dismissed', '1');
    });

    // RDV badge
    @auth_cliente
    $.get("{{ route('espace-cliente.api.stats') }}", d => {
        if (d.rdv_en_attente > 0) {
            $('#badgeRdv').text(d.rdv_en_attente).show();
        }
    });
    @endauth_cliente
    </script>
    @stack('scripts')
</body>
</html>
