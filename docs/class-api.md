# Class, Session & Booking API Documentation

**Base URL:** `http://localhost:8000/api`

**Authentication:** JWT Bearer Token

```
Authorization: Bearer {token}
```

---

## Gambaran Alur Sistem

Berikut alur lengkap dari pembuatan kelas hingga member bisa join:

```
[Admin] Buat Kelas  →  [Admin] Daftarkan Trainer ke Kelas  →  [Admin] Buat Sesi
        ↓
[Member] Lihat Sesi yang tersedia  →  [Member] Booking Sesi  →  [Admin] Konfirmasi
```

> **Penting:** Trainer tidak bisa langsung "join" kelas. Admin mendaftarkan trainer ke kelas saat membuat/update trainer (`class_ids`). Setelah terdaftar, admin bisa membuat sesi dan menunjuk trainer tersebut.

---

## Daftar Semua Endpoint

### Classes
| Method | Endpoint | Akses | Deskripsi |
|--------|----------|-------|-----------|
| GET | `/classes` | Public | Daftar semua kelas |
| GET | `/classes/{id}` | Public | Detail satu kelas |
| POST | `/classes` | Admin | Buat kelas baru |
| PUT | `/classes/{id}` | Admin | Update kelas |
| DELETE | `/classes/{id}` | Admin | Hapus kelas |

### Sessions
| Method | Endpoint | Akses | Deskripsi |
|--------|----------|-------|-----------|
| GET | `/sessions` | Public | Daftar semua sesi |
| GET | `/sessions/{id}` | Public | Detail satu sesi |
| POST | `/sessions` | Admin | Buat sesi baru |
| PUT | `/sessions/{id}` | Admin | Update sesi |
| DELETE | `/sessions/{id}` | Admin | Hapus sesi |

### Bookings
| Method | Endpoint | Akses | Deskripsi |
|--------|----------|-------|-----------|
| GET | `/bookings` | Login wajib | Daftar booking (sesuai role) |
| GET | `/bookings/{id}` | Login wajib | Detail satu booking |
| POST | `/bookings` | Member | Buat booking baru |
| PUT | `/bookings/{id}` | Admin / Member | Update booking |
| DELETE | `/bookings/{id}` | Admin / Member | Batalkan booking |

---

# BAGIAN 1 — CLASSES

---

## 1. GET /classes — Daftar Semua Kelas

**Akses:** Public (tidak perlu login)

### Query Parameters

| Parameter | Tipe | Deskripsi |
|-----------|------|-----------|
| `category` | string | Filter: `cardio`, `strength`, `flexibility`, `mind-body` |
| `difficulty` | string | Filter: `beginner`, `intermediate`, `advanced` |
| `is_active` | boolean | Filter berdasarkan status aktif (`true` / `false`) |
| `search` | string | Cari berdasarkan nama atau deskripsi kelas |
| `per_page` | integer | Jumlah item per halaman (default: `10`) |

### Contoh Request

```http
GET /api/classes?category=cardio&difficulty=beginner&per_page=5
```

### Contoh Response (200 OK)

```json
{
  "success": true,
  "message": "Classes retrieved.",
  "data": [
    {
      "id": 1,
      "name": "Zumba Morning",
      "description": "Kelas zumba energetik di pagi hari.",
      "category": "cardio",
      "difficulty_level": "beginner",
      "duration_minutes": 60,
      "max_capacity": 20,
      "price": "100000.00",
      "image": "classes/zumba.jpg",
      "image_url": "http://localhost:8000/storage/classes/zumba.jpg",
      "is_active": true,
      "sessions_count": 5,
      "trainers": [
        { "id": 2, "name": "Sari Wulandari", "specialization": "Yoga & Pilates" }
      ],
      "created_at": "2026-05-03T11:00:00.000000Z",
      "updated_at": "2026-05-03T11:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 5,
    "total": 12,
    "from": 1,
    "to": 5
  }
}
```

---

## 2. GET /classes/{id} — Detail Kelas

**Akses:** Public

