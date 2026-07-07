importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');

firebase.initializeApp({
    apiKey: "AIzaSyCh0GT9sKvGS0kb_34iTwJaCcgU20EJxJk",
    authDomain: "nutribasket-1fad9.firebaseapp.com",
    projectId: "nutribasket-1fad9",
    storageBucket: "nutribasket-1fad9.firebasestorage.app",
    messagingSenderId: "789317166563",
    appId: "1:789317166563:android:ae0cdbcb2bae91889a6cd6",
    measurementId: "G-492284538"
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function (payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body ? payload.data.body : '',
        icon: payload.data.icon ? payload.data.icon : ''
    });
});