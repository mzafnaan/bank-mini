# Business Rules

| Attribute | Value |
|-----------|-------|
| Project | E-Teller Bank Mini Sekolah |
| Document | Business Rules |
| Version | 1.0 |
| Status | Draft |

---

# 1. Purpose

Dokumen ini mendefinisikan aturan bisnis (Business Rules) yang menjadi dasar implementasi Sistem Informasi E-Teller Bank Mini Sekolah.

Seluruh proses operasional, validasi sistem, pengelolaan data, transaksi, dan keamanan harus mengikuti aturan yang didefinisikan pada dokumen ini.

---

# 2. Scope

Business Rules ini mencakup:

- Manajemen Pengguna
- Manajemen Nasabah
- Manajemen Rekening
- Autentikasi dan Otorisasi
- Transaksi Setoran
- Transaksi Penarikan
- Penutupan Kas Harian
- Jurnal Akuntansi
- Keamanan Sistem

---

# 3. General Business Rules

| ID | Business Rule |
|----|---------------|
| BR-001 | Sistem menerapkan Role-Based Access Control (RBAC). |
| BR-002 | Seluruh pengguna hanya dapat mengakses fitur sesuai hak aksesnya. |
| BR-003 | Seluruh transaksi keuangan harus tercatat di dalam sistem. |
| BR-004 | Data transaksi yang telah tersimpan tidak boleh dihapus ataupun diubah. |
| BR-005 | Seluruh perubahan data harus menjaga integritas data sistem. |

---

# 4. User Management Rules

| ID | Business Rule |
|----|---------------|
| BR-006 | Administrator bertanggung jawab mengelola akun pengguna internal. |
| BR-007 | Administrator dapat membuat akun Administrator, Teller, maupun Supervisor. |
| BR-008 | Administrator dapat mengubah role pengguna internal sesuai kebutuhan operasional. |
| BR-009 | Administrator dapat mengaktifkan maupun menonaktifkan akun pengguna internal. |
| BR-010 | Akun pengguna yang berstatus Inactive atau Blocked tidak dapat melakukan login ke sistem. |
| BR-011 | Administrator tidak diperbolehkan melakukan transaksi setoran maupun penarikan tunai. |

---

# 5. Customer & Account Rules

| ID | Business Rule |
|----|---------------|
| BR-012 | Setiap nasabah hanya boleh memiliki satu rekening. |
| BR-013 | Setiap rekening hanya boleh dimiliki oleh satu nasabah. |
| BR-014 | Administrator bertanggung jawab membuat data nasabah dan rekening. |
| BR-015 | Sistem menghasilkan nomor rekening secara otomatis berdasarkan algoritma yang ditentukan. |
| BR-016 | Nomor rekening harus bersifat unik. |
| BR-017 | Sistem menghasilkan QR Code untuk setiap rekening baru. |
| BR-018 | QR Code digunakan sebagai media identifikasi rekening nasabah. |
| BR-019 | Sistem secara otomatis membuat akun login nasabah setelah rekening berhasil dibuat. |
| BR-020 | Username awal nasabah menggunakan NIS. |
| BR-021 | Password awal nasabah dibuat secara otomatis oleh sistem. |
| BR-022 | PIN awal nasabah dibuat secara otomatis oleh sistem dengan panjang enam digit. |
| BR-023 | Nasabah wajib mengganti password dan PIN setelah berhasil melakukan login pertama. |

---

# 6. Authentication & Security Rules

| ID | Business Rule |
|----|---------------|
| BR-024 | Password seluruh pengguna harus disimpan dalam bentuk hash. |
| BR-025 | PIN nasabah harus disimpan dalam bentuk hash. |
| BR-026 | Password digunakan sebagai autentikasi login. |
| BR-027 | PIN hanya digunakan sebagai otorisasi transaksi penarikan tunai. |
| BR-028 | Nasabah hanya dapat mengakses data rekening miliknya sendiri. |

---

# 7. Cash Deposit Rules

| ID | Business Rule |
|----|---------------|
| BR-029 | Teller wajib melakukan identifikasi rekening sebelum transaksi setoran dilakukan. |
| BR-030 | Teller wajib memastikan data slip setoran telah lengkap. |
| BR-031 | Nominal uang fisik harus sesuai dengan nominal yang diinput ke sistem. |
| BR-032 | Sistem menambahkan saldo rekening setelah transaksi berhasil diproses. |
| BR-033 | Sistem mencatat transaksi setoran ke database. |
| BR-034 | Sistem menghasilkan jurnal akuntansi secara otomatis. |
| BR-035 | Sistem menghasilkan bukti transaksi setelah transaksi berhasil. |

---

# 8. Cash Withdrawal Rules

