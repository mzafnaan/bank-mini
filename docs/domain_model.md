# Domain Model

| Attribute | Value |
|-----------|-------|
| Project | E-Teller Bank Mini Sekolah |
| Document | Domain Model |
| Version | 1.0 |
| Status | Draft |

---

# 1. Purpose

Dokumen ini mendefinisikan domain model dari Sistem Informasi E-Teller Bank Mini Sekolah sebagai representasi objek-objek bisnis utama beserta hubungan antar objek di dalam sistem.

Domain Model digunakan sebagai acuan dalam perancangan database (ERD), implementasi API, dan pengembangan aplikasi web maupun mobile.

Dokumen ini tidak membahas implementasi database, tipe data, maupun teknologi yang digunakan.

---

# 2. Domain Overview

Berdasarkan kebutuhan sistem dan aturan bisnis yang telah ditetapkan, domain utama pada Sistem Informasi E-Teller Bank Mini Sekolah terdiri dari delapan entitas utama, yaitu:

- User
- Customer
- Customer Account
- Bank Account
- Transaction
- Withdrawal Request
- Journal Entry
- Daily Report

Setiap entitas memiliki tanggung jawab yang berbeda dan saling berhubungan untuk mendukung proses operasional Bank Mini Sekolah.

---

# 3. Domain Entities

## 3.1 User

### Description

Merepresentasikan pengguna internal sistem yang memiliki hak akses operasional.

### Responsibilities

- Melakukan autentikasi ke sistem.
- Mengakses fitur sesuai role.
- Melaksanakan tugas operasional sesuai hak akses.

### Roles

- Administrator
- Teller
- Supervisor

---

## 3.2 Customer

### Description

Merepresentasikan data identitas nasabah Bank Mini Sekolah.

### Responsibilities

- Menyimpan informasi identitas nasabah.
- Menjadi pemilik rekening.
- Menjadi pemilik akun login nasabah.

---

## 3.3 Customer Account

### Description

Merepresentasikan akun login milik nasabah.

### Responsibilities

- Melakukan autentikasi nasabah.
- Menyimpan username.
- Menyimpan password.
- Menyimpan PIN transaksi.
- Menyimpan status first login.

---

## 3.4 Bank Account

### Description

Merepresentasikan rekening tabungan milik nasabah.

### Responsibilities

- Menyimpan nomor rekening.
- Menyimpan saldo.
- Menyimpan QR Code rekening.
- Menjadi sumber seluruh transaksi keuangan.

---

## 3.5 Transaction

### Description

Merepresentasikan transaksi keuangan yang terjadi pada rekening nasabah.

### Responsibilities

- Mencatat transaksi setoran.
- Mencatat transaksi penarikan.
- Memperbarui saldo rekening.
- Menjadi sumber pembentukan jurnal akuntansi.
- Menjadi sumber pencetakan bukti transaksi.

### Transaction Types

- Deposit
- Withdrawal

---

## 3.6 Withdrawal Request

### Description

Merepresentasikan permintaan penarikan tunai sebelum mendapatkan otorisasi PIN dari nasabah.

### Responsibilities

- Menyimpan permintaan penarikan.
- Menunggu otorisasi PIN dari nasabah.
- Menentukan status permintaan penarikan.
- Menjadi dasar pembentukan transaksi penarikan.

### Status

- Waiting PIN
- Approved
- Rejected
- Expired

---

## 3.7 Journal Entry

### Description

Merepresentasikan pencatatan jurnal akuntansi yang dihasilkan secara otomatis dari setiap transaksi.

### Responsibilities

- Menyimpan jurnal Debit.
- Menyimpan jurnal Kredit.
- Mendukung proses audit akuntansi.

---

## 3.8 Daily Report

### Description

Merepresentasikan laporan operasional harian Teller.

### Responsibilities

- Menyimpan rekap transaksi harian.
- Menjadi dokumen verifikasi Supervisor.
- Menjadi arsip operasional Bank Mini Sekolah.

---

# 4. Domain Relationships

## User

