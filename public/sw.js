// Service Worker for PWA functionality
const CACHE_NAME = 'caffeinecrash-v1';
const urlsToCache = [
    '/',
    '/css/style.css',
    '/js/app.js',
    '/login.php',
    '/register.php',
    '/dashboard.php',
    '/medications.php',
    '/health.php',
    '/reminders.php',
    '/share.php',
    '/manifest.json'
];

// Install event
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

// Fetch event
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Return cached version or fetch new
                return response || fetch(event.request);
            })
            .catch(() => {
                // Return offline page if available
                return caches.match('/');
            })
    );
});

// Activate event
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});