| ID | Business Rule |
|----|---------------|
| BR-036 | Teller wajib melakukan identifikasi rekening sebelum transaksi penarikan dilakukan. |
| BR-037 | Teller mengajukan permintaan penarikan melalui sistem. |
| BR-038 | Sistem membuat permintaan otorisasi penarikan sebelum transaksi diproses. |
| BR-039 | Permintaan otorisasi dikirim ke dashboard nasabah melalui aplikasi web maupun mobile. |
| BR-040 | Nasabah melakukan otorisasi menggunakan PIN melalui perangkat miliknya sendiri. |
| BR-041 | Sistem melakukan validasi PIN sebelum transaksi diproses. |
| BR-042 | Sistem melakukan validasi saldo rekening sebelum transaksi diproses. |
| BR-043 | Sistem menolak transaksi apabila tidak memenuhi aturan bisnis penarikan. |
| BR-044 | Sistem mengurangi saldo rekening setelah transaksi berhasil disetujui. |
| BR-045 | Sistem mencatat transaksi penarikan ke database. |
| BR-046 | Sistem menghasilkan jurnal akuntansi secara otomatis. |
| BR-047 | Sistem menghasilkan bukti transaksi setelah transaksi berhasil. |
| BR-043 | Sistem menolak transaksi penarikan apabila saldo setelah transaksi berada di bawah batas saldo mengendap yang ditentukan. |
| BR-044 | Batas saldo mengendap ditetapkan sebesar Rp10.000 sesuai ketentuan operasional Bank Mini Sekolah. |

---

# 9. Daily Closing Rules

| ID | Business Rule |
|----|---------------|
| BR-048 | Teller melakukan proses penutupan kas pada akhir hari operasional. |
| BR-049 | Sistem menghitung saldo akhir kas secara otomatis. |
| BR-050 | Laporan harian hanya dapat diproses apabila hasil perhitungan sesuai dengan uang fisik. |
| BR-051 | Supervisor melakukan verifikasi laporan harian. |
| BR-052 | Supervisor memberikan approval terhadap laporan harian. |
| BR-053 | Laporan yang telah disetujui tidak dapat diubah kembali. |
| BR-054 | Supervisor tidak diperbolehkan melakukan transaksi setoran maupun penarikan. |

---

# 10. Accounting Rules

| ID | Business Rule |
|----|---------------|
| BR-055 | Setiap transaksi menghasilkan jurnal akuntansi secara otomatis. |
| BR-056 | Setiap transaksi menghasilkan pasangan jurnal Debit dan Kredit yang seimbang. |
| BR-057 | Administrator dapat melihat jurnal akuntansi untuk kebutuhan audit. |
| BR-058 | Jurnal akuntansi tidak dapat diubah maupun dihapus. |

---

# 11. Customer Information Rules

| ID | Business Rule |
|----|---------------|
| BR-059 | Nasabah dapat melihat saldo rekening secara mandiri. |
| BR-060 | Nasabah dapat melihat riwayat transaksi miliknya sendiri. |
| BR-061 | Nasabah dapat melihat QR Code rekening miliknya. |
| BR-062 | Nasabah tidak memiliki akses terhadap data pengguna internal maupun data nasabah lainnya. |

---

# 12. System Constraints

| ID | Constraint |
|----|------------|
| SC-001 | Sistem menggunakan arsitektur client-server. |
| SC-002 | Sistem menyediakan aplikasi web untuk operasional Bank Mini Sekolah. |
| SC-003 | Sistem menyediakan aplikasi web dan aplikasi mobile bagi nasabah dengan fitur yang setara. |
| SC-004 | Komunikasi antara client dan server dilakukan melalui API. |
| SC-005 | Seluruh validasi aturan bisnis dilakukan pada sisi server. |

---

# 13. Design Decisions

Bagian ini berisi keputusan implementasi yang tidak dijelaskan secara eksplisit pada dokumen UKK namun digunakan untuk mendukung kebutuhan sistem.

| ID | Design Decision |
|----|-----------------|
| DD-001 | Sistem membuat akun login nasabah secara otomatis ketika rekening berhasil dibuat. |
| DD-002 | Username awal menggunakan NIS. |
| DD-003 | Password awal dan PIN awal dihasilkan secara otomatis oleh sistem. |
| DD-004 | Nasabah wajib mengganti password dan PIN setelah login pertama. |
| DD-005 | QR Code berisi nomor rekening sebagai identitas rekening nasabah. |
| DD-006 | Otorisasi PIN penarikan dilakukan melalui dashboard nasabah (web/mobile), bukan melalui perangkat Teller. |
| DD-007 | Bukti transaksi dihasilkan dari data transaksi tanpa memerlukan tabel khusus penyimpanan struk. |