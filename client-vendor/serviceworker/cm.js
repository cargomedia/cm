self.addEventListener('install', function(event) {
  // Automatically take over the previous worker.
  event.waitUntil(self.skipWaiting());
});
