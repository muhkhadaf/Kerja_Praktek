# Wakacao - Sistem Absensi Karyawan

Sistem ini digunakan untuk mengelola absensi karyawan, jadwal shift, dan pengajuan izin/cuti di Wakacao.

## Fitur

- Login dan autentikasi users
- Dashboard karyawan
- Absensi check-in dan check-out
- Pengajuan izin/libur/cuti
- Riwayat shift, absensi, dan izin

## Persyaratan Sistem

- PHP 7.0 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)

## Cara Instalasi

1. Clone atau download repository ini
2. Letakkan di direktori web server Anda (htdocs untuk XAMPP)
3. Buat database dengan nama `wakacao_db`
4. Import file `wakacao_db.sql` ke database yang telah dibuat
5. Sesuaikan konfigurasi database di file `database.php` jika diperlukan
6. Akses aplikasi melalui browser (contoh: http://localhost/wakacao)

## Akun Demo

Berikut adalah akun demo yang dapat digunakan:

### Karyawan
- Email: john@example.com
- Password: password

### Admin
- Email: admin@example.com
- Password: password

## Struktur Database

Database terdiri dari beberapa tabel utama:

1. `users` - Data pengguna/karyawan
2. `shift` - Data shift kerja
3. `jadwal` - Jadwal shift karyawan
4. `absensi` - Riwayat absensi karyawan
5. `izin` - Riwayat pengajuan izin/cuti

## Pengembangan Lebih Lanjut yang bisa dikembangkan

Beberapa fitur yang dapat dikembangkan:
- Notifikasi via email
- Sistem approval bertingkat
- Laporan absensi bulanan
- Integrasi dengan penggajian
