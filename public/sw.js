const CACHE_NAME = 'prestige-v1';
const PREFIX = '/fidelity-laravel';

const STATIC_ASSETS = [
    PREFIX + '/espace-cliente',
    PREFIX + '/public/css/design-system.css',
    PREFIX + '/public/css/components.css',
    PREFIX + '/public/css/animations.css',
    PREFIX + '/public/css/cliente.css',
    PREFIX + '/public/img/lgp.png',
    PREFIX + '/public/img/lgpf.png',
    PREFIX + '/offline',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css',
    'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap',
    'https://code.jquery.com/jquery-3.7.1.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11'
];

// Install — pre-cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_ASSETS).catch(err => {
                console.warn('SW: some assets failed to cache', err);
            });
        })
    );
    self.skipWaiting();
});

// Activate — clean old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Fetch strategy
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // API calls: network-first, no cache fallback
    if (url.pathname.includes('/api/')) {
        event.respondWith(
            fetch(event.request).catch(() =>
                new Response(JSON.stringify({ error: 'offline' }), {
                    headers: { 'Content-Type': 'application/json' }
                })
            )
        );
        return;
    }

    // Static assets (CSS, JS, fonts, images): cache-first
    if (event.request.destination === 'style' ||
        event.request.destination === 'script' ||
        event.request.destination === 'font' ||
        event.request.destination === 'image') {
        event.respondWith(
            caches.match(event.request).then(cached =>
                cached || fetch(event.request).then(resp => {
                    const clone = resp.clone();
                    caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                    return resp;
                })
            ).catch(() => caches.match(event.request))
        );
        return;
    }

    // HTML pages: network-first, fallback to cache then offline page
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then(resp => {
                    const clone = resp.clone();
                    caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                    return resp;
                })
                .catch(() =>
                    caches.match(event.request)
                        .then(cached => cached || caches.match(PREFIX + '/offline'))
                )
        );
        return;
    }

    // Default: network with cache fallback
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});
