// No Firebase SDK needed here - the main app handles FCM subscription.
// This SW only needs to handle: push events, notification display, and notification clicks.

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

// Handle push events from FCM (background notifications)
self.addEventListener('push', (event) => {
    if (!event.data) return;

    let payload;
    try {
        payload = event.data.json();
    } catch (e) {
        payload = { notification: { title: 'New Notification', body: event.data.text() } };
    }

    const notificationTitle = payload.notification?.title || payload.data?.title || 'New order';
    const notificationBody = payload.notification?.body || payload.data?.body || '';
    const notificationOptions = {
        body: notificationBody,
        icon: '/images/default/firebase-logo.png',
        badge: '/images/default/firebase-logo.png',
        silent: false,
        requireInteraction: true,
        tag: 'order-notification-' + Date.now(),
        data: {
            url: payload.data?.url || '/admin/table-orders',
            topicName: payload.data?.topicName || payload.data?.topicname
        }
    };

    event.waitUntil(
        self.registration.showNotification(notificationTitle, notificationOptions)
    );
});

// Show notification from page (test button + foreground notifications)
self.addEventListener('message', (event) => {
    if (event.data?.type === 'SHOW_NOTIFICATION' && event.data.title) {
        const options = event.data.options || {};
        if (typeof options.silent === 'undefined') options.silent = false;
        if (typeof options.requireInteraction === 'undefined') options.requireInteraction = true;
        self.registration.showNotification(event.data.title, options).catch(() => {});
    }
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const urlToOpen = event.notification.data?.url || '/admin/table-orders';
    const fullUrl = new URL(urlToOpen, self.location.origin).href;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (new URL(client.url).origin === self.location.origin) {
                    return client.focus().then(() => {
                        client.postMessage({ type: 'NAVIGATE', url: urlToOpen });
                        return client;
                    }).catch(() => clients.openWindow(fullUrl));
                }
            }
            return clients.openWindow(fullUrl);
        })
    );
});
