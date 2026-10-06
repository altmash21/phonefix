var staticCacheName = 'pwa-v4-network-first';

var filesToCache = [
    '/manifest.json',
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

// Cache on install & force immediate takeover
self.addEventListener('install', function(event) {
    self.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName).then(function(cache) {
            return cache.addAll(filesToCache).catch(function(err) {
                console.warn('[PWA] Pre-cache partial fail, continuing:', err);
            });
        })
    );
});

// Clear ALL old caches on activate immediately
self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheName !== staticCacheName) {
                        console.log('[PWA] Purging outdated cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(function() {
            return self.clients.claim();
        })
    );
});

// Fetch handler: NETWORK-FIRST for all HTML / dynamic requests!
self.addEventListener('fetch', function(event) {
    var request = event.request;

    // Only handle GET requests
    if (request.method !== 'GET') {
        return;
    }

    var url = request.url;
    var isHtmlOrRoute = request.mode === 'navigate' ||
        (request.headers.get('accept') && request.headers.get('accept').includes('text/html')) ||
        url.includes('/mobileshop/') ||
        url.includes('/admin/') ||
        url.includes('/portal/') ||
        url.includes('/api/');

    // 1. ALL DYNAMIC HTML ROUTES: STRICT NETWORK-FIRST
    // Ensures latest blade views and updates appear immediately without browser caching!
    if (isHtmlOrRoute) {
        event.respondWith(
            fetch(request)
                .then(function(networkResponse) {
                    if (networkResponse && networkResponse.status === 200) {
                        var responseClone = networkResponse.clone();
                        caches.open(staticCacheName).then(function(cache) {
                            cache.put(request, responseClone);
                        });
                    }
                    return networkResponse;
                })
                .catch(function() {
                    // Fall back to cache ONLY if completely offline
                    return caches.match(request);
                })
        );
        return;
    }

    // 2. STATIC ASSETS (Images, Fonts, Vendor CSS/JS): Stale-While-Revalidate
    event.respondWith(
        caches.match(request).then(function(cachedResponse) {
            var fetchPromise = fetch(request).then(function(networkResponse) {
                if (networkResponse && networkResponse.status === 200) {
                    var responseClone = networkResponse.clone();
                    caches.open(staticCacheName).then(function(cache) {
                        cache.put(request, responseClone);
                    });
                }
                return networkResponse;
            }).catch(function() {
                return cachedResponse;
            });

            return cachedResponse || fetchPromise;
        })
    );
});
