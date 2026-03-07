var CACHE = 'sihealing-v1';
var OFFLINE_URL = '/offline';

// Install: cache offline page
self.addEventListener('install', function(e) {
    e.waitUntil(
        caches.open(CACHE).then(function(c) {
            return c.addAll([OFFLINE_URL, '/gedung.webp']);
        })
    );
    self.skipWaiting();
});

// Activate: clean old caches
self.addEventListener('activate', function(e) {
    e.waitUntil(
        caches.keys().then(function(keys) {
            return Promise.all(keys.filter(function(k) { return k !== CACHE; }).map(function(k) { return caches.delete(k); }));
        })
    );
    self.clients.claim();
});

// Fetch: network first, fallback to offline page for navigation
self.addEventListener('fetch', function(e) {
    if (e.request.mode === 'navigate') {
        e.respondWith(
            fetch(e.request).catch(function() {
                return caches.match(OFFLINE_URL);
            })
        );
    }
});
