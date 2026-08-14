# API Design

## Tujuan

Dokumen ini menjelaskan rancangan REST API yang digunakan pada Sistem Informasi E-Teller Bank Mini Sekolah.

REST API digunakan sebagai media komunikasi antara aplikasi Mobile Nasabah dengan server Laravel. Sedangkan aplikasi Web menggunakan Laravel Blade sehingga berkomunikasi langsung dengan Controller tanpa melalui REST API.

---

# Arsitektur API

```
                 Browser
                     │
             Laravel Blade
                     │
               Web Controller
                     │
                 Service Layer
                     │
                 Database MySQL


                 Flutter Mobile
                     │
                 REST API v1
                     │
               API Controller
                     │
                 Service Layer
                     │
                 Database MySQL
```

---

# Authentication

## Web

Autentikasi pada aplikasi web menggunakan Session Authentication bawaan Laravel.

Fitur:

- Login
- Logout
- Session
- Middleware Authentication
- Middleware Role

Tidak menggunakan API Token.

---

## Mobile

Autentikasi aplikasi mobile menggunakan Laravel Sanctum.

Setelah login berhasil, server akan mengirimkan Personal Access Token yang digunakan untuk mengakses endpoint API.

---

# API Versioning

Seluruh endpoint API menggunakan versi.

```
/api/v1
```

Contoh

```
POST /api/v1/login
GET  /api/v1/nasabah/saldo
```

Pendekatan ini memudahkan pengembangan apabila di masa depan terdapat perubahan API tanpa merusak aplikasi yang sudah berjalan.

---

# Format Response

## Success

```json
{
    "success": true,
    "message": "Berhasil",
    "data": {}
}
```

---

## Error

```json
{
    "success": false,
    "message": "Data tidak ditemukan"
}
```

---

# Authentication Endpoint

| Method | Endpoint | Deskripsi |
|---------|----------|-----------|
| POST | /api/v1/login | Login Nasabah |
| POST | /api/v1/logout | Logout |
| POST | /api/v1/ganti-password | Mengubah password |
| POST | /api/v1/ganti-pin | Mengubah PIN |

---

# Dashboard Nasabah

| Method | Endpoint | Deskripsi |
|---------|----------|-----------|
| GET | /api/v1/nasabah/profil | Data profil nasabah |
| GET | /api/v1/nasabah/saldo | Saldo rekening |
| GET | /api/v1/nasabah/riwayat | Riwayat transaksi (pagination) |

---

# Verifikasi PIN Penarikan

Pada proses penarikan dana, Teller akan membuat permintaan penarikan.

Server kemudian membuat permintaan verifikasi kepada aplikasi Nasabah.

Aplikasi Mobile/Web Nasabah akan menampilkan permintaan memasukkan PIN.

Nasabah memasukkan PIN melalui perangkat miliknya sendiri.

Setelah PIN berhasil diverifikasi, server akan melanjutkan proses penarikan dana.

Pendekatan ini menjaga kerahasiaan PIN karena Teller tidak pernah mengetahui maupun memasukkan PIN Nasabah.

---

# Pagination

Riwayat transaksi menggunakan pagination.

Contoh

```
GET /api/v1/nasabah/riwayat?page=1
```

Sehingga sistem tetap ringan walaupun jumlah transaksi telah mencapai ribuan data.

---

# Authorization

Seluruh endpoint dilindungi menggunakan Middleware.

Contoh:

Administrator tidak dapat mengakses endpoint Nasabah.

Nasabah tidak dapat mengakses endpoint Administrator.

Teller tidak dapat mengakses endpoint Supervisor.

Supervisor tidak dapat melakukan transaksi Teller.

---

# Soft Delete Policy

Akun yang telah memiliki riwayat transaksi tidak boleh dihapus.

Administrator hanya dapat:

- Menonaktifkan akun
- Memblokir akun
- Mengaktifkan kembali akun

Pendekatan ini menjaga integritas audit transaksi sesuai kebutuhan sistem perbankan mini.

---

# Security

REST API menerapkan beberapa mekanisme keamanan:

- Authentication menggunakan Laravel Sanctum.
- Authorization menggunakan Middleware Role.
- Password disimpan menggunakan Password Hashing (bcrypt).
- PIN disimpan dalam bentuk Hash.
- Validasi seluruh request di sisi server.
- Endpoint hanya dapat diakses oleh role yang sesuai.

---

# Error Handling

Server akan memberikan kode HTTP sesuai kondisi.

| HTTP Code | Keterangan |
|-----------|------------|
| 200 | Berhasil |
| 201 | Data berhasil dibuat |
| 400 | Request tidak valid |
| 401 | Belum login |
| 403 | Tidak memiliki hak akses |
| 404 | Data tidak ditemukan |
| 422 | Validasi gagal |
| 500 | Kesalahan server |

---

# Catatan

REST API hanya digunakan oleh aplikasi Mobile Nasabah.

Aplikasi Web dibangun menggunakan Laravel Blade sehingga proses komunikasi dilakukan secara langsung melalui Controller Laravel tanpa menggunakan REST API.