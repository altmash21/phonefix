var staticCacheName = 'pwa-v2';

var filesToCache = [
    '/',
    '/index.php',
    '/manifest.json',
    '/serviceworker.js',
    '/money.json',
    '/shortcuts-config.json',
    '/css/app.css',
    '/css/custom_loading.css',
    '/css/element.css',
    '/css/print.css',
    '/css/third_party/dropzone_custom.css',
    '/css/third_party/swiper-bundle.min.css',
    '/css/third_party/vue-html-editor.css',
    '/css/third_party/vue-html-editor_custom.css',
    '/js/auth/common.min.js',
    '/js/auth/users.min.js',
    '/js/banking/accounts.min.js',
    '/js/banking/reconciliations.min.js',
    '/js/banking/transactions.min.js',
    '/js/banking/transfers.min.js',
    '/js/common/companies.min.js',
    '/js/common/contacts.min.js',
    '/js/common/dashboards.min.js',
    '/js/common/documents.min.js',
    '/js/common/imports.min.js',
    '/js/common/items.min.js',
    '/js/common/reports.min.js',
    '/js/install.min.js',
    '/js/install/update.min.js',
    '/js/modules/apps.min.js',
    '/js/portal/apps.min.js',
    '/js/settings/categories.min.js',
    '/js/settings/currencies.min.js',
    '/js/settings/settings.min.js',
    '/js/settings/taxes.min.js',
    '/js/wizard/wizard.min.js',
    '/akaunting-js/generalAction.js',
    '/akaunting-js/hotkeys.js',
    '/akaunting-js/popper.js',
    '/akaunting-js/swiper-bundle.min.js',
    '/fonts/MaterialIcons-Regular.woff',
    '/fonts/MaterialIcons-Regular.woff2',
    '/img/favicon.ico',
    '/img/akaunting-logo-gold.png',
    '/img/akaunting-logo-green.svg',
    '/img/akaunting-logo-horizontal.svg',
    '/img/akaunting-logo-purple.svg',
    '/img/akaunting-logo-white.svg',
    '/img/akaunting-logo-wild-blue.png',
    '/img/akaunting-loading.gif',
    '/img/pwa/icon-192x192.png',
    '/img/pwa/icon-192x192-maskable.png',
    '/img/pwa/icon-512x512.png',
    '/img/pwa/icon-512x512-maskable.png',
    '/img/pwa/splash-640x1136.png',
    '/img/pwa/splash-750x1334.png',
    '/img/pwa/splash-828x1792.png',
    '/img/pwa/splash-1242x2208.png',
    '/img/pwa/splash-1242x2688.png',
    '/img/pwa/splash-1536x2048.png',
    '/img/pwa/splash-1668x2224.png',
    '/img/pwa/splash-1668x2388.png',
    '/img/pwa/splash-2048x2732.png',
    '/css/fonts/element-icons.woff'
];

// Cache on install
self.addEventListener('install', function(event) {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(function(cache) {
                return cache.addAll(filesToCache);
            })
    );
});

// Clear old caches on activate
self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames
                    .filter(function(cacheName) {
                        return cacheName.startsWith('pwa-') && cacheName !== staticCacheName;
                    })
                    .map(function(cacheName) {
                        return caches.delete(cacheName);
                    })
            );
        })
    );
    this.clients.claim();
});

// Serve from cache, fall back to network, then offline
self.addEventListener('fetch', function(event) {
    event.respondWith(
        caches.match(event.request).then(function(response) {
            return response || fetch(event.request).then(function(networkResponse) {
                // Cache successful GET responses for future offline use
                if (event.request.method === 'GET' && networkResponse && networkResponse.status === 200) {
                    var responseToCache = networkResponse.clone();
                    caches.open(staticCacheName).then(function(cache) {
                        cache.put(event.request, responseToCache);
                    });
                }
                return networkResponse;
            });
        }).catch(function() {
            return caches.match('/');
        })
    );
});