### Contoh Request

```http
GET /api/classes/1
```

### Contoh Response (200 OK)

```json
{
  "success": true,
  "message": "Class detail retrieved.",
  "data": {
    "id": 1,
    "name": "Zumba Morning",
    "description": "Kelas zumba energetik di pagi hari.",
    "category": "cardio",
    "difficulty_level": "beginner",
    "duration_minutes": 60,
    "max_capacity": 20,
    "price": "100000.00",
    "image": "classes/zumba.jpg",
    "image_url": "http://localhost:8000/storage/classes/zumba.jpg",
    "is_active": true,
    "sessions_count": 5,
    "trainers": [
      {
        "id": 2,
        "name": "Sari Wulandari",
        "specialization": "Yoga & Pilates",
        "bio": "Instruktur yoga berpengalaman..."
      }
    ],
    "created_at": "2026-05-03T11:00:00.000000Z",
    "updated_at": "2026-05-03T11:00:00.000000Z"
  }
}
```

---

## 3. POST /classes — Buat Kelas Baru

**Akses:** Admin only  
**Content-Type:** `multipart/form-data` (karena ada upload gambar)

### Request Body

| Field | Tipe | Wajib | Validasi |
|-------|------|-------|---------|
| `name` | string | Ya | Maks 100 karakter |
| `description` | string | Ya | Teks bebas |
| `category` | string | Ya | `cardio`, `strength`, `flexibility`, `mind-body` |
| `difficulty_level` | string | Ya | `beginner`, `intermediate`, `advanced` |
| `duration_minutes` | integer | Ya | Antara 15–180 menit |
| `max_capacity` | integer | Ya | Minimal 1 |
| `price` | numeric | Ya | Minimal 0 |
| `image` | file | Tidak | Format: jpeg/jpg/png, maks 50MB |
| `is_active` | boolean | Tidak | Default `true` |

> **Catatan:** Gunakan `multipart/form-data` bukan `application/json` agar upload gambar berfungsi.

### Contoh Request (cURL)

```bash
curl -X POST http://localhost:8000/api/classes \
  -H "Authorization: Bearer {token}" \
  -F "name=Body Combat" \
  -F "description=Latihan kombinasi tinju dan tendangan berenergi tinggi." \
  -F "category=cardio" \
  -F "difficulty_level=intermediate" \
  -F "duration_minutes=60" \
  -F "max_capacity=25" \
  -F "price=120000" \
  -F "is_active=1" \
  -F "image=@/path/to/image.jpg"
```

### Contoh Response (201 Created)

```json
{
  "success": true,
  "message": "Class created.",
  "data": {
    "id": 5,
    "name": "Body Combat",
    "description": "Latihan kombinasi tinju dan tendangan berenergi tinggi.",
    "category": "cardio",
    "difficulty_level": "intermediate",
    "duration_minutes": 60,
    "max_capacity": 25,
    "price": "120000.00",
    "image": "classes/abc123.jpg",
    "image_url": "http://localhost:8000/storage/classes/abc123.jpg",
    "is_active": true,
    "created_at": "2026-05-05T08:00:00.000000Z",
    "updated_at": "2026-05-05T08:00:00.000000Z"
  }
}
```

---

## 4. PUT /classes/{id} — Update Kelas

**Akses:** Admin only  
**Content-Type:** `multipart/form-data` atau `application/json` (jika tidak ada gambar)

> Semua field bersifat opsional (partial update). Jika upload gambar baru, gambar lama otomatis dihapus dari storage.

### Request Body (sama dengan POST, semua field opsional)

### Contoh Request — Update harga dan status saja

```bash
curl -X PUT http://localhost:8000/api/classes/5 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"price": 135000, "is_active": false}'
```

### Contoh Request — Ganti gambar

```bash
curl -X POST http://localhost:8000/api/classes/5 \
  -H "Authorization: Bearer {token}" \
  -F "_method=PUT" \
  -F "image=@/path/to/new-image.png"
```

