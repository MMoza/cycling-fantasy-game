const CACHE_NAME = 'pedales-v2';

const STATIC_ASSETS = [
    '/offline',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) =>
            Promise.allSettled(STATIC_ASSETS.map((url) => cache.add(url).catch(() => null)))
        ),
    );
    self.skipWaiting();
});

self.addEventListener('fetch', (event) => {
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match('/offline')),
        );
        return;
    }

    event.respondWith(
        fetch(event.request).then((response) => {
            if (response && response.status === 200) {
                const clone = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
            }
            return response;
        }).catch(() => caches.match(event.request)),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((names) =>
            Promise.all(names.filter((n) => n !== CACHE_NAME).map((n) => caches.delete(n))),
        ),
    );
});

self.addEventListener('push', (event) => {
    if (event.data) {
        const data = event.data.json();
        const notificationTitle = data.notification?.title || 'Pedales';
        const notificationOptions = {
            body: data.notification?.body || '',
            icon: '/icons/icon-192.svg',
            badge: '/icons/icon-192.svg',
            data: data.data,
        };

        event.waitUntil(
            self.registration.showNotification(notificationTitle, notificationOptions)
        );
    }
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const url = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
