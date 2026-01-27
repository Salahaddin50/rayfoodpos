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
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/images/default/firebase-logo.png',
        data: {
            url: payload.data?.url || '/admin/table-orders'
        }
    };
    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const urlToOpen = event.notification.data?.url || '/admin/table-orders';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // Check if there's already a window open with the app
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    client.focus();
                    client.postMessage({ type: 'NAVIGATE', url: urlToOpen });
                    return;
                }
            }
            // If no window is open, open a new one
            if (clients.openWindow) {
                return clients.openWindow(self.location.origin + urlToOpen);
            }
        })
    );
});
