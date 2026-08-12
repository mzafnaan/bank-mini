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
- Menggunakan Foreign Key untuk menjaga relasi antar tabel.
- Menggunakan `ON DELETE RESTRICT` pada tabel master agar data transaksi tetap terjaga.
- Tidak menyimpan data yang redundan.
- Saldo rekening hanya disimpan pada tabel `bank_accounts`.
- Seluruh transaksi menjadi sumber utama (source of truth) untuk laporan dan jurnal.

---

# 3. Database Tables

## 3.1 users

Menyimpan akun pengguna internal sistem.

### Responsibilities

- Administrator
- Teller
- Supervisor

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
- gender
- address
- phone
- created_at
- updated_at

---

## 3.3 bank_accounts

Menyimpan data rekening nasabah.

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

Menyimpan akun login nasabah.

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

Menyimpan seluruh transaksi keuangan.

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

Menyimpan permintaan penarikan yang menunggu verifikasi PIN.

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

Menyimpan jurnal akuntansi yang dihasilkan otomatis dari transaksi.

### Main Attributes

- id
- transaction_id
- account_code
- type
- amount
- created_at

---

## 3.8 daily_reports

Menyimpan rekap operasional harian Teller.

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

Setiap tabel memiliki Primary Key berupa `id`.

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

Seluruh relasi master menggunakan

```
ON DELETE RESTRICT
```

untuk menjaga integritas data transaksi.

---

# 6. Database Normalization

Seluruh tabel dirancang memenuhi Third Normal Form (3NF):

- Tidak terdapat data yang berulang.
- Setiap atribut bergantung pada Primary Key.
- Tidak terdapat ketergantungan transitif.
- Informasi identitas nasabah dipisahkan dari informasi rekening.
- Informasi akun login dipisahkan dari data nasabah.

---

# 7. Database Summary

| Table | Description |
|--------|-------------|
| users | Akun pengguna internal |
| customers | Data nasabah |
| bank_accounts | Data rekening |
| customer_accounts | Akun login nasabah |
| transactions | Data transaksi |
| withdrawal_requests | Permintaan penarikan |
| journal_entries | Jurnal akuntansi |
| daily_reports | Laporan harian |

---

# 8. Scope Boundary

Dokumen ini hanya membahas rancangan basis data.

Dokumen ini tidak membahas:

- SQL Migration
- Stored Procedure
- Trigger
- Function
- View
- API
- User Interface

Implementasi teknis akan dibahas pada tahap pengembangan sistem.