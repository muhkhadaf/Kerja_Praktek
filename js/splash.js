// Fungsi menampilkan splash screen
function showSplashScreen() {
  // Buat elemen splash screen
  const splashScreen = document.createElement('div');
  splashScreen.className = 'pwa-splash-screen';
  
  // Tambahkan logo
  const logo = document.createElement('img');
  logo.src = 'images/logowakacao.png';
  logo.alt = 'Wakacao Logo';
  splashScreen.appendChild(logo);
  
  // Tambahkan spinner
  const spinner = document.createElement('div');
  spinner.className = 'splash-spinner';
  splashScreen.appendChild(spinner);
  
  // Tambahkan ke DOM
  document.body.appendChild(splashScreen);
  
  // Sembunyikan splash screen setelah konten dimuat
  setTimeout(() => {
    hideSplashScreen();
  }, 2500); // Tampilkan selama 2.5 detik
}

// Fungsi menyembunyikan splash screen
function hideSplashScreen() {
  const splashScreen = document.querySelector('.pwa-splash-screen');
  if (splashScreen) {
    splashScreen.classList.add('hidden');
    
    // Hapus dari DOM setelah animasi selesai
    setTimeout(() => {
      splashScreen.remove();
    }, 500); // sesuai dengan durasi transisi CSS
  }
}

// Jalankan splash screen saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
  showSplashScreen();
}); 