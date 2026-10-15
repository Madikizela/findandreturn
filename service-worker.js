const CACHE_NAME = 'find-and-return-v1.0.1';
const STATIC_CACHE = 'find-and-return-static-v1.0.1';
const DYNAMIC_CACHE = 'find-and-return-dynamic-v1.0.1';

// Assets to cache on install
const STATIC_ASSETS = [
  '/',
  '/index.php',
  '/login.php',
  '/register.php',
  '/index4.php',
  '/css/custom.css',
  '/css/bootstrap.min.css',
  '/js/main.js',
  '/img/f-icon.ico',
  '/img/logo.png',
  '/img/qr5.png',
  '/img/fi.jpg',
  '/img/pic1.jpg',
  '/offline.html',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
  'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installing...');
  
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => {
        console.log('[Service Worker] Caching static assets');
        return cache.addAll(STATIC_ASSETS.map(url => new Request(url, { cache: 'reload' })));
      })
      .catch((error) => {
        console.error('[Service Worker] Cache install failed:', error);
      })
  );
  
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activating...');
  
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((cacheName) => {
            return cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE;
          })
          .map((cacheName) => {
            console.log('[Service Worker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          })
      );
    })
  );
  
  self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Only handle GET requests — never intercept POST
  if (request.method !== 'GET') {
    return;
  }

  // Skip cross-origin requests (except known CDNs)
  if (url.origin !== location.origin &&
      !url.href.includes('cdn.jsdelivr.net') &&
      !url.href.includes('cdnjs.cloudflare.com') &&
      !url.href.includes('fonts.googleapis.com')) {
    return;
  }

  // Skip PHP pages entirely — let them go straight to network
  if (url.pathname.endsWith('.php') || url.pathname.match(/\/[a-z_]+$/i)) {
    return;
  }

  // Cache-first for static assets (CSS, JS, images, fonts)
  if (url.href.includes('cdn.') ||
      url.href.includes('fonts.') ||
      request.destination === 'style' ||
      request.destination === 'script' ||
      request.destination === 'image' ||
      request.destination === 'font') {
    event.respondWith(
      caches.match(request).then((cached) => {
        return cached || fetch(request).then((response) => {
          if (response.status === 200) {
            const clone = response.clone();
            caches.open(STATIC_CACHE).then((cache) => cache.put(request, clone));
          }
          return response;
        }).catch(() => {
          if (request.destination === 'image') {
            return caches.match('/img/f-icon.ico');
          }
        });
      })
    );
    return;
  }

  // Navigation requests — network first, fall back to offline page
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() => {
        return caches.match(request).then((cached) => {
          return cached || caches.match('/offline.html');
        });
      })
    );
    return;
  }
});

// Background sync for offline submissions
self.addEventListener('sync', (event) => {
  console.log('[Service Worker] Background sync:', event.tag);
  
  if (event.tag === 'sync-forms') {
    event.waitUntil(syncFormData());
  }
});

// Push notification handler
self.addEventListener('push', (event) => {
  console.log('[Service Worker] Push received');
  
  const options = {
    body: event.data ? event.data.text() : 'New update available',
    icon: '/img/android-chrome-192x192.png',
    badge: '/img/f-icon.ico',
    vibrate: [200, 100, 200],
    tag: 'find-and-return-notification',
    requireInteraction: true,
    actions: [
      {
        action: 'open',
        title: 'Open App',
        icon: '/img/f-icon.ico'
      },
      {
        action: 'close',
        title: 'Close',
        icon: '/img/f-icon.ico'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification('Find and Return', options)
  );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
  console.log('[Service Worker] Notification clicked:', event.action);
  
  event.notification.close();
  
  if (event.action === 'open') {
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});

// Helper function to sync form data
async function syncFormData() {
  try {
    // Get pending form submissions from IndexedDB
    // This would be implemented based on your form handling logic
    console.log('[Service Worker] Syncing form data...');
    // Implementation would go here
    return Promise.resolve();
  } catch (error) {
    console.error('[Service Worker] Sync failed:', error);
    return Promise.reject(error);
  }
}

// Message handler for cache updates
self.addEventListener('message', (event) => {
  console.log('[Service Worker] Message received:', event.data);
  
  if (event.data.action === 'skipWaiting') {
    self.skipWaiting();
  }
  
  if (event.data.action === 'clearCache') {
    event.waitUntil(
      caches.keys().then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => caches.delete(cacheName))
        );
      })
    );
  }
});
