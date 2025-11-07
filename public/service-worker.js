const CACHE_NAME = 'abrm-shell-v1';
const STATIC_ASSETS = [
  '/',
  '/manifest.webmanifest',
  '/assets/css/app.css',
  '/assets/js/app.js'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
    )
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (request.method !== 'GET') {
    event.respondWith(fetch(request));
    return;
  }
  if (request.url.includes('/api/v1/')) {
    event.respondWith(staleWhileRevalidate(request));
    return;
  }
  event.respondWith(
    caches.match(request).then((cached) => cached || fetch(request))
  );
});

async function staleWhileRevalidate(request) {
  const cache = await caches.open('abrm-api');
  const cached = await cache.match(request);
  const network = fetch(request).then((response) => {
    cache.put(request, response.clone());
    return response;
  });
  return cached || network;
}

self.addEventListener('sync', (event) => {
  if (event.tag === 'offline-queue') {
    event.waitUntil(processQueue());
  }
});

async function processQueue() {
  const stored = await self.registration.periodicSync.getTags?.();
  return stored || Promise.resolve();
}
