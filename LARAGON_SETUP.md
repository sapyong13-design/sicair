# 🚀 SI HEALING - Panduan Setup Lengkap di Laragon

Panduan lengkap ini akan membimbing Anda menjalankan aplikasi SI Healing (Sistem Informasi Leave Management) di Laragon dari awal hingga akhir.

---

## 📋 Prasyarat

Sebelum memulai, pastikan Anda sudah memiliki:

✅ **Laragon** (download dari https://laragon.org/)
✅ **Visual Studio Code atau editor favorit Anda**
✅ **Git** (untuk clone project)
✅ **Koneksi internet** (untuk download dependencies)

### Versi yang digunakan:
- **PHP**: 8.2+
- **MySQL**: 5.7+
- **Node.js**: 18+
- **Composer**: 2.x
- **Laravel**: 12

---

## ✅ LANGKAH 1: Setup Laragon

### 1.1 Download dan Install Laragon

1. Buka https://laragon.org/
2. Download Laragon (Full atau Lite)
3. Jalankan installer
4. Pilih lokasi instalasi (default: `C:\laragon`)
5. Klik **Install**

### 1.2 Konfigurasi Awal Laragon

Setelah instalasi:

1. **Buka Laragon** dari Start Menu
2. Klik tombol **Start All** (tombol hijau besar)
   - Tunggu sampai semua service berjalan (MySQL, Apache, NodeJS)
   - Status akan berubah menjadi hijau dan menunjukkan "All services running"

3. **Verifikasi instalasi:**
   - Buka browser → `http://localhost`
   - Anda akan melihat dashboard Laragon

✅ Laragon berhasil diinstall!

---

## 🔧 LANGKAH 2: Setup Database MySQL

### 2.1 Buka MySQL Console

1. Di Laragon, klik **Menu** (≡)
2. Pilih **MySQL** → **MySQL Console** (atau double-click MySQL di Laragon)
3. Username: `root`
4. Password: (kosongkan, langsung Enter)

### 2.2 Buat Database

Ketik perintah berikut di MySQL Console:

```sql
CREATE DATABASE sihealing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

✅ Database `sihealing` berhasil dibuat!

---

## 📁 LANGKAH 3: Clone Project SI Healing

### 3.1 Navigasi ke Folder Laragon

1. Buka File Explorer
2. Navigasi ke: `C:\laragon\www`

### 3.2 Clone Project

1. Buka **Command Prompt** atau **PowerShell** di folder ini
   - Klik di address bar, ketik `cmd`, tekan Enter

2. Ketik perintah:
```bash
git clone https://github.com/sapyong13-design/sihealing.git
cd sihealing
```

3. Tunggu proses clone selesai (~2-3 menit)

### 3.3 Verifikasi Folder

Struktur folder seharusnya seperti ini:
```
C:\laragon\www\
├── sihealing/          ← Project baru
│   ├── app/
│   ├── routes/
│   ├── database/
│   ├── .env
│   ├── artisan
│   └── ... (file Laravel lainnya)
```

✅ Project berhasil di-clone!

---

## 💾 LANGKAH 4: Install Dependencies

### 4.1 Install PHP Dependencies (Composer)

1. Buka Command Prompt di folder `C:\laragon\www\sihealing`
2. Ketik:
```bash
composer install
```

**Output yang diharapkan:**
```
Loading composer repositories with package information
Updating dependencies
...
Installing dependencies from lock file
...
✓ All dependencies installed
```

⏱️ Waktu: 2-5 menit (tergantung kecepatan internet)

### 4.2 Generate App Key

```bash
php artisan key:generate
```

**Output:**
```
Application key [base64:xxxxxxx...] set successfully.
```

✅ PHP dependencies berhasil diinstall!

---

## 🎯 LANGKAH 5: Setup Environment File

### 5.1 Verifikasi File `.env`

File `.env` sudah ada di project. Mari kita periksa dan update konfigurasinya:

1. Buka file `.env` dengan editor favorit Anda
2. Verifikasi konfigurasi database:

```env
# Pastikan bagian DATABASE seperti ini:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sihealing
DB_USERNAME=root
DB_PASSWORD=

# Email configuration (untuk development)
MAIL_MAILER=log

# Queue (untuk email notifications)
QUEUE_CONNECTION=database

# Session (menggunakan database)
SESSION_DRIVER=database

# Cache (menggunakan database)
CACHE_STORE=database
```

### 5.2 Simpan File

Jika ada perubahan, simpan file `.env`

✅ Environment configuration berhasil!

---

## 📊 LANGKAH 6: Database Migration & Seeding

### 6.1 Jalankan Migration

1. Buka Command Prompt di folder `sihealing`
2. Ketik:
```bash
php artisan migrate
```

**Output yang diharapkan:**
```
Migration table created successfully.
Migrating: 2024_01_01_000001_create_users_table
...
✓ Migrating: (file migration terakhir)
Database migrations completed successfully.
```

### 6.2 Jalankan Database Seed (Optional - untuk data dummy)

Untuk testing, Anda bisa menambahkan data dummy:

```bash
php artisan db:seed
```

**Output:**
```
Database seeding completed successfully.
```

Ini akan membuat:
- 5 user dummy
- Leave requests contoh
- Admin account

✅ Database migration & seeding berhasil!

---

## 🌐 LANGKAH 7: Setup Virtual Host (Opsional tapi Recommended)

Untuk akses yang lebih mudah, setup virtual host:

### 7.1 Edit Hosts File

1. Buka File Explorer as Administrator
2. Navigasi ke: `C:\Windows\System32\drivers\etc`
3. Buka file `hosts` dengan Notepad (as Administrator)
4. Tambahkan di akhir file:
```
127.0.0.1 sihealing.local
```
5. Simpan file

### 7.2 Konfigurasi Laragon Virtual Host

1. Buka Laragon
2. Klik **Menu** (≡)
3. Pilih **Apache** → **httpd.conf**
4. Cari bagian `<VirtualHost>` dan verifikasi sudah ada entry untuk sihealing
5. Tutup dan restart Apache

### 7.3 Test Virtual Host

Buka browser → `http://sihealing.local`

✅ Virtual Host berhasil! (Opsional - jika tidak perlu, skip ke langkah berikutnya)

---

## 🏃 LANGKAH 8: Menjalankan Aplikasi

### 8.1 Start Laragon Services

1. Buka Laragon
2. Klik **Start All** (jika belum aktif)
   - Apache (server web)
   - MySQL (database)
   - Node.js (assets)

Pastikan semua berwarna hijau ✅

### 8.2 Jalankan Laravel Development Server (Opsional)

Jika ingin menggunakan Laravel's built-in server:

```bash
php artisan serve
```

**Output:**
```
Starting Laravel development server: http://127.0.0.1:8000
```

Tapi karena Laragon sudah include Apache, Anda bisa langsung akses via:
- `http://localhost/sihealing`
- atau `http://sihealing.local` (jika setup virtual host)

### 8.3 Build Frontend Assets (Opsional)

Jika ada asset yang belum di-build:

```bash
npm install
npm run dev
```

✅ Aplikasi siap dijalankan!

---

## 🚀 LANGKAH 9: Akses Aplikasi

### Default Akses URL

Pilih salah satu sesuai konfigurasi Anda:

| Metode | URL | Keterangan |
|--------|-----|-----------|
| **Laragon (Recommended)** | `http://localhost/sihealing` | Menggunakan Apache Laragon |
| **Virtual Host** | `http://sihealing.local` | Jika setup virtual host |
| **Artisan Serve** | `http://127.0.0.1:8000` | Jika jalankan `php artisan serve` |

### Akun Default (Jika Run Seeder)

```
Admin Account:
Email: admin@example.com
Password: password

Employee Account:
Email: employee@example.com
Password: password
```

✅ Aplikasi siap digunakan!

---

## ⚡ LANGKAH 10: Background Services (Queue & Scheduler)

Untuk fitur email notifications dan automated tasks bekerja dengan baik:

### 10.1 Jalankan Queue Worker

Buka **Command Prompt** baru (di folder sihealing):

```bash
php artisan queue:listen
```

**Output:**
```
Processing jobs from the [default] queue.
Listening for jobs...
```

Biarkan window ini tetap terbuka. Terminal ini akan memproses:
- Email notifications
- Background jobs

### 10.2 Setup Scheduler (Optional - untuk production)

Untuk production, tambahkan cron job. Tapi untuk development di Laragon, Anda bisa manually jalankan:

```bash
php artisan schedule:run
```

---

## 📝 Testing Checklist

Setelah setup selesai, test fitur-fitur berikut:

### ✅ Basic Testing

- [ ] Buka aplikasi di browser
- [ ] Login dengan akun dummy (jika seeded)
- [ ] Lihat dashboard
- [ ] Navigasi ke menu Leave Request
- [ ] Buat leave request baru

### ✅ Employee Features

- [ ] Buat leave request (Cuti, Izin, Sakit)
- [ ] Lihat history request
- [ ] Request amendment (ubah tanggal)
- [ ] Export PDF dari leave detail
- [ ] Appeal jika leave di-reject

### ✅ Admin Features

- [ ] Login sebagai admin
- [ ] Lihat pending requests
- [ ] Approve/Reject leave requests
- [ ] Buat balance adjustment
- [ ] Review appeals
- [ ] Lihat audit logs: `/admin/audit-logs`

### ✅ Automation Features

- [ ] Email notifications (cek di `storage/logs/laravel.log`)
- [ ] PDF generation (export dari leave detail)
- [ ] Notifications tampil di dashboard

---

## 🛠️ Troubleshooting

### Problem 1: "Access Denied" saat buka aplikasi

**Solusi:**
```bash
# Set permissions di Windows Command Prompt (as Administrator)
icacls C:\laragon\www\sihealing\storage /grant Everyone:F /t
icacls C:\laragon\www\sihealing\bootstrap\cache /grant Everyone:F /t
```

### Problem 2: Database connection error

**Solusi:**
1. Verifikasi MySQL running di Laragon (warna hijau)
2. Check `.env` file:
   - DB_HOST = `127.0.0.1`
   - DB_USERNAME = `root`
   - DB_PASSWORD = (kosong)

### Problem 3: Artisan commands tidak jalan

**Solusi:**
```bash
# Restart Laragon:
# 1. Laragon → Menu → Stop All
# 2. Tunggu 3 detik
# 3. Laragon → Start All
```

### Problem 4: Migration gagal

**Solusi:**
```bash
# Rollback dan jalankan ulang
php artisan migrate:rollback
php artisan migrate
```

### Problem 5: Email tidak terkirim

**Solusi:**
- Cek file `storage/logs/laravel.log`
- Email di-configure menggunakan `MAIL_MAILER=log` untuk development
- Lihat log file untuk melihat email apa yang di-send

---

## 📊 File Structure Penting

Setelah setup, Anda akan punya struktur seperti ini:

```
C:\laragon\www\sihealing\
├── app/
│   ├── Models/              ← Database models
│   ├── Http/Controllers/    ← Logic aplikasi
│   └── Services/            ← Business logic
├── database/
│   ├── migrations/          ← Table definitions
│   └── seeders/             ← Dummy data
├── routes/
│   ├── web.php              ← URL routes
│   └── api.php              ← API endpoints
├── resources/
│   ├── views/               ← HTML templates
│   └── js/                  ← JavaScript/Vue
├── storage/
│   ├── logs/                ← Application logs
│   └── app/                 ← Generated files (PDF, docs)
├── .env                     ← Configuration
├── composer.json            ← PHP dependencies
├── package.json             ← NPM dependencies
└── artisan                  ← CLI tool
```

---

## 🔐 Security Notes

### Development Only:
- `.env` file berisi credentials - jangan di-share atau di-push ke GitHub
- Password di-hash dengan bcrypt
- CSRF protection aktif di semua forms

### Best Practices:
- Jangan expose `.env` file
- Backup database secara regular
- Test di staging sebelum production
- Monitor audit logs untuk suspicious activity

---

## 📚 Useful Artisan Commands

```bash
# Database
php artisan migrate              # Run migrations
php artisan migrate:rollback     # Undo migrations
php artisan db:seed              # Add dummy data

# Cache & Config
php artisan cache:clear          # Clear cache
php artisan config:cache         # Cache config
php artisan view:clear           # Clear view cache

# Queue & Jobs
php artisan queue:listen         # Start queue worker
php artisan queue:failed          # View failed jobs
php artisan queue:retry all      # Retry failed jobs

# Debugging
php artisan tinker              # Interactive shell
php artisan route:list          # List all routes
php artisan make:model Model    # Create new model

# Maintenance
php artisan down                 # Put app in maintenance mode
php artisan up                   # Bring app back up
```

---

## ✨ Next Steps

Setelah berhasil setup:

1. **Explore Dashboard** - Lihat analytics dan summary
2. **Create Test Data** - Buat beberapa leave requests untuk testing
3. **Test Workflows** - Test approval process end-to-end
4. **Check Logs** - Monitor `storage/logs/laravel.log` untuk debugging
5. **Review Code** - Explore folder `app/` untuk memahami struktur

---

## 📞 Bantuan Lebih Lanjut

### Dokumentasi:
- **Setup Guide**: `SETUP_GUIDE.md`
- **README**: `README.md`
- **Code Comments**: Check di folder `app/`

### Laragon Resources:
- Official Website: https://laragon.org/
- Documentation: https://laragon.org/docs/

### Laravel Resources:
- Laravel Docs: https://laravel.com/docs
- Laravel API: https://laravel.com/api

---

## 🎉 Selesai!

Aplikasi SI Healing Anda sekarang siap dijalankan di Laragon!

Jika ada pertanyaan atau masalah, silakan cek:
1. Troubleshooting section di atas
2. File logs: `storage/logs/laravel.log`
3. Laravel documentation

Happy coding! 🚀

---

**Last Updated:** February 2026
**Status:** Production Ready ✅
**Platform:** Laragon Windows
