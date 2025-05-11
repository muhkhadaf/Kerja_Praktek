# 🏢 Wakacao - Sistem Absensi Karyawan

![PHP](https://img.shields.io/badge/PHP-7.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-4.0+-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

Sistem manajemen absensi karyawan terintegrasi untuk Wakacao, memungkinkan pengelolaan absensi, jadwal shift, dan pengajuan izin/cuti dengan mudah dan efisien.

## ✨ Fitur Utama

- 🔐 **Autentikasi Multi-level** - Login untuk admin, supervisor, dan karyawan
- 📊 **Dashboard Interaktif** - Tampilan informasi penting secara visual
- ⏱️ **Absensi Digital** - Check-in dan check-out dengan validasi lokasi
- 📅 **Manajemen Jadwal** - Pengaturan jadwal shift karyawan
- 📝 **Pengajuan Izin/Cuti** - Sistem pengajuan dan persetujuan izin/cuti
- 📍 **Validasi Lokasi** - Verifikasi lokasi saat absensi menggunakan GPS
- 📸 **Bukti Foto** - Unggah foto saat absensi
- 📋 **Laporan Lengkap** - Riwayat shift, absensi, dan izin

## 🔧 Persyaratan Sistem

- PHP 7.0 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)
- Browser modern dengan dukungan JavaScript dan Geolocation API

## 📥 Cara Instalasi

1. Clone atau download repository ini
   ```bash
   git clone https://github.com/muhkhadaf/kerja_praktek.git
   ```

2. Letakkan di direktori web server Anda (htdocs untuk XAMPP)
   ```bash
   mv kerja_praktek /path/to/htdocs/
   ```

3. Buat database dengan nama `wakacao_db`
   ```sql
   CREATE DATABASE wakacao_db;
   ```

4. Import file `wakacao_db.sql` ke database yang telah dibuat
   ```bash
   mysql -u username -p wakacao_db < wakacao_db.sql
   ```

5. Sesuaikan konfigurasi database di file `database.php` jika diperlukan
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "wakacao_db";
   ```

6. Akses aplikasi melalui browser
   ```
   http://localhost/wakacao
   ```

## 👥 Akun Demo

Berikut adalah akun demo yang dapat digunakan untuk mengakses sistem:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | password |
| Karyawan | john@example.com | password |

## 📊 Struktur Database

Database terdiri dari beberapa tabel utama:

| Tabel | Deskripsi |
|-------|-----------|
| `users` | Data pengguna/karyawan dengan role (admin, supervisor, karyawan) |
| `shift` | Data jadwal shift (pagi, siang, malam) |
| `jadwal` | Jadwal shift karyawan per tanggal |
| `absensi` | Riwayat absensi karyawan (check-in, check-out, foto, lokasi) |
| `izin` | Pengajuan izin/sakit/cuti karyawan |
| `cuti_tahunan` | Data pengajuan dan sisa cuti tahunan karyawan |
| `locations` | Data lokasi outlet untuk validasi absensi |
| `outlets` | Data outlet/cabang Wakacao |

## 🚀 Pengembangan Lebih Lanjut

Beberapa fitur yang dapat dikembangkan:

- 📱 Aplikasi mobile untuk absensi
- 📧 Notifikasi via email/WhatsApp
- 🔄 Sistem approval bertingkat
- 📊 Laporan absensi dan analitik yang lebih detail
- 💰 Integrasi dengan sistem penggajian
- 🗓️ Penjadwalan otomatis berdasarkan kebutuhan outlet

## 📞 Kontak

Untuk informasi lebih lanjut, silakan hubungi:
- Email: admin@wakacao.com
- Website: www.wakacao.com

## 📝 Lisensi

© 2025 Wakacao. All rights reserved.
