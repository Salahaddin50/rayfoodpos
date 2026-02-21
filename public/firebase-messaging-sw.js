importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

// Fetch Firebase config dynamically from the server (set by backend)
// This ensures consistency between frontend and service worker
self.addEventListener('message', (event) => {
    if (event.data?.type === 'INIT_FIREBASE' && event.data.config) {
        try {
            if (!firebase.apps.length) {
                firebase.initializeApp(event.data.config);
            }
        } catch (e) {
            console.error('Firebase init error in SW:', e);
        }
    }
});

// Fallback config (same as current settings, but will be overridden by dynamic config)
let config = {
        apiKey: "AIzaSyBLVngaS_tDeMogfNmVEfqQ1u_HyqXMqc4",
        authDomain: "foodscan-5102b.firebaseapp.com",
        projectId: "foodscan-5102b",
        storageBucket: "foodscan-5102b.appspot.com",
        messagingSenderId: "1068326850326",
        appId: "1:1068326850326:web:fb724f0c9ae7f487ee4a37",
        measurementId: "G-8SFLD2GVEV",
 };

if (!firebase.apps.length) {
    firebase.initializeApp(config);
}
const messaging = firebase.messaging();

// Show notification from page (test button + foreground order popup); PWA-safe (no new Notification() in window)
self.addEventListener('message', (event) => {
    if (event.data?.type === 'SHOW_NOTIFICATION' && event.data.title) {
        const options = event.data.options || {};
        if (typeof options.silent === 'undefined') options.silent = false;
        if (typeof options.requireInteraction === 'undefined') options.requireInteraction = true;
        self.registration.showNotification(event.data.title, options).catch((err) => {
            console.error('SW: showNotification error:', err);
        });
    }
});

messaging.onBackgroundMessage((payload) => {
    console.log('SW: Background message received:', payload);
    const notificationTitle = payload.notification?.title || 'New order';
    const notificationBody = payload.notification?.body || '';
    const notificationOptions = {
        body: notificationBody,
        icon: '/images/default/firebase-logo.png',
        badge: '/images/default/firebase-logo.png',
        silent: false,
        requireInteraction: true,
        tag: 'order-notification',
        data: {
            url: payload.data?.url || '/admin/table-orders',
            topicName: payload.data?.topicName || payload.data?.topicname
        }
    };
    return self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    console.log('SW: Notification clicked:', event.notification);
    event.notification.close();
    
    const urlToOpen = event.notification.data?.url || '/admin/table-orders';
    const fullUrl = new URL(urlToOpen, self.location.origin).href;
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            console.log('SW: Found clients:', clientList.length);
            // Check if there's already a window open with the app
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                const clientOrigin = new URL(client.url).origin;
                if (clientOrigin === self.location.origin) {
                    console.log('SW: Focusing existing client and navigating');
                    // Focus existing window and navigate
                    return client.focus().then(() => {
                        // Post message to client to handle navigation (better for SPA routing)
                        client.postMessage({
                            type: 'NAVIGATE',
                            url: urlToOpen
                        });
                        return client;
                    }).catch((err) => {
                        console.error('SW: Focus/navigate error:', err);
                        // Fallback to direct navigation
                        return client.navigate(fullUrl);
                    });
                }
            }
            // If no window is open, open a new one
            console.log('SW: Opening new window');
            return clients.openWindow(fullUrl);
        }).catch((err) => {
            console.error('SW: notificationclick error:', err);
        })
    );
});
