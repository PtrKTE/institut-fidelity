@php
    $role = auth()->user()->role ?? '';
    $menus = [
        'superadmin' => [
            ['route' => 'dashboard', 'icon' => 'fas fa-chart-pie', 'label' => 'Dashboard'],
            // ['route' => 'clientes.index', 'icon' => 'fas fa-users', 'label' => 'Clientes'],
            // ['route' => 'factures.index', 'icon' => 'fas fa-file-invoice', 'label' => 'Factures'],
            // ['route' => 'utilisateurs.index', 'icon' => 'fas fa-user-shield', 'label' => 'Utilisateurs'],
            // ['route' => 'rendezvous.index', 'icon' => 'fas fa-calendar-alt', 'label' => 'Rendez-vous'],
            // ['route' => 'communications.index', 'icon' => 'fas fa-envelope', 'label' => 'Communications'],
        ],
        'admin' => [
            ['route' => 'dashboard', 'icon' => 'fas fa-chart-pie', 'label' => 'Dashboard'],
        ],
        'agent' => [
            ['route' => 'dashboard', 'icon' => 'fas fa-chart-line', 'label' => 'Dashboard'],
        ],
        'compta' => [
            ['route' => 'dashboard', 'icon' => 'fas fa-calculator', 'label' => 'Dashboard'],
        ],
        'comm' => [
            ['route' => 'dashboard', 'icon' => 'fas fa-bullhorn', 'label' => 'Dashboard'],
        ],
    ];
    $items = $menus[$role] ?? [];
@endphp

<aside class="fid-sidebar">
    <div class="fid-sidebar-header">
        <img src="{{ asset('img/lgp.png') }}" alt="ProNails" class="fid-sidebar-logo">
        <span class="fid-sidebar-brand">Prestige</span>
    </div>

    <nav class="fid-sidebar-nav">
        @foreach($items as $item)
            <a href="{{ route($item['route']) }}"
               class="fid-sidebar-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
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
                <i class="fas fa-moon"></i>
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
