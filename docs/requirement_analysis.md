# Requirement Analysis

---

# 1. Purpose

Dokumen ini bertujuan untuk mengidentifikasi dan mendokumentasikan kebutuhan sistem pada Sistem Informasi E-Teller Bank Mini Sekolah berdasarkan Business Process Analysis dan spesifikasi Uji Kompetensi Keahlian (UKK).

Dokumen ini digunakan sebagai dasar dalam proses perancangan sistem, pembuatan desain aplikasi, implementasi, dan pengujian sistem.

---

# 2. System Overview

Sistem Informasi E-Teller Bank Mini Sekolah merupakan aplikasi berbasis client-server yang digunakan untuk mendukung operasional Bank Mini Sekolah dalam pengelolaan pengguna, data nasabah, rekening, transaksi keuangan, serta pelaporan operasional.

Sistem menerapkan Role-Based Access Control (RBAC) untuk membatasi hak akses berdasarkan peran pengguna.

Sistem terdiri dari beberapa pengguna utama:

- Administrator
- Teller
- Supervisor
- Nasabah

---

# 3. Functional Requirements

## 3.1 Authentication

| ID | Requirement | Actor | Priority |
|----|-------------|-------|----------|
| FR-001 | Sistem harus menyediakan fitur login untuk pengguna. | All Users | High |
| FR-002 | Sistem harus menyediakan fitur logout. | All Users | High |
| FR-003 | Sistem harus membatasi akses pengguna berdasarkan role. | System | High |
| FR-004 | Sistem harus menyediakan mekanisme perubahan password pengguna. | All Users | Medium |
| FR-005 | Nasabah dapat melakukan reset password akun miliknya. | Nasabah | Medium |

---

# 3.2 User Management

| ID | Requirement | Actor | Priority |
|----|-------------|-------|----------|
| FR-006 | Administrator dapat membuat akun pengguna internal. | Administrator | High |
| FR-007 | Administrator dapat memperbarui informasi akun pengguna internal. | Administrator | High |
| FR-008 | Administrator dapat mengubah role pengguna internal. | Administrator | High |
| FR-009 | Administrator dapat mengaktifkan akun pengguna. | Administrator | Medium |
| FR-010 | Administrator dapat menonaktifkan akun pengguna. | Administrator | Medium |
| FR-011 | Administrator dapat melihat daftar akun pengguna internal. | Administrator | High |

---

# 3.3 Customer Management

| ID | Requirement | Actor | Priority |
|----|-------------|-------|----------|
| FR-012 | Administrator dapat membuat data nasabah baru. | Administrator | High |
| FR-013 | Administrator dapat memperbarui data nasabah. | Administrator | High |
| FR-014 | Administrator dapat melihat informasi data nasabah. | Administrator | High |
| FR-015 | Sistem dapat menghubungkan data nasabah dengan akun akses nasabah. | System | High |

---

# 3.4 Account Management

| ID | Requirement | Actor | Priority |
|----|-------------|-------|----------|
| FR-016 | Administrator dapat membuat rekening untuk nasabah. | Administrator | High |
| FR-017 | Sistem dapat menghasilkan nomor rekening secara otomatis. | System | High |
| FR-018 | Sistem dapat menghasilkan QR Code rekening. | System | High |
| FR-019 | Sistem dapat menghubungkan rekening dengan data nasabah. | System | High |

---

# 3.5 Customer Identification

| ID | Requirement | Actor | Priority |
|----|-------------|-------|----------|
| FR-020 | Teller dapat mencari data nasabah berdasarkan nomor rekening. | Teller | High |
| FR-021 | Teller dapat melakukan identifikasi nasabah menggunakan QR Code. | Teller | High |
| FR-022 | Sistem dapat menampilkan informasi rekening setelah identifikasi berhasil. | System | High |

---

# 3.6 Cash Deposit Transaction

| ID | Requirement | Actor | Priority |
|----|-------------|-------|----------|
| FR-023 | Teller dapat melakukan transaksi setoran tunai. | Teller | High |
| FR-024 | Sistem dapat melakukan validasi transaksi setoran. | System | High |
| FR-025 | Sistem dapat memperbarui saldo rekening setelah transaksi berhasil. | System | High |
| FR-026 | Sistem dapat menghasilkan bukti transaksi setoran. | System | Medium |

---

# 3.7 Cash Withdrawal Transaction

