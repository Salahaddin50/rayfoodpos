const CACHE_NAME = 'rayfood-admin-v1';
const urlsToCache = [
  '/admin/dashboard',
  '/admin',
  '/css/app.css',
  '/themes/default/css/custom.css',
  '/themes/default/fonts/fontawesome/fontawesome.css',
  '/themes/default/fonts/lab/lab.css'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request)
      .then((response) => response || fetch(event.request))
  );
});

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