> **Tip:** Beberapa HTTP client tidak mendukung PUT dengan form-data. Gunakan `POST` dengan field `_method=PUT` sebagai workaround (sudah didukung di route).

### Contoh Response (200 OK)

```json
{
  "success": true,
  "message": "Class updated.",
  "data": {
    "id": 5,
    "name": "Body Combat",
    "price": "135000.00",
    "is_active": false,
    "image_url": "http://localhost:8000/storage/classes/new-abc456.png",
    "updated_at": "2026-05-05T09:00:00.000000Z"
  }
}
```

---

## 5. DELETE /classes/{id} — Hapus Kelas

**Akses:** Admin only

> Gambar kelas otomatis ikut dihapus dari storage saat kelas dihapus.

### Contoh Request

```http
DELETE /api/classes/5
Authorization: Bearer {token}
```

### Contoh Response (200 OK)

```json
{
  "success": true,
  "message": "Class deleted."
}
```

---

# BAGIAN 2 — SESSIONS (Cara Trainer Masuk ke Kelas)

> Sesi adalah **jadwal nyata** sebuah kelas. Admin membuat sesi dengan menunjuk trainer yang **sudah terdaftar** di kelas tersebut. Lewat sesi inilah member bisa booking.

---

## Prasyarat: Trainer Harus Terdaftar di Kelas

Sebelum membuat sesi, trainer harus sudah terhubung ke kelas via `class_ids` saat membuat atau mengupdate trainer.

**Cara mendaftarkan trainer ke kelas:**

```http
PUT /api/trainers/{trainer_id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "class_ids": [1, 2, 5]
}
```

Jika trainer belum terdaftar di kelas dan admin mencoba membuat sesi, sistem akan menolak dengan error `422`.

---

## 6. GET /sessions — Daftar Semua Sesi

**Akses:** Public (tidak perlu login)

### Query Parameters

| Parameter | Tipe | Deskripsi |
|-----------|------|-----------|
| `date` | date (YYYY-MM-DD) | Filter sesi pada tanggal tertentu |
| `date_from` | date | Filter sesi mulai dari tanggal ini |
| `date_to` | date | Filter sesi hingga tanggal ini |
| `class_id` | integer | Filter berdasarkan kelas |
| `trainer_id` | integer | Filter berdasarkan trainer |
| `status` | string | `scheduled`, `ongoing`, `completed`, `cancelled` |
| `available` | boolean | Jika `true`, hanya tampilkan sesi yang masih ada slot |
| `per_page` | integer | Jumlah item per halaman (default: `10`) |

> Hasil diurutkan berdasarkan `session_date` dan `start_time` (paling dekat duluan).

### Contoh Request — Cari sesi tersedia minggu ini

```http
GET /api/sessions?date_from=2026-05-05&date_to=2026-05-11&available=true&status=scheduled
```

### Contoh Response (200 OK)

```json
{
  "success": true,
  "message": "Sessions retrieved.",
  "data": [
    {
      "id": 3,
      "class_id": 1,
      "trainer_id": 2,
      "session_date": "2026-05-06",
      "start_time": "07:00:00",
      "end_time": "08:00:00",
      "location": "Studio A",
      "capacity": 20,
      "booked_count": 12,
      "available_slots": 8,
      "price": "100000.00",
      "status": "scheduled",
      "notes": null,
      "gym_class": {
        "id": 1,
        "name": "Zumba Morning",
        "category": "cardio",
        "image_url": "http://localhost:8000/storage/classes/zumba.jpg"
      },
      "trainer": {
        "id": 2,
        "name": "Sari Wulandari",
        "specialization": "Yoga & Pilates"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 10,
    "total": 15
  }
}
```

---

## 7. GET /sessions/{id} — Detail Sesi

**Akses:** Public

### Contoh Request

```http
GET /api/sessions/3
```

### Contoh Response (200 OK)

