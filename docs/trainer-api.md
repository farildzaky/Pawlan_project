# Trainer API Documentation

**Base URL:** `http://localhost:8000/api`

**Authentication:** Menggunakan JWT Bearer Token. Sertakan header berikut untuk endpoint yang membutuhkan autentikasi:

```
Authorization: Bearer {token}
```

---

## Daftar Endpoint

| Method | Endpoint | Akses | Deskripsi |
|--------|----------|-------|-----------|
| GET | `/trainers` | Public | Ambil semua trainer |
| GET | `/trainers/{id}` | Public | Detail satu trainer |
| POST | `/trainers` | Admin | Buat trainer baru |
| PUT | `/trainers/{id}` | Admin / Trainer | Update data trainer |
| DELETE | `/trainers/{id}` | Admin | Hapus trainer |

---

## 1. GET /trainers — Daftar Semua Trainer

Mengambil daftar semua trainer dengan dukungan filter dan pagination.

**Akses:** Public (tidak perlu login)

### Query Parameters

| Parameter | Tipe | Wajib | Deskripsi |
|-----------|------|-------|-----------|
| `specialization` | string | Tidak | Filter berdasarkan spesialisasi (partial match) |
| `is_active` | boolean | Tidak | Filter berdasarkan status aktif (`true` / `false`) |
| `search` | string | Tidak | Cari berdasarkan nama atau email |
| `class_id` | integer | Tidak | Filter trainer yang mengajar kelas tertentu |
| `per_page` | integer | Tidak | Jumlah data per halaman (default: `10`) |

### Contoh Request

```http
GET /api/trainers?specialization=yoga&is_active=true&per_page=5
```

### Contoh Response (200 OK)

```json
{
  "success": true,
  "message": "Trainers retrieved.",
  "data": [
    {
      "id": 2,
      "name": "Sari Wulandari",
      "email": "sari@pawlan.com",
      "phone": "081234567891",
      "specialization": "Yoga & Pilates",
      "years_experience": 8,
      "certification": "RYT-500 Yoga Alliance",
      "bio": "Instruktur yoga berpengalaman...",
      "hourly_rate": "175000.00",
      "is_active": true,
      "classes_count": 2,
      "created_at": "2026-05-03T11:00:00.000000Z",
      "updated_at": "2026-05-03T11:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 5,
    "total": 1
  }
}
```

---

## 2. GET /trainers/{id} — Detail Trainer

Mengambil detail satu trainer beserta kelas yang diajar dan jumlah sesi.

**Akses:** Public (tidak perlu login)

### Path Parameter

| Parameter | Tipe | Deskripsi |
|-----------|------|-----------|
| `id` | integer | ID trainer |

### Contoh Request

```http
GET /api/trainers/1
```

### Contoh Response (200 OK)

```json
{
  "success": true,
  "message": "Trainer detail retrieved.",
  "data": {
    "id": 1,
    "name": "Andi Pratama",
    "email": "andi@pawlan.com",
    "phone": "081234567890",
    "specialization": "Strength Training",
    "years_experience": 6,
    "certification": "NSCA-CPT",
    "bio": "Spesialis latihan kekuatan...",
    "hourly_rate": "150000.00",
    "is_active": true,
    "sessions_count": 12,
    "classes": [
      { "id": 1, "name": "Power Lifting", "category": "Strength" },
      { "id": 3, "name": "Body Combat", "category": "Cardio" }
    ],
    "created_at": "2026-05-03T11:00:00.000000Z",
    "updated_at": "2026-05-03T11:00:00.000000Z"
  }
}
```

### Contoh Response (404 Not Found)

```json
{
  "message": "No query results for model [App\\Models\\Trainer] 99"
}
```

---

## 3. POST /trainers — Buat Trainer Baru

Membuat data trainer baru dan menghubungkannya ke kelas (opsional).

**Akses:** Admin only  
**Header:** `Authorization: Bearer {token}`, `Content-Type: application/json`

### Request Body

| Field | Tipe | Wajib | Validasi |
|-------|------|-------|---------|
| `name` | string | Ya | Maks 100 karakter |
| `email` | string | Ya | Format email valid, unik, maks 150 karakter |
| `phone` | string | Ya | Maks 20 karakter |
| `specialization` | string | Ya | Maks 150 karakter |
| `years_experience` | integer | Ya | Minimal 0 |
| `certification` | string | Tidak | Teks bebas |
| `bio` | string | Tidak | Teks bebas |
| `hourly_rate` | numeric | Ya | Minimal 0 |
| `is_active` | boolean | Tidak | Default `true` |
| `class_ids` | array of integer | Tidak | ID kelas yang ada di database |

### Contoh Request

```http
POST /api/trainers
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Dewi Rahmawati",
  "email": "dewi@pawlan.com",
  "phone": "081298765432",
  "specialization": "Zumba & Dance",
  "years_experience": 5,
  "certification": "Zumba Instructor Network",
  "bio": "Instruktur Zumba energetik dan berpengalaman.",
  "hourly_rate": 160000,
  "is_active": true,
  "class_ids": [2, 4]
}
```

### Contoh Response (201 Created)

```json
{
  "success": true,
  "message": "Trainer created.",
  "data": {
    "id": 4,
    "name": "Dewi Rahmawati",
    "email": "dewi@pawlan.com",
    "phone": "081298765432",
    "specialization": "Zumba & Dance",
    "years_experience": 5,
    "certification": "Zumba Instructor Network",
    "bio": "Instruktur Zumba energetik dan berpengalaman.",
    "hourly_rate": "160000.00",
    "is_active": true,
    "classes": [
      { "id": 2, "name": "Zumba Fit" },
      { "id": 4, "name": "Dance Aerobic" }
    ],
    "created_at": "2026-05-05T08:00:00.000000Z",
    "updated_at": "2026-05-05T08:00:00.000000Z"
  }
}
```

