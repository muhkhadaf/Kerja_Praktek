const CACHE_NAME = 'wakacao-v1';
const urlsToCache = [
  '/',
  '/index.php',
  '/login.php',
  '/manifest.json',
  '/css/vertical-layout-light/style.css',
  '/vendors/feather/feather.css',
  '/vendors/ti-icons/css/themify-icons.css',
  '/vendors/css/vendor.bundle.base.css',
  '/vendors/js/vendor.bundle.base.js',
  '/js/off-canvas.js',
  '/js/hoverable-collapse.js',
  '/js/template.js',
  '/js/settings.js',
  '/js/todolist.js',
  '/images/logowakacao.png',
  '/images/pwa/icon-72x72.png',
  '/images/pwa/icon-96x96.png',
  '/images/pwa/icon-128x128.png',
  '/images/pwa/icon-144x144.png',
  '/images/pwa/icon-152x152.png',
  '/images/pwa/icon-192x192.png',
  '/images/pwa/icon-384x384.png',
  '/images/pwa/icon-512x512.png'
];

// Instalasi Service Worker
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Cache dibuka');
        return cache.addAll(urlsToCache);
      })
  );
});

// Aktivasi Service Worker (menghapus cache lama)
self.addEventListener('activate', (event) => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Strategi cache: Cache First, kemudian network
self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // Cache hit - return response
        if (response) {
          return response;
        }
        return fetch(event.request)
          .then((res) => {
            // Jika bukan permintaan yang perlu di-cache, kembalikan saja responsenya
            if (!res || res.status !== 200 || res.type !== 'basic') {
              return res;
            }
            
            // Kloning response untuk dimasukkan ke cache
            const responseToCache = res.clone();
            
            caches.open(CACHE_NAME)
              .then((cache) => {
                cache.put(event.request, responseToCache);
              });
              
            return res;
          })
          .catch(() => {
            // Jika gagal mengambil dari network dan permintaan adalah untuk halaman,
            // tampilkan halaman offline
            if (event.request.mode === 'navigate') {
              return caches.match('/offline.html');
            }
          });
      })
  );
});

// Event untuk menampilkan konten offline
self.addEventListener('fetch', (event) => {
  if (event.request.mode === 'navigate' || (event.request.method === 'GET' && event.request.headers.get('accept').includes('text/html'))) {
    event.respondWith(
      fetch(event.request.url)
        .catch(() => {
          return caches.match('/offline.html');
        })
    );
  }
});

// Event untuk menangani push notification
self.addEventListener('push', (event) => {
  const title = 'Wakacao App';
  const options = {
    body: event.data.text(),
    icon: '/images/pwa/icon-192x192.png',
    badge: '/images/pwa/icon-72x72.png'
  };
  
  event.waitUntil(self.registration.showNotification(title, options));
});

// Event untuk menangani klik pada notifikasi
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  event.waitUntil(
    clients.openWindow('/')
  );
}); 