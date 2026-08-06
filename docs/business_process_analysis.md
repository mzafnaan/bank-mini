# Business Process Analysis

# 1. Purpose

Dokumen ini bertujuan untuk mengidentifikasi dan mendokumentasikan proses bisnis utama pada Sistem Informasi E-Teller Bank Mini Sekolah

---

# 2. Business Overview

Sistem Informasi E-Teller Bank Mini Sekolah merupakan sistem yang mendukung operasional Bank Mini Sekolah dalam pengelolaan pengguna, nasabah, rekening, transaksi keuangan, serta pelaporan operasional. Seluruh proses bisnis dilakukan berdasarkan hak akses masing-masing pengguna untuk menjaga keamanan dan integritas data.

---

# 3. Business Actors

| Actor | Description |
|--------|-------------|
| Administrator | Mengelola data pengguna, data nasabah, rekening, dan konfigurasi sistem.
| Teller | Melayani transaksi setoran dan penarikan tunai. 
| Supervisor | Memverifikasi laporan operasional harian. 
| Nasabah | Melihat informasi rekening serta melakukan verifikasi PIN saat penarikan. 

---

# 4. Core Business Processes

Berdasarkan dokumen UKK, sistem memiliki proses bisnis utama sebagai berikut:

1. Manajemen Pengguna
2. Manajemen Nasabah
3. Manajemen Rekening
4. Identifikasi Nasabah
5. Transaksi Setoran Tunai
6. Transaksi Penarikan Tunai
7. Pelaporan Harian
8. Monitoring Informasi Rekening Nasabah

---

# 5. Business Process Details

## 5.1 User Management

### Actor

Administrator

### Description

Administrator mengelola data pengguna sistem beserta hak akses yang dimiliki masing-masing pengguna, termasuk menambahkan, mengubah, mengaktifkan, dan menonaktifkan akun pengguna.

### Business Flow

1. Administrator login ke sistem.
2. Administrator memilih menu manajemen pengguna.
3. Administrator menambahkan, mengubah, mengaktifkan, atau menonaktifkan akun pengguna.
4. Sistem memvalidasi data pengguna.
5. Sistem menyimpan perubahan data pengguna.  

---

## 5.2 Customer Management

### Actor

Administrator

### Description

Administrator mengelola data nasabah yang akan menggunakan layanan Bank Mini Sekolah.

### Business Flow

1. Administrator login ke sistem.
2. Administrator menambahkan atau mengubah data nasabah.
3. Sistem menyimpan data nasabah.

---

## 5.3 Account Management

### Actor

Administrator

### Description

Administrator membuat rekening untuk nasabah sebagai identitas transaksi.

### Business Flow

1. Administrator memilih data nasabah.
2. Sistem membuat rekening.
3. Sistem menghasilkan nomor rekening.
4. Sistem menghasilkan QR Code rekening.

---

## 5.4 Customer Identification

### Actor

Teller

### Description

Teller melakukan identifikasi nasabah sebelum transaksi dilakukan.

### Business Flow

1. Nasabah datang ke teller.
2. Teller melakukan identifikasi menggunakan QR Code atau nomor rekening.
3. Sistem menampilkan informasi rekening nasabah.

---

## 5.5 Cash Deposit

### Actor

Teller

### Description

Teller melakukan transaksi setoran tunai ke rekening nasabah.

### Business Flow

1. Teller melakukan identifikasi nasabah.
2. Teller memasukkan nominal setoran.
3. Sistem memvalidasi data transaksi.
4. Sistem menambahkan saldo rekening.
5. Sistem menyimpan transaksi.
6. Sistem mencatat jurnal transaksi.
7. Sistem menghasilkan bukti transaksi.

---

## 5.6 Cash Withdrawal

### Actor

Teller, Nasabah

### Description

Teller melakukan transaksi penarikan tunai setelah proses verifikasi PIN nasabah.

### Business Flow

1. Teller melakukan identifikasi nasabah.
2. Nasabah memasukkan PIN.
3. Sistem memvalidasi PIN.
4. Teller memasukkan nominal penarikan.
5. Sistem memvalidasi saldo.
6. Sistem mengurangi saldo rekening.
7. Sistem menyimpan transaksi.
8. Sistem mencatat jurnal transaksi.
9. Sistem menghasilkan bukti transaksi.

---

## 5.7 Daily Report

### Actor

Teller, Supervisor

### Description

Teller membuat laporan operasional harian yang selanjutnya diverifikasi oleh Supervisor.

### Business Flow

1. Teller menghasilkan laporan harian.
2. Supervisor melakukan pemeriksaan laporan.
3. Supervisor melakukan approval laporan.

---

## 5.8 Customer Information

### Actor

Nasabah

### Description

Nasabah mengakses informasi rekening miliknya.

### Business Flow

1. Nasabah login ke sistem.
2. Nasabah melihat saldo rekening.
3. Nasabah melihat riwayat transaksi.
4. Nasabah melihat QR Code rekening.

---

# 6. Business Process Summary

| Business Process | Primary Actor |
|------------------|---------------|
| User Management | Administrator |
| Customer Management | Administrator |
| Account Management | Administrator |
| Customer Identification | Teller |
| Cash Deposit | Teller |
| Cash Withdrawal | Teller, Nasabah |
| Daily Report | Teller, Supervisor |
| Customer Information | Nasabah |

---

# 7. Scope Boundary

Business process yang didokumentasikan pada dokumen ini hanya mencakup proses yang dijelaskan pada dokumen UKK.

Dokumen ini tidak membahas:

- Desain antarmuka pengguna.
- Struktur database.
- Desain API.
- Arsitektur sistem.
- Teknologi yang digunakan.
- Implementasi aplikasi web maupun mobile.

Aspek-aspek tersebut akan dibahas pada dokumen tersendiri.