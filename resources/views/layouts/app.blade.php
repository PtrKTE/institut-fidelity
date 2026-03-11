<!DOCTYPE html>
<html lang="fr" class="{{ session('dark_mode') ? 'dark-mode' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Pronails Fidelity</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/design-system.css') }}" rel="stylesheet">
    <link href="{{ asset('css/components.css') }}" rel="stylesheet">
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="fid-app-wrapper">
        @include('components.sidebar')

        <main class="fid-main-content" id="mainContent">
            <div class="fid-topbar d-md-none">
                <button class="btn btn-link fid-sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="fid-topbar-title">@yield('title', 'Dashboard')</span>
            </div>

            <div class="fid-page-content fid-fade-in">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Notifications Offcanvas Panel --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="notifPanel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title"><i class="fas fa-bell me-2"></i>Notifications</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0" id="notifList">
            <div class="text-center py-5 text-muted">
                <div class="fid-spinner mx-auto mb-3"></div>
                Chargement...
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

        // Dark mode toggle with icon switch
        function updateDarkModeIcon() {
            const icon = document.getElementById('darkModeIcon');
            if (icon) {
                const isDark = document.documentElement.classList.contains('dark-mode');
                icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
            }
        }
        document.getElementById('darkModeToggle')?.addEventListener('click', function() {
            document.documentElement.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.documentElement.classList.contains('dark-mode'));
            updateDarkModeIcon();
        });
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark-mode');
        }
        updateDarkModeIcon();

        // Sidebar toggle mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.fid-sidebar').classList.toggle('open');
        });

        // Notifications system
        function loadNotifications() {
            $.get("{{ url('/api/notifications') }}", function(data) {
                const badge = $('#notifBadge');
                if (data.count > 0) {
                    badge.text(data.count > 9 ? '9+' : data.count).show();
                } else {
                    badge.hide();
                }

                let html = '';
                if (data.items.length === 0) {
                    html = '<div class="text-center py-5 text-muted"><i class="fas fa-check-circle fa-2x mb-2"></i><p>Aucune notification</p></div>';
                } else {
                    data.items.forEach(n => {
                        const iconMap = { rdv: 'fa-calendar-check text-primary', cliente: 'fa-user-plus text-success', facture: 'fa-file-invoice text-warning', system: 'fa-info-circle text-info' };
                        const iconClass = iconMap[n.type] || 'fa-bell text-muted';
                        html += `<div class="fid-notif-item ${n.read ? '' : 'unread'}">
                            <div class="fid-notif-icon"><i class="fas ${iconClass}"></i></div>
                            <div class="fid-notif-content">
                                <div class="fid-notif-text">${n.message}</div>
                                <small class="fid-notif-time">${n.time_ago}</small>
                            </div>
                        </div>`;
                    });
                }
                $('#notifList').html(html);
            });
        }

        // Load on panel open
        document.getElementById('notifPanel')?.addEventListener('show.bs.offcanvas', loadNotifications);
        // Load badge count on page load
        $.get("{{ url('/api/notifications/count') }}", function(data) {
            if (data.count > 0) {
                $('#notifBadge').text(data.count > 9 ? '9+' : data.count).show();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
