const CACHE_NAME = 'rayfood-pos-v1';
const urlsToCache = [
  '/',
  '/css/app.css',
  '/themes/default/css/custom.css',
  '/themes/default/fonts/fontawesome/fontawesome.css',
  '/themes/default/fonts/lab/lab.css'
];

// Install event - cache resources
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        return cache.addAll(urlsToCache);
      })
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Return cached version or fetch from network
        return response || fetch(event.request);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Show notification from page (PWA-safe: no "new Notification()" in window)
self.addEventListener('message', (event) => {
  if (event.data?.type === 'SHOW_NOTIFICATION' && event.data.title) {
    const options = event.data.options || {};
    self.registration.showNotification(event.data.title, options).catch(() => {});
  }
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const url = event.notification.data?.url;
  if (url) {
    const fullUrl = new URL(url, self.location.origin).href;
    event.waitUntil(
      clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
        for (let i = 0; i < clientList.length; i++) {
          if (new URL(clientList[i].url).origin === self.location.origin) {
            return clientList[i].focus().then(() => clientList[i].navigate(fullUrl));
          }
        }
        return clients.openWindow(fullUrl);
      })
    );
  }
});
