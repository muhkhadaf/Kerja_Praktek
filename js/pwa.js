// Variabel untuk menyimpan event prompt instalasi PWA
let deferredPrompt;

// Register service worker
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then(registration => {
        console.log('Service Worker berhasil didaftarkan dengan scope:', registration.scope);
      })
      .catch(error => {
        console.log('Pendaftaran Service Worker gagal:', error);
      });
  });
}

// Tangkap event beforeinstallprompt
window.addEventListener('beforeinstallprompt', (e) => {
  // Cegah Chrome menampilkan prompt instalasi otomatis
  e.preventDefault();
  // Simpan event yang sudah di-trigger
  deferredPrompt = e;
  // Tampilkan tombol instalasi jika ada
  const installButton = document.getElementById('pwa-install-button');
  if (installButton) {
    installButton.style.display = 'block';
  }
});

// Fungsi untuk menginstal PWA ketika tombol diklik
function installPWA() {
  const installButton = document.getElementById('pwa-install-button');
  
  if (!deferredPrompt) {
    console.log('Tidak dapat menginstal: prompt instalasi tidak tersedia');
    return;
  }
  
  // Tampilkan prompt instalasi
  deferredPrompt.prompt();
  
  // Tunggu pengguna merespons prompt
  deferredPrompt.userChoice.then((choiceResult) => {
    if (choiceResult.outcome === 'accepted') {
      console.log('Pengguna menerima instalasi PWA');
      // Sembunyikan tombol setelah instalasi
      if (installButton) {
        installButton.style.display = 'none';
      }
    } else {
      console.log('Pengguna menolak instalasi PWA');
    }
    
    // Reset variabel deferredPrompt
    deferredPrompt = null;
  });
}

// Cek apakah aplikasi sudah diinstal
window.addEventListener('appinstalled', (evt) => {
  console.log('Aplikasi berhasil diinstal');
  // Sembunyikan tombol instalasi
  const installButton = document.getElementById('pwa-install-button');
  if (installButton) {
    installButton.style.display = 'none';
  }
}); 