```json
{
  "success": true,
  "message": "Session detail retrieved.",
  "data": {
    "id": 3,
    "session_date": "2026-05-06",
    "start_time": "07:00:00",
    "end_time": "08:00:00",
    "location": "Studio A",
    "capacity": 20,
    "booked_count": 12,
    "available_slots": 8,
    "price": "100000.00",
    "status": "scheduled",
    "notes": null,
    "gym_class": {
      "id": 1,
      "name": "Zumba Morning",
      "category": "cardio",
      "description": "Kelas zumba energetik di pagi hari.",
      "image_url": "http://localhost:8000/storage/classes/zumba.jpg"
    },
    "trainer": {
      "id": 2,
      "name": "Sari Wulandari",
      "specialization": "Yoga & Pilates",
      "bio": "Instruktur yoga berpengalaman..."
    }
  }
}
```

---

## 8. POST /sessions — Buat Sesi Baru

**Akses:** Admin only  
**Content-Type:** `application/json`

### Request Body

| Field | Tipe | Wajib | Validasi |
|-------|------|-------|---------|
| `class_id` | integer | Ya | ID kelas yang ada |
| `trainer_id` | integer | Ya | ID trainer yang sudah terdaftar di kelas tersebut |
| `session_date` | date | Ya | Format `YYYY-MM-DD`, tidak boleh tanggal lampau |
| `start_time` | string | Ya | Format `HH:MM` (24 jam) |
| `end_time` | string | Ya | Format `HH:MM`, harus setelah `start_time` |
| `location` | string | Ya | Maks 100 karakter |
| `capacity` | integer | Tidak | Minimal 1. Jika kosong, pakai `max_capacity` dari kelas |
| `price` | numeric | Tidak | Minimal 0. Jika kosong, pakai `price` dari kelas |
| `notes` | string | Tidak | Catatan tambahan |

**Validasi otomatis oleh sistem:**
- Trainer wajib sudah terdaftar mengajar kelas tersebut
- Sistem mendeteksi bentrok jadwal trainer (overlap waktu di tanggal yang sama)

### Contoh Request

```http
POST /api/sessions
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "class_id": 1,
  "trainer_id": 2,
  "session_date": "2026-05-10",
  "start_time": "07:00",
  "end_time": "08:00",
  "location": "Studio B",
  "capacity": 15,
  "notes": "Bawa matras sendiri"
}
```

### Contoh Response (201 Created)

```json
{
  "success": true,
  "message": "Session created.",
  "data": {
    "id": 10,
    "class_id": 1,
    "trainer_id": 2,
    "session_date": "2026-05-10",
    "start_time": "07:00:00",
    "end_time": "08:00:00",
    "location": "Studio B",
    "capacity": 15,
    "booked_count": 0,
    "available_slots": 15,
    "price": "100000.00",
    "status": "scheduled",
    "notes": "Bawa matras sendiri",
    "gym_class": { "id": 1, "name": "Zumba Morning" },
    "trainer": { "id": 2, "name": "Sari Wulandari" },
    "created_at": "2026-05-05T10:00:00.000000Z"
  }
}
```

### Contoh Response Error — Trainer belum terdaftar di kelas (422)

```json
{
  "success": false,
  "message": "Trainer tidak terdaftar mengajar kelas ini.",
  "errors": {
    "trainer_id": ["Trainer tidak terdaftar mengajar kelas ini."]
  }
}
```

### Contoh Response Error — Bentrok jadwal trainer (422)

```json
{
  "success": false,
  "message": "Trainer memiliki bentrok jadwal pada waktu tersebut.",
  "errors": {
    "start_time": ["Bentrok jadwal trainer."]
  }
}
```

---

## 9. PUT /sessions/{id} — Update Sesi

**Akses:** Admin only  
**Content-Type:** `application/json`

> **Perhatian:** Jika sesi sudah memiliki booking aktif (`booked_count > 0`), hanya field `notes` dan `location` yang bisa diubah.

