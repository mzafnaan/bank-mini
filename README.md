# Bank Mini Sekolah - Core Banking & E-Teller System

> **Sistem Informasi Manajemen Bank Mini Sekolah Berbasis Web & RESTful API**  
> *Bagian dari Proyek Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak (RPL)*

---

## 1. Project Title & Overview

### Nama Aplikasi
**Bank Mini Sekolah (E-Teller & Core Banking System)**

### Deskripsi Singkat
Aplikasi sistem informasi perbankan mini sekolah yang dirancang untuk mengelola operasional keuangan dan tabungan siswa secara terpadu. Sistem ini menyediakan antarmuka web responsif untuk staf (Administrator, Teller, Supervisor) dan nasabah (siswa), serta dilengkapi layanan RESTful API untuk integrasi dengan aplikasi Mobile Banking berbasis Flutter.

### Latar Belakang
Di lingkungan sekolah kejuruan (khususnya SMK), keberadaan Bank Mini berfungsi strategis sebagai sarana edukasi literasi keuangan bagi siswa sekaligus wadah pembelajaran praktik perbankan dan akuntansi. Operasional manual menggunakan buku kas fisik atau lembar sebar (*spreadsheet*) memiliki kelemahan seperti risiko kesalahan manusia (*human error*), lambatnya rekonsiliasi kas harian loket, minimnya transparansi saldo dan riwayat bagi siswa, serta ketiadaan alur otorisasi berjenjang yang aman.

### Tujuan Aplikasi
1. **Otomatisasi Operasional Perbankan:** Menyediakan sistem terpadu untuk pencatatan transaksi setoran, penarikan, dan pembukuan kas yang akurat dan transparan.
2. **Penerapan Standar Tata Kelola & Otorisasi:** Mengimplementasikan alur otorisasi berjenjang, seperti verifikasi PIN nasabah pada penarikan tunai dan persetujuan supervisor pada penutupan kas loket harian (*daily closing*).
3. **Pemberdayaan Nasabah (Self-Service Portal):** Memberikan kemudahan bagi siswa untuk memantau saldo, memeriksa riwayat mutasi, mengunduh rekening koran digital, dan melakukan otorisasi transaksi secara mandiri.
4. **Integrasi Multi-Platform:** Menghubungkan sistem inti perbankan (*core banking*) berbasis web dengan aplikasi mobile nasabah melalui RESTful API.
5. **Dukungan Administrasi Kesiswaan:** Memfasilitasi kebutuhan administrasi sekolah, seperti import data siswa massal via Excel/CSV dan manajemen kenaikan kelas otomatis (*class promotion*).

---

## 2. Features

Sistem ini mencakup modul dan fitur fungsional yang telah diimplementasikan sesuai kebutuhan perbankan mini sekolah:

### A. Modul Operasional & Loket Kasir (Teller)
- **Pencarian & Identifikasi Nasabah:** Pencarian data nasabah dan rekening melalui Nomor Induk Siswa (NIS), Nomor Rekening, atau parameter QR Code.
- **Transaksi Setoran Tunai (*Cash Deposit*):** Pencatatan penerimaan uang tunai ke rekening nasabah secara langsung dengan mutasi kredit otomatis.
- **Transaksi Penarikan Tunai (*Cash Withdrawal*):** Penarikan tunai di loket dengan verifikasi otorisasi PIN nasabah (langsung di loket atau via konfirmasi perangkat nasabah), disertai validasi saldo mengendap minimum (Rp 10.000).
- **Cetak Bukti Transaksi (*Receipt Struk*):** Fasilitas cetak struk tanda bukti setoran dan penarikan tunai untuk nasabah.
- **Pembukuan Kas Loket Harian (*Cash Drawer*):** Pencatatan modal awal loket, rekapitulasi kas masuk/keluar harian, dan pengajuan laporan penutupan kas (*daily report*) kepada supervisor.

### B. Modul Supervisi & Pengawasan (Supervisor)
- **Monitoring Operasional Kasir:** Pemantauan transaksi perbankan harian yang dilakukan seluruh teller melalui dashboard supervisi.
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
- **Riwayat Mutasi Transaksi:** Akses riwayat mutasi transaksi dengan filter tanggal dan kategori transaksi.
- **Ekspor & Cetak Rekening Koran:** Pengunduhan laporan mutasi transaksi dalam format spreadsheet Excel (`.xlsx`) dan format cetak PDF.
- **Alur Otorisasi Penarikan Tunai:** Fitur persetujuan (*authorize*) atau penolakan (*reject*) permintaan penarikan tunai loket secara mandiri oleh nasabah menggunakan verifikasi PIN.
- **Pusat Keamanan Akun:** Penggantian password, pembaruan PIN 6-digit, reset PIN berbasis verifikasi password akun, dan registrasi email pemulihan terverifikasi (*signed URL*).

