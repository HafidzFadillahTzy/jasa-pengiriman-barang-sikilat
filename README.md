# Website Jasa Pengiriman Barang

Website untuk mengelola jasa pengiriman barang dengan fitur CRUD lengkap dan sistem role-based access control.

## Fitur

### 1. Sistem Autentikasi & Role
- **3 Role:**
  - **Admin**: Akses penuh ke semua fitur
  - **Staff**: Dapat mengelola paket, pelanggan, dan pengiriman
  - **Customer**: Hanya dapat melihat pengiriman mereka

### 2. CRUD untuk 3 Fitur Utama

#### a. Paket (Packages)
- Create: Tambah paket baru dengan informasi lengkap
- Read: Lihat daftar semua paket
- Update: Edit informasi paket
- Delete: Hapus paket (dengan validasi)

#### b. Pelanggan (Customers)
- Create: Tambah pelanggan baru
- Read: Lihat daftar semua pelanggan
- Update: Edit informasi pelanggan
- Delete: Hapus pelanggan (dengan validasi)

#### c. Pengiriman (Shipments)
- Create: Buat pengiriman baru dengan tracking number otomatis
- Read: Lihat daftar semua pengiriman
- Update: Edit informasi pengiriman
- Delete: Hapus pengiriman
- View: Detail lengkap pengiriman

## Instalasi

### 1. Persyaratan
- XAMPP (PHP 7.4+ dan MySQL)
- Web Browser

### 2. Setup Database

1. Buka XAMPP Control Panel
2. Start Apache dan MySQL
3. Buka phpMyAdmin (http://localhost/phpmyadmin)
4. Import file `database.sql`:
   - Klik tab "Import"
   - Pilih file `database.sql`
   - Klik "Go"

### 3. Konfigurasi

Edit file `config/database.php` jika diperlukan:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Sesuaikan dengan password MySQL Anda
define('DB_NAME', 'jasa_pengiriman');
```

### 4. Setup Password (Opsional)

Jalankan setup script untuk memastikan password admin berfungsi:
1. Buka browser
2. Akses: `http://localhost/nama-folder/setup.php`
3. Tunggu sampai muncul pesan sukses
4. File ini hanya perlu dijalankan sekali

### 5. Menjalankan Aplikasi

1. Copy folder project ke `C:\xampp\htdocs\` atau direktori htdocs XAMPP Anda
2. Buka browser dan akses: `http://localhost/nama-folder/`

## Login Default

- **Username:** admin
- **Password:** admin123
- **Role:** Admin

## Struktur Folder

```
├── assets/
│   └── css/
│       └── style.css          # Styling utama
├── config/
│   ├── database.php           # Konfigurasi database
│   └── auth.php               # Fungsi autentikasi
├── includes/
│   ├── header.php             # Header navigasi
│   └── footer.php             # Footer
├── packages/                   # CRUD Paket
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   └── delete.php
├── customers/                  # CRUD Pelanggan
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   └── delete.php
├── shipments/                  # CRUD Pengiriman
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   ├── view.php
│   └── delete.php
├── index.php                   # Dashboard
├── login.php                   # Halaman login
├── logout.php                  # Logout
└── database.sql                # File SQL untuk import
```

## Fitur Keamanan

- Password hashing menggunakan `password_hash()` dan `password_verify()`
- Session management untuk autentikasi
- Role-based access control
- SQL injection protection dengan prepared statements
- XSS protection dengan `htmlspecialchars()`

## Teknologi

- **Backend:** PHP 7.4+
- **Database:** MySQL
- **Frontend:** HTML5, CSS3
- **Server:** Apache (XAMPP)

## Catatan

- Pastikan MySQL service berjalan di XAMPP
- Default password MySQL di XAMPP biasanya kosong
- Jika ada error koneksi database, periksa konfigurasi di `config/database.php`

## Lisensi

Project ini dibuat untuk keperluan pembelajaran.