### Request Body (Sesi Belum Ada Booking)

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `class_id` | integer | Ganti kelas |
| `trainer_id` | integer | Ganti trainer |
| `session_date` | date | Ganti tanggal (tidak boleh lampau) |
| `start_time` | string | Format `HH:MM` |
| `end_time` | string | Format `HH:MM` |
| `location` | string | Lokasi, maks 100 karakter |
| `capacity` | integer | Kapasitas |
| `price` | numeric | Harga |
| `status` | string | `scheduled`, `ongoing`, `completed`, `cancelled` |
| `notes` | string | Catatan |

### Request Body (Sesi Sudah Ada Booking — Terbatas)

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `location` | string | Lokasi baru |
| `notes` | string | Catatan baru |

### Contoh Request

```http
PUT /api/sessions/10
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "location": "Studio C",
  "notes": "Pindah studio, matras disediakan"
}
```

### Contoh Response — Sesi dengan booking aktif (200 OK)

```json
{
  "success": true,
  "message": "Session updated (limited fields karena ada booking aktif).",
  "data": {
    "id": 10,
    "location": "Studio C",
    "notes": "Pindah studio, matras disediakan"
  }
}
```

---

## 10. DELETE /sessions/{id} — Hapus Sesi

**Akses:** Admin only

> Menghapus sesi akan otomatis **membatalkan semua booking** yang statusnya `pending` atau `confirmed`. Booking yang sudah dibayar (`payment_status: paid`) otomatis berubah menjadi `refunded`.

### Contoh Request

```http
DELETE /api/sessions/10
Authorization: Bearer {admin_token}
```

### Contoh Response (200 OK)

```json
{
  "success": true,
  "message": "Session deleted (semua booking terkait dibatalkan)."
}
```

---

# BAGIAN 3 — BOOKINGS (Cara Member Join Kelas)

> Member melakukan booking pada **sesi** (bukan langsung ke kelas). Setiap booking memiliki kode unik dan diproses dengan database transaction untuk mencegah overbooking.

---

## 11. GET /bookings — Daftar Booking

**Akses:** Login wajib (semua role)

**Data yang ditampilkan berbeda per role:**
- **Admin:** Melihat semua booking dari semua member
- **Trainer:** Hanya melihat booking pada sesi yang ia ampu
- **Member:** Hanya melihat booking milik sendiri

### Query Parameters

| Parameter | Tipe | Deskripsi |
|-----------|------|-----------|
| `status` | string | Filter: `pending`, `confirmed`, `cancelled`, `completed` |
| `payment_status` | string | Filter: `unpaid`, `paid`, `refunded` |
| `date_from` | date | Filter booking mulai dari tanggal ini |
| `date_to` | date | Filter booking hingga tanggal ini |
| `session_id` | integer | Filter berdasarkan sesi tertentu |
| `per_page` | integer | Default: `10` |

### Contoh Request

```http
GET /api/bookings?status=confirmed&payment_status=paid
Authorization: Bearer {token}
```

### Contoh Response (200 OK)

