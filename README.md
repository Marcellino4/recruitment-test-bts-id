# BTS.ID Product Management API

REST API untuk manajemen produk dengan autentikasi berbasis token menggunakan Laravel Sanctum.

## Tech Stack

- **Laravel** 12 (PHP 8.2)
- **MySQL** 8.0
- **Laravel Sanctum** -> token-based authentication
- **Docker** -> containerized deployment

## Fitur

- Autentikasi dengan username & password (access token + refresh token)
- CRUD produk dengan pagination, search, dan filter kategori
- Caching response GET produk (60 detik)
- Rate limiting: 3x/menit untuk auth, 1x/5 detik untuk product write
- CORS enabled untuk semua origin
- Timezone Asia/Jakarta

---

## Cara Menjalankan

**Prasyarat:** Docker & Docker Compose terinstall.

1. Copy file environment:
   ```bash
   cp .env.example .env
   ```

2. Generate app key:
   ```bash
   php artisan key:generate
   ```

3. Jalankan Docker:
   ```bash
   docker compose up --build -d
   ```

4. Jalankan migration dan seeder:
   ```bash
   docker compose exec app php artisan migrate --seed --force
   ```

5. Aplikasi berjalan di `http://localhost:8000`

---

## Default Users (Seeder)

| Username   | Password   |
|------------|------------|
| `jhon_doe` | `password` |
| `jane_doe` | `password` |

---

## API Endpoints

Base URL: `http://localhost:8000/api`

### Auth

| Method | Endpoint            | Auth | Deskripsi                   |
|--------|---------------------|------|-----------------------------|
| POST   | `/auth/register`    | -    | Registrasi pengguna baru    |
| POST   | `/auth/login`       | -    | Login dan dapatkan token    |
| POST   | `/auth/refresh`     | -    | Refresh access token        |
| POST   | `/auth/logout`      | Yes  | Logout dan invalidate token |

### Products

| Method | Endpoint            | Auth | Deskripsi             |
|--------|---------------------|------|-----------------------|
| GET    | `/products`         | -    | Ambil semua produk    |
| GET    | `/products/{id}`    | -    | Ambil detail produk   |
| POST   | `/products`         | Yes  | Tambah produk baru    |
| PUT    | `/products/{id}`    | Yes  | Update produk         |
| DELETE | `/products/{id}`    | Yes  | Hapus produk          |

**Query params GET `/products`:**
- `search` -> cari berdasarkan judul
- `category` -> filter berdasarkan kategori
- `limit` -> item per halaman (default: 10, max: 100)
- `page` -> nomor halaman (default: 1)

---

## Contoh Request

### Register
```http
POST /api/auth/register
Content-Type: application/json

{
  "username": "jhon_doe",
  "password": "supersecret",
  "password_confirmation": "supersecret"
}
```

Response `201`:
```json
{
  "status": 201,
  "message": "Registration successful"
}
```

---

### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "username": "jhon_doe",
  "password": "password"
}
```

Response `200`:
```json
{
  "status": 200,
  "authentication_token": "1|abc123...",
  "refresh_token": "xyz789...",
  "user": {
    "id": 1,
    "username": "jhon_doe"
  }
}
```

---

### Refresh Token
```http
POST /api/auth/refresh
Content-Type: application/json

{
  "refresh_token": "xyz789..."
}
```

Response `200`:
```json
{
  "status": 200,
  "authentication_token": "2|newtoken123...",
  "refresh_token": "newrefresh456..."
}
```

---

### Logout
```http
POST /api/auth/logout
Authorization: Bearer 1|abc123...
```

Response `200`:
```json
{
  "status": 200,
  "message": "Logged out successfully"
}
```

---

### GET All Products
```http
GET /api/products?limit=10&page=1
```

Response `200`:
```json
{
  "status": 200,
  "data": [
    {
      "id": 1,
      "title": "Awesome T-Shirt",
      "price": 99.99,
      "description": "High-quality cotton t-shirt",
      "category": "Clothes",
      "images": ["https://example.com/image.jpg"],
      "created_at": "2026-05-25 10:00:00",
      "created_by": "jhon_doe",
      "created_by_id": "1",
      "updated_at": "2026-05-25 10:00:00",
      "updated_by": "jhon_doe",
      "updated_by_id": "1"
    }
  ],
  "meta": {
    "total": 10,
    "per_page": 10,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

### GET Product by ID
```http
GET /api/products/1
```

Response `200`:
```json
{
  "status": 200,
  "data": {
    "id": 1,
    "title": "Awesome T-Shirt",
    "price": 99.99,
    "description": "High-quality cotton t-shirt",
    "category": "Clothes",
    "images": ["https://example.com/image.jpg"],
    "created_at": "2026-05-25 10:00:00",
    "created_by": "jhon_doe",
    "created_by_id": "1",
    "updated_at": "2026-05-25 10:00:00",
    "updated_by": "jhon_doe",
    "updated_by_id": "1"
  }
}
```

---

### POST Create Product
```http
POST /api/products
Content-Type: application/json
Authorization: Bearer 1|abc123...

{
  "title": "Awesome T-Shirt",
  "price": 99.99,
  "description": "High-quality cotton t-shirt",
  "category": "Clothes",
  "images": ["https://example.com/image.jpg"]
}
```

Response `201`:
```json
{
  "status": 201,
  "data": {
    "id": 1,
    "title": "Awesome T-Shirt",
    "price": 99.99,
    "description": "High-quality cotton t-shirt",
    "category": "Clothes",
    "images": ["https://example.com/image.jpg"],
    "created_at": "2026-05-25 10:00:00",
    "created_by": "jhon_doe",
    "created_by_id": "1",
    "updated_at": "2026-05-25 10:00:00",
    "updated_by": "jhon_doe",
    "updated_by_id": "1"
  }
}
```

---

### PUT Update Product
```http
PUT /api/products/1
Content-Type: application/json
Authorization: Bearer 1|abc123...

{
  "title": "Updated T-Shirt",
  "price": 149.99
}
```

Response `200`:
```json
{
  "status": 200,
  "data": {
    "id": 1,
    "title": "Updated T-Shirt",
    "price": 149.99,
    "description": "High-quality cotton t-shirt",
    "category": "Clothes",
    "images": ["https://example.com/image.jpg"],
    "created_at": "2026-05-25 10:00:00",
    "created_by": "jhon_doe",
    "created_by_id": "1",
    "updated_at": "2026-05-25 11:00:00",
    "updated_by": "jhon_doe",
    "updated_by_id": "1"
  }
}
```

---

### DELETE Product
```http
DELETE /api/products/1
Authorization: Bearer 1|abc123...
```

Response `200`:
```json
{
  "status": 200,
  "message": "Product deleted successfully"
}
```

---

## Postman Collection

Import file `docs/postman_collection.json` ke Postman.

---

## Rate Limiting

| Endpoint                     | Limit           |
|------------------------------|-----------------|
| `POST /auth/register`        | 3x per 60 detik |
| `POST /auth/login`           | 3x per 60 detik |
| `POST/PUT/DELETE /products`  | 1x per 5 detik  |
