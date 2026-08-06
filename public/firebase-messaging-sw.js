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

// Force Chrome to activate updated service worker immediately
self.addEventListener("install", function () {
  self.skipWaiting();
});

self.addEventListener("activate", function (event) {
  event.waitUntil(clients.claim());
});

messaging.onBackgroundMessage(function (payload) {
  // If Chrome / Firebase SDK already handles the native notification payload, skip manual render to avoid duplicate popups
  if (payload.notification) {
    return;
  }

  const title = payload.data?.title || "CleanWay Ops";
  const logoUrl = self.location.origin + "/logo.jpg";
  const options = {
    body: payload.data?.body || "",
    icon: logoUrl,
    badge: logoUrl,
    image: logoUrl,
    data: payload.data || {}
  };

  self.registration.showNotification(title, options);
});

self.addEventListener("notificationclick", function (event) {
  event.notification.close();
  const urlToOpen = event.notification.data?.url || "/admin/notifications";
  event.waitUntil(clients.openWindow(urlToOpen));
});