### Contoh Response Error Validasi (422 Unprocessable Entity)

```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

### Contoh Response Tidak Terautentikasi (401 Unauthorized)

```json
{
  "message": "Unauthenticated."
}
```

### Contoh Response Tidak Berwenang (403 Forbidden)

```json
{
  "message": "Forbidden."
}
```

---

## 4. PUT /trainers/{id} — Update Trainer

Memperbarui data trainer. Trainer hanya bisa mengubah profil milik sendiri, dan hanya field terbatas.

**Akses:** Admin atau Trainer (login wajib)  
**Header:** `Authorization: Bearer {token}`, `Content-Type: application/json`

### Path Parameter

| Parameter | Tipe | Deskripsi |
|-----------|------|-----------|
| `id` | integer | ID trainer yang akan diupdate |

### Request Body

> Semua field bersifat opsional (partial update). Kirim hanya field yang ingin diubah.

#### Jika diakses oleh **Admin**

| Field | Tipe | Validasi |
|-------|------|---------|
| `name` | string | Maks 100 karakter |
| `email` | string | Format email valid, maks 150 karakter, unik |
| `phone` | string | Maks 20 karakter |
| `specialization` | string | Maks 150 karakter |
| `years_experience` | integer | Minimal 0 |
| `certification` | string | Teks bebas |
| `bio` | string | Teks bebas |
| `hourly_rate` | numeric | Minimal 0 |
| `is_active` | boolean | `true` / `false` |
| `class_ids` | array of integer | ID kelas yang ada |

#### Jika diakses oleh **Trainer** (hanya profil sendiri)

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `phone` | string | Nomor telepon |
| `bio` | string | Bio trainer |
| `certification` | string | Sertifikasi |

> Trainer **tidak bisa** mengubah `hourly_rate`, `is_active`, `class_ids`, `name`, `email`, `specialization`, `years_experience`.

### Contoh Request — Admin Update Penuh

```http
PUT /api/trainers/4
Authorization: Bearer {token}
Content-Type: application/json

{
  "hourly_rate": 180000,
  "is_active": true,
  "class_ids": [2, 4, 5]
}
```

### Contoh Request — Trainer Update Profil Sendiri

```http
PUT /api/trainers/4
Authorization: Bearer {token}
Content-Type: application/json

{
  "phone": "081300001111",
  "bio": "Bio terbaru saya sebagai instruktur Zumba.",
  "certification": "Zumba Gold Certification"
}
```

### Contoh Response (200 OK)

```json
{
  "success": true,
  "message": "Trainer updated.",
  "data": {
    "id": 4,
    "name": "Dewi Rahmawati",
    "email": "dewi@pawlan.com",
    "phone": "081300001111",
    "specialization": "Zumba & Dance",
    "years_experience": 5,
    "certification": "Zumba Gold Certification",
    "bio": "Bio terbaru saya sebagai instruktur Zumba.",
    "hourly_rate": "160000.00",
    "is_active": true,
    "classes": [
      { "id": 2, "name": "Zumba Fit" },
      { "id": 4, "name": "Dance Aerobic" }
    ],
    "updated_at": "2026-05-05T09:00:00.000000Z"
  }
}
```

### Contoh Response Trainer Update Profil Orang Lain (403 Forbidden)

```json
{
  "success": false,
  "message": "Anda hanya bisa mengubah profil trainer milik Anda sendiri."
}
```

---

## 5. DELETE /trainers/{id} — Hapus Trainer

Menghapus (soft delete) trainer. Trainer **tidak bisa dihapus** jika masih memiliki sesi terjadwal atau sedang berlangsung.

**Akses:** Admin only  
**Header:** `Authorization: Bearer {token}`

### Path Parameter

| Parameter | Tipe | Deskripsi |
|-----------|------|-----------|
| `id` | integer | ID trainer yang akan dihapus |

### Contoh Request

```http
DELETE /api/trainers/4
Authorization: Bearer {token}
```

### Contoh Response (200 OK)

```json
{
  "success": true,
  "message": "Trainer deleted."
}
```

### Contoh Response Trainer Masih Punya Sesi Aktif (409 Conflict)

```json
{
  "success": false,
  "message": "Tidak dapat menghapus trainer yang masih memiliki sesi terjadwal."
}
```

---

## Ringkasan Aturan Akses

| Endpoint | Guest | Member | Trainer | Admin |
|----------|-------|--------|---------|-------|
| GET /trainers | ✅ | ✅ | ✅ | ✅ |
| GET /trainers/{id} | ✅ | ✅ | ✅ | ✅ |
| POST /trainers | ❌ | ❌ | ❌ | ✅ |
| PUT /trainers/{id} | ❌ | ❌ | ✅ (diri sendiri, field terbatas) | ✅ (semua field) |
| DELETE /trainers/{id} | ❌ | ❌ | ❌ | ✅ |

---

## Kode Status HTTP

| Kode | Arti |
|------|------|
| 200 | OK — Request berhasil |
| 201 | Created — Trainer berhasil dibuat |
| 401 | Unauthorized — Token tidak ada atau tidak valid |
| 403 | Forbidden — Role tidak memiliki akses |
| 404 | Not Found — Trainer tidak ditemukan |
| 409 | Conflict — Tidak bisa dihapus karena ada sesi aktif |
| 422 | Unprocessable Entity — Validasi gagal |
