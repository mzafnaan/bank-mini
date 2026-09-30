# Database Design

---

# 1. Purpose

Dokumen ini menjelaskan rancangan basis data yang digunakan pada Sistem Informasi E-Teller Bank Mini Sekolah.

Perancangan database dibuat berdasarkan Domain Model, Business Rules, serta Requirement Analysis yang telah disusun sebelumnya. Struktur database dirancang agar memenuhi prinsip normalisasi (Third Normal Form / 3NF), menjaga integritas data, serta mendukung implementasi aturan bisnis pada sistem.

---

# 2. Database Design Principles

Perancangan database menggunakan prinsip berikut:

- Memenuhi Third Normal Form (3NF).
- Menggunakan Primary Key pada setiap tabel.
- Menggunakan Foreign Key untuk menjaga integritas relasi antar tabel.
- Menggunakan `ON DELETE RESTRICT` pada tabel master agar data transaksi tetap terjaga.
- Tidak menyimpan data yang redundan.
- Saldo rekening hanya disimpan pada tabel `bank_accounts`.
- Seluruh transaksi menjadi sumber utama (Source of Truth) untuk laporan harian dan jurnal akuntansi.

---

# 3. Database Tables

## 3.1 users

Menyimpan akun pengguna internal yang memiliki akses ke sistem.

### Responsibilities

- Administrator
- Supervisor
- Teller

### Main Attributes

- id
- name
- username
- password
- role
- status
- created_at
- updated_at

---

## 3.2 customers

Menyimpan data identitas nasabah.

### Main Attributes

- id
- nis
- name
- class
- phone
- created_at
- updated_at

---

## 3.3 bank_accounts

Menyimpan informasi rekening milik nasabah.

### Main Attributes

- id
- customer_id
- account_number
- balance
- qr_code
- created_at
- updated_at

---

## 3.4 customer_accounts

Menyimpan akun login nasabah untuk aplikasi mobile.

### Main Attributes

- id
- customer_id
- username
- password
- pin
- first_login
- status
- created_at
- updated_at

---

## 3.5 transactions

Menyimpan seluruh transaksi yang dilakukan melalui Teller.

### Main Attributes

- id
- bank_account_id
- teller_id
- type
- amount
- created_at

### Transaction Types

- Deposit
- Withdrawal

---

## 3.6 withdrawal_requests

Menyimpan permintaan penarikan saldo yang menunggu verifikasi PIN nasabah.

### Main Attributes

- id
- bank_account_id
- teller_id
- amount
- status
- expires_at
- approved_at
- created_at

---

## 3.7 journal_entries

Menyimpan jurnal akuntansi yang dihasilkan secara otomatis dari setiap transaksi.

### Main Attributes

- id
- transaction_id
- account_code
- type
- amount
- created_at

---

## 3.8 daily_reports

Menyimpan laporan operasional harian Teller.

### Main Attributes

- id
- teller_id
- supervisor_id
- report_date
- opening_cash
- total_deposit
- total_withdrawal
- closing_cash
- status
- approved_at
- created_at

---

# 4. Database Relationships

| Parent | Child | Relationship |
|---------|-------|--------------|
| customers | bank_accounts | One to One |
| customers | customer_accounts | One to One |
| bank_accounts | transactions | One to Many |
| bank_accounts | withdrawal_requests | One to Many |
| transactions | journal_entries | One to Many |
| users | transactions | One to Many |
| users | withdrawal_requests | One to Many |
| users | daily_reports | One to Many |

---

# 5. Database Constraints

## Primary Key

Seluruh tabel menggunakan Primary Key berupa kolom `id`.

---

## Foreign Key

- bank_accounts.customer_id → customers.id
- customer_accounts.customer_id → customers.id
- transactions.bank_account_id → bank_accounts.id
- transactions.teller_id → users.id
- withdrawal_requests.bank_account_id → bank_accounts.id
- withdrawal_requests.teller_id → users.id
- journal_entries.transaction_id → transactions.id
- daily_reports.teller_id → users.id
- daily_reports.supervisor_id → users.id

---

## Unique Constraints

- users.username
- customers.nis
- bank_accounts.account_number
- customer_accounts.username

---

## Delete Rules

Seluruh tabel master menggunakan aturan:

```text
ON DELETE RESTRICT
```

untuk menjaga konsistensi data transaksi serta riwayat audit.

---

# 6. Database Normalization

Seluruh tabel dirancang memenuhi Third Normal Form (3NF):

- Tidak terdapat data yang redundan.
- Seluruh atribut bergantung pada Primary Key.
- Tidak terdapat ketergantungan transitif.
- Informasi identitas nasabah dipisahkan dari informasi rekening.
- Informasi akun login nasabah dipisahkan dari data identitas.
- Informasi transaksi dipisahkan dari jurnal akuntansi.
- Laporan harian dibentuk dari rekap transaksi operasional.

---

# 7. Database Summary

| Table | Description |
|--------|-------------|
| users | Akun pengguna internal |
| customers | Data identitas nasabah |
| bank_accounts | Data rekening nasabah |
| customer_accounts | Akun login nasabah |
| transactions | Riwayat transaksi |
| withdrawal_requests | Permintaan penarikan saldo |
| journal_entries | Jurnal akuntansi |
| daily_reports | Laporan operasional harian |

---

# 8. Scope Boundary

Dokumen ini hanya membahas rancangan basis data.

Tidak mencakup implementasi teknis seperti:

- Laravel Migration
- Seeder
- Model
- Stored Procedure
- Trigger
- Function
- View
- REST API
- User Interface

Seluruh implementasi teknis akan dijelaskan pada dokumen Architecture Design dan API Design.