| ID | Requirement | Actor | Priority |
|----|-------------|-------|----------|
| FR-027 | Teller dapat melakukan transaksi penarikan tunai. | Teller | High |
| FR-028 | Sistem dapat melakukan verifikasi PIN nasabah sebelum transaksi diproses. | System | High |
| FR-029 | Sistem dapat melakukan validasi saldo rekening sebelum transaksi penarikan. | System | High |
| FR-030 | Sistem dapat memperbarui saldo rekening setelah transaksi berhasil. | System | High |
| FR-031 | Sistem dapat menghasilkan bukti transaksi penarikan. | System | Medium |

---

# 3.8 Daily Closing and Reporting

| ID | Requirement | Actor | Priority |
|----|-------------|-------|----------|
| FR-032 | Teller dapat membuat laporan operasional harian. | Teller | High |
| FR-033 | Sistem dapat menghitung data penutupan kas harian. | System | High |
| FR-034 | Supervisor dapat melakukan verifikasi laporan harian. | Supervisor | High |
| FR-035 | Supervisor dapat memberikan approval laporan harian. | Supervisor | High |

---

# 3.9 Accounting Audit

| ID | Requirement | Actor | Priority |
|----|-------------|-------|----------|
| FR-036 | Sistem dapat mencatat jurnal transaksi secara otomatis. | System | High |
| FR-037 | Administrator dapat melihat jurnal akuntansi. | Administrator | High |
| FR-038 | Administrator dapat melakukan audit terhadap jurnal akuntansi. | Administrator | High |

---

# 3.10 Customer Information

| ID | Requirement | Actor | Priority |
|----|-------------|-------|----------|
| FR-039 | Nasabah dapat melihat informasi rekening miliknya. | Nasabah | High |
| FR-040 | Nasabah dapat melihat saldo rekening. | Nasabah | High |
| FR-041 | Nasabah dapat melihat riwayat transaksi rekening. | Nasabah | High |
| FR-042 | Nasabah dapat melihat QR Code rekening. | Nasabah | Medium |

---

# 4. Functional Restrictions

| ID | Restriction |
|----|-------------|
| FRS-001 | Administrator tidak dapat melakukan transaksi setoran maupun penarikan tunai. |
| FRS-002 | Teller tidak dapat mengubah atau menghapus transaksi yang telah tersimpan. |
| FRS-003 | Teller tidak dapat melakukan approval terhadap laporan hariannya sendiri. |
| FRS-004 | Supervisor tidak dapat melakukan transaksi langsung kepada nasabah. |
| FRS-005 | Nasabah tidak dapat mengakses data pengguna internal, jurnal akuntansi, data nasabah lain, atau modul transaksi. |

---

# 5. Non Functional Requirements

## 5.1 Security

| ID | Requirement |
|----|-------------|
| NFR-001 | Sistem harus menerapkan autentikasi pengguna yang aman. |
| NFR-002 | Sistem harus menerapkan Role-Based Access Control (RBAC). |
| NFR-003 | Sistem harus menggunakan password hashing untuk penyimpanan password. |
| NFR-004 | Sistem harus menjaga keamanan PIN nasabah. |

---

## 5.2 Reliability

| ID | Requirement |
|----|-------------|
| NFR-005 | Sistem harus menjaga konsistensi data transaksi. |
| NFR-006 | Sistem harus menangani kesalahan transaksi menggunakan mekanisme error handling. |
| NFR-007 | Sistem harus mendukung rollback apabila transaksi gagal. |

---

## 5.3 Database

| ID | Requirement |
|----|-------------|
| NFR-008 | Database harus menerapkan struktur yang terorganisasi dan menghindari redundansi data. |
| NFR-009 | Database harus menerapkan Foreign Key untuk menjaga integritas data. |
| NFR-010 | Database harus menerapkan validasi data sesuai kebutuhan sistem. |

---

# 6. Business Constraints

- Sistem menggunakan arsitektur client-server.
- Sistem menerapkan Role-Based Access Control.
- Sistem harus mengikuti aturan bisnis yang telah ditentukan pada dokumen Business Rules.
- Sistem harus menjaga integritas data transaksi keuangan.
- Sistem harus menyediakan akses informasi rekening untuk nasabah melalui website dan aplikasi mobile.

---

# 7. Requirement Traceability

| Business Process | Functional Requirement |
|------------------|------------------------|
| User Management | FR-006 - FR-011 |
| Customer Management | FR-012 - FR-015 |
| Account Management | FR-016 - FR-019 |
| Customer Identification | FR-020 - FR-022 |
| Cash Deposit | FR-023 - FR-026 |
| Cash Withdrawal | FR-027 - FR-031 |
| Daily Closing | FR-032 - FR-035 |
| Accounting Audit | FR-036 - FR-038 |
| Customer Information | FR-039 - FR-042 |