### E. Layanan RESTful API (Integrasi Mobile Banking)
- **Autentikasi Token:** Pengelolaan sesi login mobile menggunakan token Bearer berbasis **Laravel Sanctum**.
- **Endpoint Layanan Nasabah:** Mendukung pengambilan profil akun, statistik saldo, riwayat transaksi, ekspor data mutasi, otorisasi penarikan dana, dan pembaruan kredensial keamanan melalui aplikasi mobile.

### F. Keamanan & Proteksi Akses
- **Multi-Guard Authentication:** Pemisahan sesi otentikasi akun staf internal sekolah (`web`) dan akun nasabah siswa (`customer_web`).
- **Role-Based Access Control (RBAC):** Pembatasan hak akses halaman dan aksi spesifik berdasarkan wewenang peran pengguna.
- **Proteksi Brute-Force & Rate Limiting:** Pembatasan frekuensi percobaan login, otorisasi PIN transaksi, dan permintaan reset password.
- **Hashing Kredensial:** Password dan PIN transaksi disimpan dalam bentuk hash menggunakan Bcrypt.
- **Force Password Change:** Kebijakan wajib ubah kata sandi pada saat nasabah pertama kali login ke sistem.

---

## 3. User Roles

Sistem mengimplementasikan 4 peran pengguna dengan pembagian hak akses:

| Role | Tanggung Jawab & Hak Akses Utama |
| :--- | :--- |
| **Administrator** | Memiliki wewenang dalam mengelola akun pengguna staf (Admin, Teller, Supervisor), data nasabah, pembukaan rekening, import data massal via Excel/CSV, pengelolaan tahun ajaran, proses kenaikan kelas, serta audit jurnal pembukuan. |
| **Teller** | Bertugas di loket kasir sekolah untuk melayani setoran tunai dan penarikan tunai nasabah (dengan verifikasi PIN), identifikasi nasabah (NIS/No. Rekening/QR Code), mencetak struk bukti transaksi, serta menyusun laporan pembukuan kas loket harian. |
| **Supervisor** | Bertanggung jawab dalam pengawasan operasional, meninjau dan memberikan persetujuan (*approval*) atau penolakan (*rejection*) terhadap laporan penutupan kas harian teller, memonitor transaksi, dan mencetak laporan rekapitulasi transaksi. |
| **Nasabah (Siswa)** | Mengakses portal web atau aplikasi mobile banking untuk memeriksa saldo, menampilkan QR Code rekening, melihat mutasi, mengunduh rekening koran (PDF & Excel), menyetujui transaksi penarikan dana dengan PIN, serta mengelola keamanan akun. |

---

## 4. Tech Stack

Teknologi yang digunakan dalam pengembangan sistem ini:

- **Backend Framework:** [Laravel](https://laravel.com/) (PHP Web Framework)
- **Bahasa Pemrograman:** PHP >= 8.2 / 8.3
- **Database Management System:** MySQL 8.0+ / MariaDB 10.4+
- **Frontend & Antarmuka Web:** Laravel Blade Templates, [Tailwind CSS v4](https://tailwindcss.com/), JavaScript
- **Asset Bundler:** [Vite](https://vitejs.dev/)
- **API Engine:** RESTful API & [Laravel Sanctum](https://laravel.com/docs/sanctum)
- **Aplikasi Mobile (Client):** [Flutter](https://flutter.dev/) (Dart) dengan Dio dan Provider
- **Reporting & File Processing Engine:** Native OpenXML XLSX Service (via PHP `ZipArchive`), HTML-to-PDF Print Engine

---

## 5. Requirements

Sebelum menjalankan aplikasi, pastikan perangkat telah memenuhi persyaratan perangkat lunak berikut:

- **PHP:** Versi `>= 8.2` (Disarankan PHP `8.3` atau `8.4`)
  - Ekstensi PHP yang wajib aktif:
    - `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `libxml`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `zip`
- **Composer:** Versi `>= 2.2` (PHP Package Manager)
- **Database Server:** MySQL `>= 8.0` atau MariaDB `>= 10.4` (XAMPP / Laragon / Standalone)
- **Node.js & NPM:** Node.js `>= 18.x` dan NPM `>= 9.x`
- **Flutter SDK:** Versi `>= 3.8` *(opsional, hanya jika ingin menjalankan aplikasi mobile)*
- **Web Browser:** Google Chrome, Microsoft Edge, atau Mozilla Firefox versi terbaru

---

## 6. Panduan Instalasi Backend (Laravel)

Ikuti urutan instalasi berikut secara berurutan pada hasil clone repositori baru:

### 1. Clone Repository
```bash
git clone <URL_REPOSITORY>
cd bank-mini
```

### 2. Masuk ke Direktori Aplikasi Laravel
```bash
cd bank-mini
```

### 3. Instalasi Dependensi PHP
```bash
composer install
```

### 4. Salin File Konfigurasi Environment (`.env`)
- Di Linux / macOS / Git Bash:
  ```bash
  cp .env.example .env
  ```
- Di Windows PowerShell / CMD:
  ```powershell
  copy .env.example .env
  ```

### 5. Konfigurasi Lingkungan Database Lokal
Buka berkas `.env` dan sesuaikan pengaturan database lokal Anda:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_bank
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Pembuatan Database di MySQL
Buat database baru bernama `mini_bank` melalui terminal atau phpMyAdmin:
```sql
CREATE DATABASE mini_bank;
```

### 7. Generate Application Key
```bash
php artisan key:generate
```

### 8. Eksekusi Migrasi & Database Seeder
Jalankan migrasi skema tabel dan isi data awal untuk kebutuhan pengujian:
```bash
php artisan migrate --seed
```

### 9. Tautkan Direktori Penyimpanan Publik (Storage Link)
Buat symlink dari `storage/app/public` ke direktori `public/storage`:
```bash
php artisan storage:link
```

### 10. Instalasi Dependensi Frontend & Kompilasi Aset
```bash
npm install
npm run build
```

### 11. Jalankan Server Pengembangan Lokal
```bash
php artisan serve --port=8000
```
Aplikasi web backend dan dashboard kini dapat diakses melalui peramban web pada alamat:  
[http://localhost:8000](http://localhost:8000)

---

## 7. Environment Configuration

Sesuaikan parameter penting pada berkas `.env` sesuai dengan lingkungan server lokal Anda:

```dotenv
# Informasi Dasar Aplikasi
APP_NAME="Bank Mini Sekolah"
APP_ENV=local
APP_KEY=base64:... # Dihasilkan melalui php artisan key:generate
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
> Pada pengaturan bawaan, `MAIL_MAILER=log` **hanya mencatat isi email ke dalam berkas log** (`bank-mini/storage/logs/laravel.log`) dan **tidak mengirimkan email fisik sungguhan** ke alamat email tujuan.  
> Untuk menguji alur verifikasi email nasabah atau tautan reset password di lingkungan pengembangan lokal, buka berkas `bank-mini/storage/logs/laravel.log` dan salin tautan verifikasi (*verification URL*) atau token yang tercetak di dalamnya. Jika aplikasi dipindahkan ke lingkungan produksi (*production*), ubah driver menjadi `smtp` dan lengkapi kredensial mail server terkait.

---

## 8. Database Setup & Akun Pengujian

### A. Migrasi Standar
Untuk menyiapkan tabel dan data awal pertama kali pada database baru:
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

### C. Akun Pengujian Lokal (Development Seeder)
> [!WARNING]
> **Pemberitahuan Keamanan:**  
> Akun di bawah ini merupakan akun pengujian bawaan yang dihasilkan melalui database seeder (`UserSeeder` dan `CustomerAccountSeeder`) untuk kebutuhan verifikasi fitur di lingkungan pengembangan lokal (*local development*).  
> Demi keamanan, **kredensial asli (password dan PIN) tidak dicantumkan pada dokumentasi publik ini**. Pengembang atau penguji dapat merujuk langsung ke berkas seeder di `bank-mini/database/seeders/` untuk melihat konfigurasi bawaan lokal atau melakukan reset kata sandi melalui antarmuka / perintah Artisan. **Dilarang keras menggunakan data akun demo pada lingkungan produksi.**

| Role | Identifier / Username Bawaan | Sumber Data Seeder | Keterangan Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `UserSeeder.php` | Pengujian modul manajemen sistem & master data |
| **Teller** | `teller1` | `UserSeeder.php` | Pengujian loket transaksi setoran, penarikan, & laporan harian |
| **Supervisor** | `supervisor` | `UserSeeder.php` | Pengujian otorisasi laporan kas teller & monitoring transaksi |
| **Nasabah (Siswa)** | `17782` (NIS) | `CustomerAccountSeeder.php` | Pengujian portal web & mobile (No. Rekening: `RPL-001`) |

---

## 9. Mobile Application (Flutter)

Aplikasi mobile banking untuk nasabah siswa tersedia dalam bentuk aplikasi mobile berbasis framework **Flutter** yang berlokasi pada direktori:
```text
m_bank/
```

### Panduan Menjalankan Aplikasi Mobile

1. **Buka Terminal dan arahkan ke direktori proyek Flutter:**
   ```bash
   cd m_bank
   ```

2. **Unduh seluruh dependensi paket Flutter:**
   ```bash
   flutter pub get
   ```

3. **Konfigurasi URL API Backend:**
   Buka berkas `lib/core/constants/app_constants.dart` dan sesuaikan nilai getter `baseUrl`:
   - **Mode Live / Production:** `https://bankminiafnaan.my.id`
   - **Mode Android Emulator:** `http://10.0.2.2:8000`
   - **Mode Perangkat Fisik (via USB):** Jalankan perintah `adb reverse tcp:8000 tcp:8000` pada terminal, kemudian gunakan `http://127.0.0.1:8000`.
   - **Mode Web (Chrome / Edge):** `http://127.0.0.1:8000`

4. **Jalankan Aplikasi Mobile:**
   ```bash
   flutter run
   ```

---

## 10. Live Demo & Deployment

Aplikasi backend dan API saat ini telah dideploy pada server hosting:

- **Alamat URL Live (Produksi):** [https://bankminiafnaan.my.id](https://bankminiafnaan.my.id)

---

## 11. Project Structure

Berikut adalah struktur direktori lengkap repositori dan masing-masing sub-proyek:

### Struktur Root Repositori
```text
bank-mini/
├── bank-mini/              # Aplikasi Backend & Web Dashboard (Laravel)
├── m_bank/                 # Aplikasi Mobile Banking (Flutter)
└── README.md               # Dokumentasi utama proyek
```

### Struktur Direktori Backend (`bank-mini/`)
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
├── bootstrap/                     # Inisialisasi dan bootstrap framework
├── config/                        # Berkas konfigurasi aplikasi Laravel
├── database/
│   ├── migrations/                # Skema migrasi tabel basis data
│   └── seeders/                   # Data awal (seeder) pengguna dan konfigurasi sistem
├── public/                        # Entry point publik web (index.php) dan aset terkompilasi
├── resources/
│   ├── css/                       # Sumber file styling Tailwind CSS
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

### Struktur Direktori Mobile (`m_bank/`)
```text
m_bank/
├── android/                       # Konfigurasi dan build project Android
├── ios/                           # Konfigurasi dan build project iOS
├── assets/                        # Berkas aset gambar dan ikon aplikasi
├── lib/                           # Source code utama aplikasi Flutter
│   ├── core/                      # Modul inti (konstanta, network API client, tema, utils)
│   │   ├── constants/             # AppConstants (baseUrl, endpoint, storage keys)
│   │   ├── network/               # ApiClient (Dio HTTP interceptors, token handling)
│   │   ├── theme/                 # Tema warna dan tipografi Material Design
│   │   └── utils/                 # Formatters (rupiah, tanggal, status)
│   ├── models/                    # Model data objek Dart
│   ├── providers/                 # State management (Provider)
│   ├── screens/                   # Antarmuka layar aplikasi mobile (login, dashboard, mutasi, dll.)
│   ├── widgets/                   # Komponen widget UI reusable
│   └── main.dart                  # Titik awal eksekusi aplikasi Flutter
├── test/                          # Unit dan widget test aplikasi mobile
├── pubspec.yaml                   # Definisi paket dan dependensi Flutter
└── analysis_options.yaml          # Pengaturan linting dan analisis kode Dart
```

---

<p align="center">
  <b>Bank Mini Sekolah &copy; 2026 - Proyek Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak</b>
</p>
