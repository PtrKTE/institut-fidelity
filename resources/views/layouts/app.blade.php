<!DOCTYPE html>
<html lang="fr" class="{{ session('dark_mode') ? 'dark-mode' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Prestige by ProNails</title>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

        // Dark mode toggle
        document.getElementById('darkModeToggle')?.addEventListener('click', function() {
            document.documentElement.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.documentElement.classList.contains('dark-mode'));
        });
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark-mode');
        }

        // Sidebar toggle mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.fid-sidebar').classList.toggle('open');
        });
    </script>
    @stack('scripts')
</body>
</html>
