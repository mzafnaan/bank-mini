# 🏦 Bank Mini Sekolah - Core Banking & E-Teller System

> **Sistem Informasi Manajemen Bank Mini Sekolah Berbasis Web & RESTful API**  
> *Proyek Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak (RPL)*

---

## 1. Project Title
**Nama Aplikasi:** Bank Mini Sekolah (E-Teller & Core Banking System)  
**Deskripsi Singkat:**  
Aplikasi sistem informasi perbankan mini sekolah yang dirancang untuk mengelola seluruh siklus operasional keuangan dan tabungan siswa secara terintegrasi. Sistem ini menyediakan antarmuka web responsif untuk staf (Administrator, Teller, Supervisor) dan nasabah (siswa), serta dilengkapi layanan RESTful API untuk integrasi dengan aplikasi Mobile Banking berbasis Flutter.

---

## 2. Overview
### Latar Belakang
Di lingkungan sekolah kejuruan (khususnya SMK), keberadaan Bank Mini memiliki fungsi strategis sebagai sarana edukasi literasi keuangan bagi siswa sekaligus wadah pembelajaran praktik riil perbankan dan akuntansi. Selama ini, operasional perbankan sekolah yang masih menggunakan pencatatan manual atau lembar sebar (*spreadsheet*) memiliki banyak kelemahan, seperti risiko kesalahan manusia (*human error*), lambatnya rekonsiliasi kas harian, minimnya transparansi saldo dan riwayat bagi siswa, serta tidak adanya alur otorisasi berjenjang yang aman.

### Tujuan Aplikasi
1. **Otomatisasi Operasional Perbankan:** Menyediakan sistem terpadu untuk pencatatan transaksi setoran, penarikan, dan pembukuan kas yang cepat, akurat, dan transparan.
2. **Penerapan Standar Tata Kelola & Otorisasi:** Mengimplementasikan alur otorisasi berjenjang yang sesuai dengan praktik industri, seperti verifikasi PIN untuk penarikan tunai dan persetujuan supervisor pada penutupan kas loket harian (*daily closing*).
3. **Pemberdayaan Nasabah (Self-Service Portal):** Memberikan kemudahan bagi siswa untuk memantau saldo, memeriksa riwayat mutasi transaksi, mengunduh rekening koran digital, dan melakukan transaksi secara mandiri.
4. **Integrasi Multi-Platform:** Menghubungkan sistem inti perbankan (*core banking*) berbasis web dengan aplikasi mobile nasabah melalui RESTful API berkeamanan tinggi.
5. **Dukungan Administrasi Kesiswaan:** Memfasilitasi kebutuhan administrasi sekolah, seperti import data siswa massal via Excel dan manajemen kenaikan kelas otomatis (*class promotion*).

---

## 3. Features
Sistem ini mencakup modul dan fitur fungsional yang telah diimplementasikan sesuai kebutuhan perbankan mini sekolah:

### A. Modul Operasional & Loket Kasir (Teller)
- **Pencarian & Identifikasi Nasabah:** Pencarian data nasabah dan rekening melalui Nomor Induk Siswa (NIS), Nomor Rekening, atau parameter QR Code.
- **Transaksi Setoran Tunai (*Cash Deposit*):** Penerimaan uang tunai ke rekening nasabah secara instan dengan pencatatan mutasi kredit otomatis.
- **Transaksi Penarikan Tunai (*Cash Withdrawal*):** Penarikan tunai di loket dengan verifikasi otorisasi PIN nasabah (langsung di loket atau via konfirmasi perangkat nasabah), disertai validasi saldo mengendap minimum (Rp 10.000).
- **Cetak Bukti Transaksi (*Receipt Struk*):** Fasilitas cetak struk tanda bukti setoran dan penarikan tunai untuk nasabah.
- **Pembukuan Kas Loket Harian (*Cash Drawer*):** Pencatatan modal awal loket, rekapitulasi kas masuk/keluar harian, dan pengajuan laporan penutupan kas (*daily report*) kepada supervisor.

### B. Modul Supervisi & Pengawasan (Supervisor)
- **Monitoring Operasional Real-Time:** Pemantauan arus pergerakan transaksi perbankan harian yang dilakukan seluruh teller.
- **Verifikasi & Approval Laporan Kas Teller:** Pemeriksaan, persetujuan (*approve*), atau penolakan (*reject*) laporan fisik penutupan kas harian loket teller.
- **Rekapitulasi & Laporan Transaksi:** Tinjauan rekap mutasi transaksi perbankan dan fasilitas cetak laporan rekap harian.
- **Pengawasan Jurnal Pembukuan:** Pemantauan catatan akuntansi jurnal umum untuk menjaga keselarasan pembukuan.

