@php
    $role = auth()->user()->role ?? '';
    $menus = [
        'superadmin' => [
            ['route' => 'dashboard', 'icon' => 'fas fa-chart-pie', 'label' => 'Tableau de bord'],
            ['route' => 'clientes.create', 'icon' => 'fas fa-user-plus', 'label' => 'Ajouter cliente'],
            ['route' => 'clientes.index', 'icon' => 'fas fa-users', 'label' => 'Toutes clientes'],
            ['route' => 'caisse', 'icon' => 'fas fa-cash-register', 'label' => 'Caisse'],
            ['route' => 'factures.index', 'icon' => 'fas fa-file-invoice', 'label' => 'Historique factures'],
            ['route' => 'rendezvous.index', 'icon' => 'fas fa-calendar-check', 'label' => 'Gestion des RDV'],
            ['route' => 'depenses.index', 'icon' => 'fas fa-receipt', 'label' => 'Depenses'],
            ['route' => 'operations-bancaires.index', 'icon' => 'fas fa-university', 'label' => 'Banque'],
            ['route' => 'utilisateurs.index', 'icon' => 'fas fa-user-shield', 'label' => 'Utilisateurs'],
            ['route' => 'communications.index', 'icon' => 'fas fa-bullhorn', 'label' => 'Communications'],
        ],
        'admin' => [
            ['route' => 'dashboard', 'icon' => 'fas fa-chart-pie', 'label' => 'Tableau de bord'],
            ['route' => 'clientes.index', 'icon' => 'fas fa-users', 'label' => 'Toutes clientes'],
            ['route' => 'factures.index', 'icon' => 'fas fa-file-invoice', 'label' => 'Historique factures'],
            ['route' => 'rendezvous.index', 'icon' => 'fas fa-calendar-check', 'label' => 'Gestion des RDV'],
        ],
        'agent' => [
            ['route' => 'dashboard', 'icon' => 'fas fa-chart-line', 'label' => 'Tableau de bord'],
            ['route' => 'clientes.index', 'icon' => 'fas fa-users', 'label' => 'Mes clientes'],
            ['route' => 'caisse', 'icon' => 'fas fa-cash-register', 'label' => 'Ma caisse'],
            ['route' => 'factures.index', 'icon' => 'fas fa-file-invoice', 'label' => 'Mes factures'],
            ['route' => 'depenses.index', 'icon' => 'fas fa-receipt', 'label' => 'Mes depenses'],
            ['route' => 'operations-bancaires.index', 'icon' => 'fas fa-university', 'label' => 'Operations bancaires'],
            ['route' => 'rendezvous.index', 'icon' => 'fas fa-calendar-alt', 'label' => 'Mes rendez-vous'],
        ],
        'compta' => [
            ['route' => 'dashboard', 'icon' => 'fas fa-calculator', 'label' => 'Tableau de bord'],
            ['route' => 'clientes.create', 'icon' => 'fas fa-user-plus', 'label' => 'Ajouter cliente'],
            ['route' => 'clientes.index', 'icon' => 'fas fa-users', 'label' => 'Mes clientes'],
            ['route' => 'caisse', 'icon' => 'fas fa-cash-register', 'label' => 'Ma caisse'],
            ['route' => 'factures.index', 'icon' => 'fas fa-file-invoice', 'label' => 'Mes factures'],
            ['route' => 'depenses.index', 'icon' => 'fas fa-receipt', 'label' => 'Mes depenses'],
            ['route' => 'rendezvous.index', 'icon' => 'fas fa-calendar-alt', 'label' => 'Mes rendez-vous'],
        ],
        'comm' => [
            ['route' => 'dashboard', 'icon' => 'fas fa-bullhorn', 'label' => 'Tableau de bord'],
            ['route' => 'clientes.create', 'icon' => 'fas fa-user-plus', 'label' => 'Ajouter cliente'],
            ['route' => 'clientes.index', 'icon' => 'fas fa-users', 'label' => 'Toutes clientes'],
            ['route' => 'rendezvous.index', 'icon' => 'fas fa-calendar-check', 'label' => 'Gestion des RDV'],
            ['route' => 'communications.index', 'icon' => 'fas fa-bullhorn', 'label' => 'Communications'],
        ],
    ];
    $items = $menus[$role] ?? [];
@endphp

<aside class="fid-sidebar">
    <div class="fid-sidebar-header">
        <img src="{{ asset('img/lgp.png') }}" alt="ProNails" class="fid-sidebar-logo">
        <span class="fid-sidebar-brand">Fidelity</span>
    </div>

    <nav class="fid-sidebar-nav">
        @foreach($items as $item)
            <a href="{{ route($item['route']) }}"
               class="fid-sidebar-link {{ request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*') ? 'active' : '' }}">
                <i class="{{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="fid-sidebar-footer">
        <div class="fid-sidebar-user">
            <i class="fas fa-user-circle"></i>
            <div>
                <div class="fid-sidebar-username">{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</div>
                <small class="fid-sidebar-role">{{ ucfirst(auth()->user()->role) }}</small>
            </div>
        </div>
        <div class="fid-sidebar-actions">
            <button id="darkModeToggle" class="btn btn-sm btn-link fid-sidebar-action" title="Mode sombre">
                <i class="fas fa-moon" id="darkModeIcon"></i>
            </button>
            <button id="notifToggle" class="btn btn-sm btn-link fid-sidebar-action position-relative" title="Notifications" data-bs-toggle="offcanvas" data-bs-target="#notifPanel">
                <i class="fas fa-bell"></i>
                <span class="fid-notif-badge" id="notifBadge" style="display:none;"></span>
            </button>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-link fid-sidebar-action" title="Déconnexion">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