- Mengelola Customer.
- Mengelola Bank Account.
- Membuat Daily Report (Teller).
- Menyetujui Daily Report (Supervisor).
- Membuat Withdrawal Request (Teller).

---

## Customer

- Memiliki satu Customer Account.
- Memiliki satu Bank Account.

---

## Customer Account

- Dimiliki oleh satu Customer.
- Digunakan untuk login aplikasi web maupun mobile.

---

## Bank Account

- Dimiliki oleh satu Customer.
- Memiliki banyak Transaction.
- Memiliki satu QR Code.

---

## Transaction

- Berasal dari satu Bank Account.
- Menghasilkan Journal Entry.
- Dapat berasal dari Withdrawal Request.

---

## Withdrawal Request

- Dibuat oleh Teller.
- Dimiliki oleh satu Bank Account.
- Menghasilkan satu Transaction apabila disetujui.

---

## Journal Entry

- Berasal dari satu Transaction.
- Setiap Transaction menghasilkan dua Journal Entry.

---

## Daily Report

- Dibuat oleh Teller.
- Diverifikasi oleh Supervisor.

---

# 5. Cardinality

| Relationship | Cardinality |
|-------------|-------------|
| Customer → Customer Account | One to One |
| Customer → Bank Account | One to One |
| Bank Account → Transaction | One to Many |
| Transaction → Journal Entry | One to Many (2 Entries) |
| Bank Account → Withdrawal Request | One to Many |
| Withdrawal Request → Transaction | One to One |
| Teller → Withdrawal Request | One to Many |
| Teller → Daily Report | One to Many |
| Supervisor → Daily Report | One to Many |

---

# 6. Domain Lifecycle

## Customer Registration

Administrator membuat data Customer.

↓

Sistem membuat Bank Account.

↓

Sistem menghasilkan nomor rekening.

↓

Sistem menghasilkan QR Code.

↓

Sistem membuat Customer Account.

↓

Sistem menghasilkan username, password, dan PIN awal.

↓

Nasabah melakukan login pertama.

↓

Nasabah wajib mengganti password dan PIN.

---

## Deposit Transaction

Teller melakukan identifikasi rekening.

↓

Teller memasukkan nominal setoran.

↓

Sistem memvalidasi data.

↓

Sistem membuat Transaction.

↓

Sistem memperbarui saldo.

↓

Sistem membuat Journal Entry.

↓

Sistem menghasilkan bukti transaksi.

---

## Withdrawal Transaction

Teller mengajukan Withdrawal Request.

↓

Nasabah menerima permintaan otorisasi pada aplikasi.

↓

Nasabah memasukkan PIN.

↓

Sistem memvalidasi PIN.

↓

Withdrawal Request disetujui.

↓

Sistem membuat Transaction.

↓

Sistem memperbarui saldo.

↓

Sistem membuat Journal Entry.

↓

Sistem menghasilkan bukti transaksi.

---

## Daily Closing

Teller membuat Daily Report.

↓

Supervisor melakukan verifikasi.

↓

Supervisor memberikan approval.

↓

Laporan diarsipkan oleh sistem.

---

# 7. Domain Summary

| Entity | Responsibility |
|---------|----------------|
| User | Pengguna internal sistem |
| Customer | Data identitas nasabah |
| Customer Account | Akun login nasabah |
| Bank Account | Rekening tabungan nasabah |
| Transaction | Pencatatan transaksi keuangan |
| Withdrawal Request | Otorisasi penarikan melalui PIN |
| Journal Entry | Pencatatan jurnal akuntansi |
| Daily Report | Laporan operasional harian Teller |

---

# 8. Scope Boundary

Dokumen Domain Model hanya mendefinisikan objek bisnis beserta hubungan antar objek.

Dokumen ini tidak membahas:

- Struktur tabel database.
- Primary Key dan Foreign Key.
- Tipe data.
- API Endpoint.
- Antarmuka pengguna (UI).
- Implementasi aplikasi web maupun mobile.

Seluruh aspek implementasi tersebut akan dibahas pada dokumen berikutnya.