### C. Modul Administrasi & Manajemen Master Data (Administrator)
- **Manajemen Pengguna (*User Management*):** Pengelolaan akun staf (Admin, Teller, Supervisor), pengaktifan/penonaktifan status akun, dan fasilitas reset password.
- **Onboarding Nasabah Baru:** Registrasi siswa, pembukaan rekening tabungan otomatis, dan pembuatan akun portal nasabah.
- **Import Data Nasabah Massal:** Pengunggahan data siswa secara massal menggunakan file Excel (`.xlsx`) dan file `.csv` dengan template terstandarisasi.
- **Kontrol Status Rekening:** Pengelolaan status rekening tabungan (Aktif, Blokir, atau Nonaktif).
- **Manajemen Tahun Ajaran & Kenaikan Kelas (*Class Promotion*):** Pengaturan tahun ajaran aktif dan pemrosesan kenaikan kelas siswa secara berkala dengan pencatatan riwayat kelas.
- **Audit Jurnal Sistem:** Peninjauan log pembukuan akuntansi debit dan kredit untuk audit keuangan.

### D. Modul Portal Nasabah (Siswa / Customer)
- **Dashboard Ringkasan Tabungan:** Informasi saldo tabungan terkini, status rekening, dan kartu identitas digital dengan QR Code.
- **Riwayat Mutasi Transaksi:** Akses riwayat mutasi transaksi lengkap dengan filter tanggal dan kategori transaksi.
- **Ekspor & Cetak Rekening Koran:** Pengunduhan laporan mutasi transaksi dalam format spreadsheet Excel (`.xlsx`) dan format cetak PDF.
- **Otorisasi Penarikan Dana Mandiri:** Fitur persetujuan (*authorize*) atau penolakan (*reject*) permintaan penarikan tunai loket secara mandiri oleh nasabah.
- **Pusat Keamanan Akun:** Penggantian password, pembaruan PIN 6-digit, reset PIN berbasis verifikasi password, dan registrasi email pemulihan terverifikasi (*signed URL*).

### E. Layanan RESTful API (Integrasi Mobile Banking)
- **Autentikasi Aman:** Pengelolaan sesi login mobile menggunakan token Bearer berbasis **Laravel Sanctum**.
- **Endpoint Layanan Nasabah:** Mendukung pengambilan profil akun, statistik saldo, riwayat transaksi, ekspor data, persetujuan penarikan, dan pembaruan kredensial keamanan melalui aplikasi mobile.

### F. Keamanan & Proteksi Akses
- **Multi-Guard Authentication:** Pemisahan sesi otentikasi akun staf internal sekolah (`web`) dan akun nasabah siswa (`customer_web`).
- **Role-Based Access Control (RBAC):** Pembatasan akses halaman dan aksi spesifik berdasarkan wewenang peran pengguna.
- **Proteksi Brute-Force & Rate Limiting:** Pembatasan frekuensi percobaan login, otorisasi PIN transaksi, dan permintaan reset password.
- **Hashing Kredensial:** Seluruh kata sandi dan PIN transaksi dienkripsi satu arah menggunakan algoritma Bcrypt.
- **Force Password Change:** Kebijakan wajib ubah kata sandi pada saat nasabah pertama kali login ke sistem.

---

## 4. User Roles
Sistem mengimplementasikan 4 peran pengguna dengan pembagian hak akses:

| Role | Tanggung Jawab & Hak Akses Utama |
| :--- | :--- |
| **Administrator** | Memiliki wewenang tertinggi dalam mengelola data master pengguna (Admin, Teller, Supervisor), data nasabah, pembukaan rekening, import data kesiswaan massal, pengelolaan tahun ajaran, proses kenaikan kelas, serta audit jurnal pembukuan. |
| **Teller** | Bertugas di loket kasir sekolah untuk melayani setoran tunai dan penarikan tunai nasabah (dengan verifikasi PIN), identifikasi nasabah (NIS/No. Rekening/QR Code), mencetak struk bukti transaksi, serta menyusun laporan pembukuan kas loket harian. |
| **Supervisor** | Bertanggung jawab dalam pengawasan operasional, meninjau dan memberikan persetujuan (*approval*) atau penolakan (*rejection*) terhadap laporan penutupan kas harian teller, memonitor transaksi real-time, dan mencetak laporan rekapitulasi transaksi. |
| **Nasabah (Siswa)** | Memiliki akses mandiri melalui portal web dan mobile banking untuk mengecek saldo tabungan, menampilkan QR Code rekening, melihat mutasi, mengunduh rekening koran (PDF & Excel), menyetujui transaksi penarikan dana, serta mengelola PIN dan kata sandi akun. |

