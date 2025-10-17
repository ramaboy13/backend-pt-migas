# 🛠️ MIGAS FINANCE API — Backend (Laravel 12)

> **Versi:** 1.0.0  
> **Dibangun dengan:** Laravel 12, MySQL, JWT, Spatie Roles, Swagger, Docker  
> **Terakhir Diperbarui:** 17 October 2025

---

## 📘 Deskripsi Proyek
**MIGAS FINANCE API** adalah aplikasi backend berbasis **Laravel 12** yang dirancang untuk mendukung sistem pencatatan keuangan internal **PT Migas**.  
Sistem ini berfungsi sebagai RESTful API yang akan diintegrasikan dengan frontend berbasis **Next.js**, serta dilengkapi dengan autentikasi JWT, manajemen peran (role-based access control), dan dokumentasi API otomatis menggunakan Swagger.

---

## 🧱 Fitur Utama
- 🔐 **Autentikasi JWT Token** (dengan Refresh Token & TTL 3 hari)
- 🧩 **Role Management** menggunakan `spatie/laravel-permission`
- 🧾 **Manajemen Transaksi** (pemasukan, pengeluaran, kategori, laporan PDF)
- ⚙️ **Rate Limiting** untuk mencegah spam request
- 📄 **Swagger API Documentation**
- 🧪 **Unit & Feature Test**
- 🐳 **Docker & Docker Compose Setup**
- 🧠 **Repository Pattern & Service Layer Architecture**
- 💾 **Optimasi Database Connection Pooling**
- 🔍 **Logging & Audit Trail** untuk aktivitas admin

---

## 🧰 Teknologi yang Digunakan
| Kategori | Teknologi |
|:----------|:-----------|
| **Framework** | Laravel 12 |
| **Database** | MySQL 8+ |
| **Auth** | JWT (php-open-source-saver/jwt-auth) |
| **Role & Permission** | Spatie Laravel Permission |
| **PDF Generator** | Barryvdh DomPDF |
| **API Documentation** | L5 Swagger |
| **Testing** | PHPUnit / PestPHP |
| **Containerization** | Docker & Docker Compose |

---

## 🏗️ Arsitektur Proyek
```
app/
 ├── Http/
 │    ├── Controllers/
 │    ├── Middleware/
 │    └── Requests/
 ├── Models/
 ├── Repositories/
 ├── Services/
 ├── Helpers/
 └── Policies/
config/
database/
 ├── migrations/
 ├── seeders/
routes/
 ├── api.php
 ├── admin.php
 └── superadmin.php
tests/
 ├── Feature/
 └── Unit/
```

---

## ⚙️ Instalasi & Setup

### 1️⃣ Clone Repository
```bash
git clone https://github.com/username/migas-finance-api.git
cd migas-finance-api
```

### 2️⃣ Install Dependencies
```bash
composer install
npm install
```

### 3️⃣ Salin & Konfigurasi `.env`
```bash
cp .env.example .env
```
Lalu atur konfigurasi database dan JWT:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=migas_finance
DB_USERNAME=root
DB_PASSWORD=

JWT_TTL=4320
JWT_REFRESH_TTL=10080
```

### 4️⃣ Generate Key & JWT Secret
```bash
php artisan key:generate
php artisan jwt:secret
```

### 5️⃣ Jalankan Migrasi & Seeder
```bash
php artisan migrate --seed
```

### 6️⃣ Jalankan Server Lokal
```bash
php artisan serve
```

API dapat diakses di:
```
http://localhost:8000/api
```

---

## 🔑 Role Management
| Role | Deskripsi | Hak Akses |
|:-----|:-----------|:----------|
| **Super Admin (CEO)** | Akses penuh ke seluruh fitur sistem | Semua endpoint |
| **Admin (Staff)** | Akses terbatas, tidak dapat melakukan registrasi | Endpoint admin saja |

Middleware yang digunakan:
```php
'role:superadmin'
'role:admin'
```

---

## 🔐 Autentikasi JWT
Token akan kedaluwarsa setelah **3 hari** dan dapat diperbarui dengan **refresh token**.  
Semua endpoint dilindungi dengan middleware `auth:api`.

Contoh Header:
```
Authorization: Bearer {token}
```

---

## 🧪 Testing
Menjalankan seluruh unit dan feature test:
```bash
php artisan test
```
Atau menggunakan **PestPHP**:
```bash
./vendor/bin/pest
```

---

## 📘 Swagger API Documentation
Generate dokumentasi API otomatis:
```bash
php artisan l5-swagger:generate
```
Akses dokumentasi di:
```
http://localhost:8000/api/documentation
```

---

## 🐳 Docker Setup

### 1️⃣ Build Container
```bash
docker-compose up -d --build
```

### 2️⃣ Service yang Disertakan
| Service | Port | Keterangan |
|:---------|:------|:------------|
| `nginx` | 8000 | Reverse Proxy |
| `app` | 9000 | Laravel Backend |
| `db` | 3306 | MySQL Database |
| `redis` | 6379 | Cache (opsional) |

---

## 📈 API Rate Limiting
Setiap pengguna dibatasi:
```
60 request / menit per user/IP
```
Dikonfigurasi di `RouteServiceProvider`.

---

## 🧾 License
MIT License © 2025 — **PT Migas Digital Finance System**

---

## 💡 Kontributor
- 🧑‍💻 **Lead Backend Developer:** Ramaboy 

> Dibangun dengan ❤️ menggunakan Laravel 12 dan arsitektur modern yang scalable.