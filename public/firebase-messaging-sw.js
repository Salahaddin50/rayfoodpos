importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');
let config = {
        apiKey: "AIzaSyBLVngaS_tDeMogfNmVEfqQ1u_HyqXMqc4",
        authDomain: "foodscan-5102b.firebaseapp.com",
        projectId: "foodscan-5102b",
        storageBucket: "foodscan-5102b.appspot.com",
        messagingSenderId: "1068326850326",
        appId: "1:1068326850326:web:fb724f0c9ae7f487ee4a37",
        measurementId: "G-8SFLD2GVEV",
 };
firebase.initializeApp(config);
const messaging = firebase.messaging();
messaging.onBackgroundMessage((payload) => {
    const notif = payload.notification || {};
    const data = payload.data || {};
    const notificationTitle = notif.title || 'New order';
    const notificationOptions = {
        body: notif.body || '',
        icon: '/images/default/firebase-logo.png',
        tag: data.topicName || 'fcm',
        requireInteraction: false,
        silent: false,
        data: {
            url: data.url || '/admin/table-orders'
        }
    };
    return self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    
    const urlToOpen = event.notification.data?.url || '/admin/table-orders';
    const fullUrl = new URL(urlToOpen, self.location.origin).href;
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // Check if there's already a window open with the app
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                const clientOrigin = new URL(client.url).origin;
                if (clientOrigin === self.location.origin) {
                    // Focus existing window and navigate
                    return client.focus().then(() => {
                        return client.navigate(fullUrl);
                    });
                }
            }
            // If no window is open, open a new one
            return clients.openWindow(fullUrl);
        })
    );
});
