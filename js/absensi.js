// Fungsi untuk memulai webcam
function startWebcam(videoElement) {
  if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    navigator.mediaDevices.getUserMedia({ video: true })
      .then(function(stream) {
        videoElement.srcObject = stream;
        videoElement.play();
      })
      .catch(function(error) {
        console.error('Error accessing webcam:', error);
        alert('Tidak dapat mengakses kamera. Pastikan browser Anda mengizinkan akses kamera.');
      });
  } else {
    alert('Maaf, browser Anda tidak mendukung akses kamera. Gunakan browser modern seperti Chrome, Firefox, atau Edge.');
  }
}

// Fungsi untuk mengambil gambar dari webcam
function captureImage(videoElement, canvasElement) {
  const context = canvasElement.getContext('2d');
  
  if (videoElement.videoWidth && videoElement.videoHeight) {
    canvasElement.width = videoElement.videoWidth;
    canvasElement.height = videoElement.videoHeight;
    context.drawImage(videoElement, 0, 0, videoElement.videoWidth, videoElement.videoHeight);
    
    return canvasElement.toDataURL('image/jpeg');
  }
  
  return null;
}

// Fungsi untuk mengonversi data URL menjadi Blob
function dataURLtoBlob(dataURL) {
  const parts = dataURL.split(';base64,');
  const contentType = parts[0].split(':')[1];
  const raw = window.atob(parts[1]);
  const rawLength = raw.length;
  const uInt8Array = new Uint8Array(rawLength);
  
  for (let i = 0; i < rawLength; ++i) {
    uInt8Array[i] = raw.charCodeAt(i);
  }
  
  return new Blob([uInt8Array], { type: contentType });
}