```json
{
  "success": true,
  "message": "Bookings retrieved.",
  "data": [
    {
      "id": 7,
      "booking_code": "BK-20260505-A3FZ",
      "booking_date": "2026-05-05T10:30:00.000000Z",
      "status": "confirmed",
      "payment_method": "transfer",
      "payment_status": "paid",
      "total_price": "100000.00",
      "notes": null,
      "cancelled_at": null,
      "user": { "id": 3, "name": "Budi Santoso", "email": "budi@email.com" },
      "session": {
        "id": 3,
        "session_date": "2026-05-06",
        "start_time": "07:00:00",
        "end_time": "08:00:00",
        "gym_class": { "id": 1, "name": "Zumba Morning", "category": "cardio" },
        "trainer": { "id": 2, "name": "Sari Wulandari" }
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

---

## 12. GET /bookings/{id} — Detail Booking

**Akses:** Login wajib

> Admin bisa melihat semua booking. Member hanya bisa melihat booking miliknya. Trainer hanya bisa melihat booking pada sesinya.

### Contoh Request

```http
GET /api/bookings/7
Authorization: Bearer {token}
```

### Contoh Response (200 OK)

```json
{
  "success": true,
  "message": "Booking detail retrieved.",
  "data": {
    "id": 7,
    "booking_code": "BK-20260505-A3FZ",
    "booking_date": "2026-05-05T10:30:00.000000Z",
    "status": "confirmed",
    "payment_method": "transfer",
    "payment_status": "paid",
    "total_price": "100000.00",
    "notes": null,
    "user": { "id": 3, "name": "Budi Santoso", "email": "budi@email.com", "phone": "081234567890" },
    "session": {
      "id": 3,
      "session_date": "2026-05-06",
      "start_time": "07:00:00",
      "end_time": "08:00:00",
      "gym_class": { "id": 1, "name": "Zumba Morning", "category": "cardio", "description": "..." },
      "trainer": { "id": 2, "name": "Sari Wulandari", "specialization": "Yoga & Pilates" }
    }
  }
}
```

---

## 13. POST /bookings — Buat Booking (Member Join Kelas)

**Akses:** Member only  
**Content-Type:** `application/json`

**Validasi otomatis oleh sistem:**
- Sesi harus berstatus `scheduled`
- Tanggal sesi belum lampau
- Kapasitas sesi belum penuh
- Member belum punya booking aktif (`pending`/`confirmed`) di sesi yang sama

### Request Body

| Field | Tipe | Wajib | Validasi |
|-------|------|-------|---------|
| `session_id` | integer | Ya | ID sesi yang ada dan berstatus `scheduled` |
| `payment_method` | string | Ya | `cash`, `transfer`, `ewallet` |
| `notes` | string | Tidak | Catatan tambahan |

### Contoh Request

```http
POST /api/bookings
Authorization: Bearer {member_token}
Content-Type: application/json

{
  "session_id": 3,
  "payment_method": "transfer",
  "notes": "Alergi parfum, mohon ventilasi cukup"
}
```

### Contoh Response (201 Created)

```json
{
  "success": true,
  "message": "Booking berhasil dibuat.",
  "data": {
    "id": 8,
    "booking_code": "BK-20260505-X7QP",
    "booking_date": "2026-05-05T10:45:00.000000Z",
    "status": "pending",
    "payment_method": "transfer",
    "payment_status": "unpaid",
    "total_price": "100000.00",
    "notes": "Alergi parfum, mohon ventilasi cukup",
    "session": {
      "id": 3,
      "session_date": "2026-05-06",
      "start_time": "07:00:00",
      "gym_class": { "id": 1, "name": "Zumba Morning" },
      "trainer": { "id": 2, "name": "Sari Wulandari" }
    }
  }
}
```

### Contoh Response Error — Sesi penuh (409)

```json
{
  "success": false,
  "message": "Sesi sudah penuh."
}
```

### Contoh Response Error — Sudah punya booking aktif (409)

```json
{
  "success": false,
  "message": "Anda sudah memiliki booking aktif untuk sesi ini."
}
```

### Contoh Response Error — Sesi tidak tersedia / sudah lewat (422)

```json
{
  "success": false,
  "message": "Sesi tidak tersedia untuk booking."
}
```

---

## 14. PUT /bookings/{id} — Update Booking

**Akses:** Admin atau Member (login wajib)

**Perbedaan akses per role:**

### Jika Member
Hanya bisa mengubah `notes`, dan hanya jika status booking masih `pending`.

```http
PUT /api/bookings/8
Authorization: Bearer {member_token}
Content-Type: application/json