---

## 5. Tech Stack
Teknologi yang digunakan dalam pengembangan sistem ini:

- **Backend Framework:** [Laravel](https://laravel.com/) (PHP Web Framework)
- **Bahasa Pemrograman:** PHP 8.2+ / 8.3+
- **Database Management System:** MySQL 8.0+ / MariaDB 10.4+
- **Frontend & Antarmuka Web:** Laravel Blade Templates, [Tailwind CSS v4](https://tailwindcss.com/), JavaScript
- **Asset Bundler:** [Vite](https://vitejs.dev/)
- **API Engine:** RESTful API & [Laravel Sanctum](https://laravel.com/docs/sanctum)
- **Aplikasi Mobile (Client):** [Flutter](https://flutter.dev/) (Dart) untuk platform Android / iOS
- **Reporting & File Processing Engine:** Native OpenXML XLSX Service (via PHP `ZipArchive`), HTML-to-PDF Print Engine

---

## 6. Requirements
Sebelum menjalankan aplikasi, pastikan perangkat telah memenuhi persyaratan perangkat lunak berikut:

- **PHP:** Versi `>= 8.2` (Disarankan PHP `8.3` atau `8.4`)
  - Ekstensi PHP yang wajib aktif:
    - `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `libxml`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `zip`
- **Composer:** Versi `>= 2.2` (PHP Package Manager)
- **Database Server:** MySQL `>= 8.0` atau MariaDB `>= 10.4` (XAMPP / Laragon / Standalone)
- **Node.js & NPM:** Node.js `>= 18.x` dan NPM `>= 9.x`
- **Flutter SDK:** Versi `>= 3.x` *(opsional, hanya jika ingin menjalankan aplikasi mobile)*
- **Web Browser:** Google Chrome, Microsoft Edge, atau Mozilla Firefox versi terbaru

---

## 7. Installation
Ikuti urutan instalasi berikut secara berurutan agar seluruh dependensi, konfigurasi, database, penyimpanan publik, dan kompilasi antarmuka terpasang dengan benar:

### 1. Masuk ke Direktori Proyek
```bash
cd bank-mini/bank-mini
```

### 2. Instalasi Dependensi PHP (Composer)
```bash
composer install
```

### 3. Salin File Konfigurasi Environment (`.env`)
- Di Linux / macOS / Git Bash:
  ```bash
  cp .env.example .env
  ```
- Di Windows PowerShell / CMD:
  ```powershell
  copy .env.example .env
  ```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Konfigurasi Lingkungan (`.env`)
Buka file `.env` menggunakan teks editor dan sesuaikan kredensial database lokal Anda (lihat panduan pada [Bagian 8](#8-environment-configuration)).

### 6. Pembuatan Database di MySQL
Buat database baru bernama `mini_bank` melalui terminal atau phpMyAdmin:
```sql
CREATE DATABASE mini_bank;
```

### 7. Eksekusi Migrasi & Database Seeder
Jalankan migrasi skema tabel dan isi data awal untuk kebutuhan pengujian:
```bash
php artisan migrate --seed
```

### 8. Tautkan Direktori Penyimpanan Publik (Storage Link)
Buat symlink dari `storage/app/public` ke direktori `public/storage`:
```bash
php artisan storage:link
```

### 9. Instalasi Dependensi Frontend & Kompilasi Aset
```bash
npm install
npm run build
```

### 10. Jalankan Server Pengembangan Lokal
```bash
php artisan serve --port=8000
```
Aplikasi web backend dan dashboard kini dapat diakses melalui peramban web pada alamat:  
[http://localhost:8000](http://localhost:8000)

---

## 8. Environment Configuration
Sesuaikan parameter penting pada file konfigurasi `.env` sesuai dengan lingkungan server lokal Anda:

```dotenv
# Informasi Dasar Aplikasi
APP_NAME="Bank Mini Sekolah"
APP_ENV=local
APP_KEY=base64:... # Dihasilkan oleh perintah php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000

# Konfigurasi Koneksi Database MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_bank
DB_USERNAME=root
DB_PASSWORD=

# Konfigurasi Session, Cache, & Queue
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

# Konfigurasi Pengiriman Email
MAIL_MAILER=log
```

> [!NOTE]
> **Penting Mengenai `MAIL_MAILER=log`:**  
> Pada pengaturan bawaan, `MAIL_MAILER=log` **hanya mencatat isi email ke dalam berkas log** (`storage/logs/laravel.log`) dan **tidak mengirimkan email fisik sungguhan** ke alamat email tujuan.  
> Untuk menguji alur verifikasi email nasabah atau tautan reset password di lingkungan pengembangan lokal, buka file `storage/logs/laravel.log` dan salin tautan verifikasi (*verification URL*) atau token yang tercetak di dalamnya. Jika aplikasi dipindahkan ke lingkungan produksi (*production*), ubah driver menjadi `smtp` dan lengkapi kredensial mail server terkait.

---

## 9. Database Setup
Panduan penyiapan database, migrasi tabel, dan kredensial akun uji coba:

### A. Migrasi Standar
Untuk menyiapkan tabel dan data awal pertama kali:
```bash
php artisan migrate --seed
```

### B. Reset Database Development
> [!CAUTION]
> **PERINGATAN KERAS:**  
> Perintah `php artisan migrate:fresh --seed` akan **MENGHAPUS (DROP) SELURUH TABEL DAN DATA** yang ada di dalam database sebelum membuat ulang struktur dan menjalankan seeder. Perintah ini **HANYA BOLEH** digunakan pada lingkungan pengembangan (*development / testing*). **DILARANG MENJALANKAN** perintah ini pada lingkungan produksi (*production*) karena seluruh transaksi dan data nasabah riil akan hilang secara permanen!

```bash
# HANYA untuk lingkungan pengembangan lokal:
php artisan migrate:fresh --seed
```

### C. Akun Demonstrasi & Pengujian (Development Only)
> [!WARNING]
> **PERINGATAN KEAMANAN:**  
> Kredensial di bawah ini adalah data akun bawaan yang dihasilkan oleh *database seeder* khusus untuk keperluan demonstrasi dan pengujian lokal. **JANGAN PERNAH** menggunakan akun dan kata sandi bawaan ini pada server lingkungan produksi (*production*). Seluruh akun produksi wajib dibuat baru dengan kata sandi yang kuat dan aman.

| Role | Username / NIS | Kata Sandi (Password) | Keterangan / PIN |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin1234567` | Akun demo pengujian modul Administrator |
| **Teller** | `teller1` | `teller1234` | Akun demo pengujian loket kasir & transaksi teller |
| **Supervisor** | `supervisor` | `supervisor1234567` | Akun demo pengujian approval laporan harian teller |
| **Nasabah (Siswa)** | `17782` (NIS) | `password` *(atau `Bk17782!`)* | No. Rekening: `RPL-001`, PIN Transaksi: `123456` |

---

## 10. Mobile Application (Flutter)
Aplikasi mobile banking untuk nasabah siswa tersedia dalam bentuk aplikasi mobile berbasis framework **Flutter** yang berlokasi pada direktori:
```text
m_bank/m_bank/
```

### Panduan Menjalankan Aplikasi Mobile
1. **Buka Terminal dan arahkan ke direktori proyek Flutter:**
   ```bash
   # Dari direktori root workspace:
   cd m_bank/m_bank
   ```

2. **Unduh seluruh dependensi paket Flutter:**
   ```bash
   flutter pub get
   ```

3. **Konfigurasi URL API Backend:**
   Buka file `lib/core/constants/app_constants.dart` dan sesuaikan nilai `baseUrl`:
   - **Mode Live / Production:** `https://bankminiafnaan.my.id`
   - **Mode Android Emulator:** `http://10.0.2.2:8000`
   - **Mode Perangkat Fisik (via USB):** Jalankan perintah `adb reverse tcp:8000 tcp:8000` pada terminal, kemudian gunakan `http://127.0.0.1:8000`.

4. **Jalankan Aplikasi Mobile:**
   ```bash
   flutter run
   ```

---

## 11. Live Demo & Deployment
Aplikasi backend dan API saat ini telah dideploy pada server hosting:

- **Alamat URL Live (Produksi):** [https://bankminiafnaan.my.id](https://bankminiafnaan.my.id)
- <!-- TODO: Tambahkan tautan dokumentasi API Swagger/Postman atau demo hosting alternatif jika tersedia -->

---

## 12. Screenshots
Dokumentasi visual antarmuka sistem diorganisir di dalam folder `docs/screenshots/`:

| Tampilan Antarmuka | Deskripsi Halaman | Jalur Berkas (*File Path*) |
| :--- | :--- | :--- |
| **Halaman Login** | Portal autentikasi multi-guard (Staf & Nasabah) | `docs/screenshots/01-login.png` |
| **Dashboard Administrator** | Statistik perbankan, pengguna, dan mutasi | `docs/screenshots/02-dashboard-admin.png` |
| **Transaksi Loket Teller** | Formulir setoran & penarikan tunai ber-PIN | `docs/screenshots/03-teller-transaction.png` |
| **Supervisi Kasir** | Peninjauan dan approval laporan kas harian | `docs/screenshots/04-supervisor-approval.png` |
| **Portal Web Nasabah** | Ringkasan saldo, QR code rekening, dan riwayat | `docs/screenshots/05-customer-portal.png` |
| **Mobile Banking Nasabah** | Antarmuka aplikasi Flutter nasabah pada smartphone | `docs/screenshots/06-mobile-banking.png` |

> *Catatan: Simpan file gambar tangkapan layar dalam format PNG atau JPG ke dalam folder `docs/screenshots/` sesuai dengan nama berkas di atas.*  
> <!-- TODO: Masukkan file screenshot asli hasil uji coba sistem ke dalam folder docs/screenshots/ -->

---

## 13. Project Structure
Berikut adalah gambaran struktur direktori penting dalam proyek backend ini:

```text
bank-mini/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/               # Controller RESTful API (Sanctum) untuk aplikasi mobile
│   │   │   │   ├── AuthApiController.php
│   │   │   │   └── NasabahApiController.php
│   │   │   └── Web/               # Controller Web Dashboard per peran
│   │   │       ├── AdminController.php
│   │   │       ├── AuthController.php
│   │   │       ├── CustomerController.php
│   │   │       ├── PasswordResetController.php
│   │   │       ├── SupervisorController.php
│   │   │       └── TellerController.php
│   │   ├── Middleware/            # Middleware sistem (RoleMiddleware, ForcePasswordChange, dll.)
│   │   └── Requests/              # Validasi form input per modul (Admin, Teller, Customer, Auth)
│   ├── Models/                    # Model Eloquent (User, Customer, BankAccount, Transaction, dll.)
│   ├── Notifications/             # Layanan notifikasi (Reset Password, Verifikasi Email Nasabah)
│   └── Services/                  # Business Logic Layer (Teller, Customer, Import, Promotion, dll.)
│       ├── AcademicYearService.php
│       ├── AuthService.php
│       ├── BankAccountService.php
│       ├── ClassPromotionService.php
│       ├── CustomerImportService.php
│       ├── CustomerService.php
│       ├── CustomerWithdrawalService.php
│       ├── JournalService.php
│       ├── SimpleXlsxExportService.php
│       ├── SupervisorService.php
│       ├── TellerService.php
│       └── UserService.php
├── bootstrap/                     # Inisialisasi dan konfigurasi bootstrap Laravel
├── config/                        # Pengaturan konfigurasi aplikasi
├── database/
│   ├── migrations/                # Skema migrasi tabel basis data
│   └── seeders/                   # Data awal (seeder) pengguna dan konfigurasi sistem
├── public/                        # Entry point publik (index.php) dan aset terkompilasi
├── resources/
│   ├── css/                       # Sumber file Tailwind CSS
│   └── views/                     # Template antarmuka Blade untuk seluruh role
├── routes/
│   ├── api.php                    # Rute endpoint RESTful API (v1)
│   └── web.php                    # Rute antarmuka Web Dashboard
├── storage/                       # Penyimpanan berkas aplikasi, log, dan cache
├── tests/                         # Pengujian otomatis (Unit & Feature Test)
├── .env.example                   # Template variabel lingkungan
├── composer.json                  # Konfigurasi dependensi paket PHP
├── package.json                   # Konfigurasi dependensi JavaScript & Vite
└── vite.config.js                 # Konfigurasi bundler Vite
```

---

<p align="center">
  <b>Bank Mini Sekolah &copy; 2026 - Proyek Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak</b>
</p>