// Fungsi untuk mendapatkan lokasi pengguna
function getLocation() {
  return new Promise((resolve, reject) => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        position => {
          resolve({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy
          });
        },
        error => {
          let errorMessage = 'Tidak dapat mendapatkan lokasi: ';
          switch(error.code) {
            case error.PERMISSION_DENIED:
              errorMessage += 'User menolak permintaan Geolocation.';
              break;
            case error.POSITION_UNAVAILABLE:
              errorMessage += 'Informasi lokasi tidak tersedia.';
              break;
            case error.TIMEOUT:
              errorMessage += 'Waktu permintaan untuk mendapatkan lokasi habis.';
              break;
            case error.UNKNOWN_ERROR:
              errorMessage += 'Terjadi kesalahan yang tidak diketahui.';
              break;
          }
          reject(errorMessage);
        },
        { 
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 0
        }
      );
    } else {
      reject('Geolocation tidak didukung oleh browser ini.');
    }
  });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
  // Tangani tombol absen
  document.querySelectorAll('.absen-btn').forEach(button => {
    button.addEventListener('click', function() {
      const tanggal = this.getAttribute('data-date');
      const idShift = this.getAttribute('data-shift');
      const isCheckedIn = this.getAttribute('data-checkin') === '1';
      const isCheckedOut = this.getAttribute('data-checkout') === '1';
      
      // Set data tanggal dan shift ke modal
      document.getElementById('inputTanggal').value = tanggal;
      document.getElementById('inputShift').value = idShift;
      
      // Atur status tombol berdasarkan status absensi
      const btnCheckIn = document.getElementById('btnCheckIn');
      const btnCheckOut = document.getElementById('btnCheckOut');
      
      // Nonaktifkan tombol Check-in jika sudah check-in
      if (isCheckedIn) {
        btnCheckIn.disabled = true;
        btnCheckIn.classList.add('btn-secondary');
        btnCheckIn.classList.remove('btn-success');
        btnCheckIn.textContent = 'Sudah Check-in';
      } else {
        btnCheckIn.disabled = false;
        btnCheckIn.classList.add('btn-success');
        btnCheckIn.classList.remove('btn-secondary');
        btnCheckIn.textContent = 'Check-in';
      }
      
      // Nonaktifkan tombol Check-out jika sudah check-out
      if (isCheckedOut) {
        btnCheckOut.disabled = true;
        btnCheckOut.classList.add('btn-secondary');
        btnCheckOut.classList.remove('btn-danger');
        btnCheckOut.textContent = 'Sudah Check-out';
      } else {
        btnCheckOut.disabled = false;
        btnCheckOut.classList.add('btn-danger');
        btnCheckOut.classList.remove('btn-secondary');
        btnCheckOut.textContent = 'Check-out';
      }
      
      // Nonaktifkan tombol Check-out jika belum check-in
      if (!isCheckedIn && !isCheckedOut) {
        btnCheckOut.disabled = true;
        btnCheckOut.classList.add('btn-secondary');
        btnCheckOut.classList.remove('btn-danger');
        btnCheckOut.title = 'Anda harus Check-in terlebih dahulu';
      } else if (isCheckedIn && !isCheckedOut) {
        btnCheckOut.disabled = false;
        btnCheckOut.classList.add('btn-danger');
        btnCheckOut.classList.remove('btn-secondary');
        btnCheckOut.title = '';
      }
      
      // Tampilkan modal
      $('#absenModal').modal('show');
    });
  });
  
  // Tangani tombol izin
  document.querySelectorAll('.izin-btn').forEach(button => {
    button.addEventListener('click', function() {
      const tanggal = this.getAttribute('data-date');
      
      // Set data tanggal ke modal
      document.getElementById('izinDate').value = tanggal;
      
      // Tampilkan modal
      $('#izinModal').modal('show');
    });
  });
  
  // Tangani perubahan jenis izin
  document.getElementById('izinType').addEventListener('change', function() {
    const sakitForm = document.getElementById('sakitForm');
    if (this.value === 'sakit') {
      sakitForm.style.display = 'block';
    } else {
      sakitForm.style.display = 'none';
    }
  });
  
  // Tangani tombol check-in di modal absensi
  document.getElementById('btnCheckIn').addEventListener('click', function() {
    // Periksa jika tombol dinonaktifkan, jangan lanjutkan
    if (this.disabled) {
      return;
    }
    
    const videoElement = document.getElementById('webcam');
    const canvasElement = document.getElementById('canvas');
    
    // Mulai webcam jika belum dimulai
    if (!videoElement.srcObject) {
      startWebcam(videoElement);
    }
    
    // Set jenis absensi ke check-in
    document.getElementById('jenisAbsensi').value = 'check_in';
    
    // Tampilkan tombol ambil foto
    document.getElementById('btnCapture').style.display = 'block';
  });
  
  // Tangani tombol check-out di modal absensi
  document.getElementById('btnCheckOut').addEventListener('click', function() {
    // Periksa jika tombol dinonaktifkan, jangan lanjutkan
    if (this.disabled) {
      return;
    }
    
    const videoElement = document.getElementById('webcam');
    const canvasElement = document.getElementById('canvas');
    
    // Mulai webcam jika belum dimulai
    if (!videoElement.srcObject) {
      startWebcam(videoElement);
    }
    
    // Set jenis absensi ke check-out
    document.getElementById('jenisAbsensi').value = 'check_out';
    
    // Tampilkan tombol ambil foto
    document.getElementById('btnCapture').style.display = 'block';
  });
  
  // Tangani tombol ambil foto
  document.getElementById('btnCapture').addEventListener('click', async function() {
    const videoElement = document.getElementById('webcam');
    const canvasElement = document.getElementById('canvas');
    const statusElement = document.getElementById('locationStatus');
    
    try {
      // Tampilkan loading
      this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengambil data...';
      this.disabled = true;
      
      // Cek ulang status tombol check-in dan check-out
      const jenisAbsensi = document.getElementById('jenisAbsensi').value;
      const btnCheckIn = document.getElementById('btnCheckIn');
      const btnCheckOut = document.getElementById('btnCheckOut');
      
      // Jika tombol yang seharusnya digunakan sudah dinonaktifkan, batalkan proses
      if ((jenisAbsensi === 'check_in' && btnCheckIn.disabled) || 
          (jenisAbsensi === 'check_out' && btnCheckOut.disabled)) {
        alert('Status absensi tidak valid. Silakan muat ulang halaman.');
        this.innerHTML = 'Ambil Foto';
        this.disabled = false;
        $('#absenModal').modal('hide');
        return;
      }
      
      // Ambil lokasi
      const locationData = await getLocation();
      
      // Tampilkan informasi lokasi
      statusElement.style.display = 'block';
      statusElement.innerHTML = `<small>Lokasi berhasil diambil</small>`;
      
      // Ambil gambar dari webcam
      const imageData = captureImage(videoElement, canvasElement);
      
      if (imageData) {
        // Simpan gambar ke form
        document.getElementById('inputFoto').value = imageData;
        
        // Tambahkan data lokasi ke form
        const form = document.getElementById('absenForm');
        
        // Tambahkan input lokasi jika belum ada
        if (!document.getElementById('inputLatitude')) {
          const latInput = document.createElement('input');
          latInput.type = 'hidden';
          latInput.id = 'inputLatitude';
          latInput.name = 'latitude';
          latInput.value = locationData.latitude;
          form.appendChild(latInput);
        } else {
          document.getElementById('inputLatitude').value = locationData.latitude;
        }
        
        if (!document.getElementById('inputLongitude')) {
          const longInput = document.createElement('input');
          longInput.type = 'hidden';
          longInput.id = 'inputLongitude';
          longInput.name = 'longitude';
          longInput.value = locationData.longitude;
          form.appendChild(longInput);
        } else {
          document.getElementById('inputLongitude').value = locationData.longitude;
        }
        
        // Tambahkan data foto ke form sebagai base64
        const fotoInput = document.createElement('input');
        fotoInput.type = 'hidden';
        fotoInput.name = 'foto_base64';
        fotoInput.value = imageData;
        form.appendChild(fotoInput);
        
        // Submit form
        this.innerHTML = 'Menyimpan...';
        form.submit();
      } else {
        alert('Gagal mengambil gambar. Pastikan kamera Anda sudah aktif.');
        this.innerHTML = 'Ambil Foto';
        this.disabled = false;
      }
    } catch (error) {
      alert('Terjadi kesalahan: ' + error);
      console.error('Error:', error);
      this.innerHTML = 'Ambil Foto';
      this.disabled = false;
    }
  });
}); 