{
  "notes": "Catatan diperbarui"
}
```

### Jika Admin
Bisa mengubah `status`, `payment_method`, `payment_status`, dan `notes`.

```http
PUT /api/bookings/8
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "status": "confirmed",
  "payment_status": "paid"
}
```

### Field yang bisa diupdate oleh Admin

| Field | Tipe | Nilai yang Valid |
|-------|------|-----------------|
| `status` | string | `pending`, `confirmed`, `cancelled`, `completed` |
| `payment_method` | string | `cash`, `transfer`, `ewallet` |
| `payment_status` | string | `unpaid`, `paid`, `refunded` |
| `notes` | string | Teks bebas |

### Contoh Response (200 OK)

```json
{
  "success": true,
  "message": "Booking updated.",
  "data": {
    "id": 8,
    "booking_code": "BK-20260505-X7QP",
    "status": "confirmed",
    "payment_status": "paid",
    "updated_at": "2026-05-05T11:00:00.000000Z"
  }
}
```

### Contoh Response Error — Member coba update booking bukan miliknya (403)

```json
{
  "message": "Anda tidak memiliki akses ke booking ini."
}
```

---

## 15. DELETE /bookings/{id} — Batalkan Booking

**Akses:** Admin atau Member (login wajib)

**Aturan pembatalan:**
- **Trainer:** Tidak bisa membatalkan booking sama sekali
- **Member:** Hanya bisa membatalkan **minimal 24 jam sebelum sesi dimulai**
- **Admin:** Bisa membatalkan kapan saja tanpa batasan waktu
- Jika `payment_status` sudah `paid`, otomatis berubah menjadi `refunded`
- `booked_count` pada sesi otomatis berkurang 1

### Contoh Request — Member membatalkan

```http
DELETE /api/bookings/8
Authorization: Bearer {member_token}
```

### Contoh Request — Admin membatalkan dengan alasan

```http
DELETE /api/bookings/8
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "cancelled_reason": "Member mengajukan pembatalan via CS"
}
```

### Contoh Response (200 OK)

```json
{
  "success": true,
  "message": "Booking dibatalkan."
}
```

### Contoh Response Error — Kurang dari 24 jam (403)

```json
{
  "success": false,
  "message": "Pembatalan hanya bisa dilakukan minimal 24 jam sebelum sesi."
}
```

---

# Ringkasan Akses Per Role

## Classes

| Endpoint | Guest | Member | Trainer | Admin |
|----------|-------|--------|---------|-------|
| GET /classes | ✅ | ✅ | ✅ | ✅ |
| GET /classes/{id} | ✅ | ✅ | ✅ | ✅ |
| POST /classes | ❌ | ❌ | ❌ | ✅ |
| PUT /classes/{id} | ❌ | ❌ | ❌ | ✅ |
| DELETE /classes/{id} | ❌ | ❌ | ❌ | ✅ |

## Sessions

| Endpoint | Guest | Member | Trainer | Admin |
|----------|-------|--------|---------|-------|
| GET /sessions | ✅ | ✅ | ✅ | ✅ |
| GET /sessions/{id} | ✅ | ✅ | ✅ | ✅ |
| POST /sessions | ❌ | ❌ | ❌ | ✅ |
| PUT /sessions/{id} | ❌ | ❌ | ❌ | ✅ |
| DELETE /sessions/{id} | ❌ | ❌ | ❌ | ✅ |

## Bookings

| Endpoint | Guest | Member | Trainer | Admin |
|----------|-------|--------|---------|-------|
| GET /bookings | ❌ | ✅ (milik sendiri) | ✅ (sesinya) | ✅ (semua) |
| GET /bookings/{id} | ❌ | ✅ (milik sendiri) | ✅ (sesinya) | ✅ |
| POST /bookings | ❌ | ✅ | ❌ | ❌ |
| PUT /bookings/{id} | ❌ | ✅ (notes, pending only) | ❌ | ✅ (semua field) |
| DELETE /bookings/{id} | ❌ | ✅ (min. 24 jam sebelum sesi) | ❌ | ✅ (kapan saja) |

---

# Kode Status HTTP

| Kode | Arti |
|------|------|
| 200 | OK — Request berhasil |
| 201 | Created — Data berhasil dibuat |
| 401 | Unauthorized — Token tidak ada atau tidak valid |
| 403 | Forbidden — Role tidak punya akses / aturan bisnis dilanggar |
| 404 | Not Found — Data tidak ditemukan |
| 409 | Conflict — Sesi penuh / sudah booking / duplikasi |
| 422 | Unprocessable Entity — Validasi gagal |
