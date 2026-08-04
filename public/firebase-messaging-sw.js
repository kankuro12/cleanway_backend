/* Firebase Messaging service worker.
   Web app config below is public client data (safe to ship to browsers);
   the admin SDK credential lives only on the server. */
importScripts("https://www.gstatic.com/firebasejs/12.17.0/firebase-app-compat.js");
importScripts("https://www.gstatic.com/firebasejs/12.17.0/firebase-messaging-compat.js");

const firebaseConfig = {
  apiKey: "AIzaSyAzRyBhEijRPznusCUijjsK4A07kaYF7gk",
  authDomain: "test-cc2e6.firebaseapp.com",
  projectId: "test-cc2e6",
  storageBucket: "test-cc2e6.firebasestorage.app",
  messagingSenderId: "305457888207",
  appId: "1:305457888207:web:2567f911d03d0f6e6f70ba",
  measurementId: "G-74D132F5WY"
};

const app = firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

messaging.onBackgroundMessage(function (payload) {
  const title = payload.notification?.title || "CleanWay Ops";
  const options = {
    body: payload.notification?.body || "",
    icon: "/favicon.svg"
  };
  self.registration.showNotification(title, options);
});

self.addEventListener("notificationclick", function (event) {
  event.notification.close();
  event.waitUntil(clients.openWindow("/admin/notifications"));
});
