importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js");

firebase.initializeApp({
    apiKey: "AIzaSyA6onISFLWbdzsHN2J2HmzPeiOsodjEJ2k",
    authDomain: "nutribasket-1fad9.firebaseapp.com",
    projectId: "nutribasket-1fad9",
    storageBucket: "nutribasket-1fad9.firebasestorage.app",
    messagingSenderId: "789317166563",
    appId: "1:789317166563:web:6482127409d2a9b69a6cd6",
    measurementId: "G-W8THHHB5T0"
});

const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function (payload) {
    const promiseChain = clients
        .matchAll({
            type: "window",
            includeUncontrolled: true
        })
        .then(windowClients => {
            for (let i = 0; i < windowClients.length; i++) {
                const windowClient = windowClients[i];
                windowClient.postMessage(payload);
            }
        })
        .then(() => {
            const title = payload.notification.title;
            const options = {
                body: payload.notification.score
            };
            return registration.showNotification(title, options);
        });
    return promiseChain;
});
self.addEventListener('notificationclick', function (event) {
    console.log('notification received: ', event)
});