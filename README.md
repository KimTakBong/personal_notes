## Plugin / Library yang Digunakan

- Laravel (framework utama)
- Laravel Breeze/Jetstream (digunakan untuk auth)
- jQuery (AJAX & DOM, untuk interaksi dinamis di halaman)
- Tailwind CSS (styling UI)
- CKEditor (rich text editor untuk catatan)
- Vite (build tool untuk asset frontend)

Library lain dapat dilihat di file `composer.json` (PHP) dan `package.json` (JS).

# Cara Install & Menjalankan

1. **Clone repository & masuk folder**
   ```bash
   git clone https://github.com/KimTakBong/personal_notes
   cd personal_notes
   ```

2. **Install dependency PHP & JS**
   ```bash
   composer install
   npm install
   ```

3. **Copy file .env & generate key**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Set database di .env**
   - Edit DB_DATABASE, DB_USERNAME, DB_PASSWORD sesuai database lokal Anda

5. **Jalankan migrasi & seeder**
   ```bash
   php artisan migrate --seed
   ```

6. **Jalankan server Laravel & Vite**
   ```bash
   php artisan serve
   npm run dev
   ```

7. **Akses aplikasi**
   - Buka browser ke http://localhost:8000/notes


# Struktur Database & Penjelasan Field

### 1. users
| Field      | Tipe Data | Keterangan                |
|----------- |-----------|---------------------------|
| id         | UUID (PK) | Primary key user          |
| name       | string    | Nama user                 |
| email      | string    | Email user (unique)       |
| password   | string    | Password (hash)           |
| timestamps | datetime  | created_at, updated_at    |

### 2. notes
| Field      | Tipe Data | Keterangan                        |
|----------- |-----------|-----------------------------------|
| id         | UUID (PK) | Primary key note                  |
| user_id    | UUID (FK) | Pemilik note (relasi ke users)    |
| title      | string    | Judul note                        |
| content    | text      | Isi note                          |
| is_public  | boolean   | Status public/private             |
| timestamps | datetime  | created_at, updated_at            |

### 3. note_user (pivot/shared_notes)
| Field      | Tipe Data | Keterangan                                |
|----------- |-----------|-------------------------------------------|
| id         | UUID (PK) | Primary key                               |
| note_id    | UUID (FK) | Relasi ke notes                           |
| user_id    | UUID (FK) | User yang menerima share (relasi ke users)|
| is_read    | boolean   | Status sudah dibaca/belum (0/1)           |
| is_updated | boolean   | Status note diupdate (0/1)                |
| timestamps | datetime  | created_at, updated_at                    |

### 4. comments
| Field         | Tipe Data | Keterangan                                 |
|-------------- |-----------|--------------------------------------------|
| id            | UUID (PK) | Primary key                                |
| note_id       | UUID (FK) | Relasi ke notes                            |
| user_id       | UUID (FK) | Pembuat komentar (relasi ke users)         |
| content       | text      | Isi komentar                               |
| is_read_owner | boolean   | Status sudah dibaca owner (0/1)            |
| is_read_shared| boolean   | Status sudah dibaca penerima share (0/1)   |
| timestamps    | datetime  | created_at, updated_at                     |


# Fitur Aplikasi Personal Notes

## 1. Autentikasi & User
- Register, login, logout (Laravel Breeze/Jetstream)
- Redirect otomatis ke halaman /notes setelah login/register
- Edit profile (nama, email) via modal
- Hapus akun (dengan konfirmasi password)

## 2. Catatan (Notes)
- CRUD catatan: buat, edit, hapus, lihat detail
- Edit note bisa langsung dari halaman detail (tombol Edit, modal popup)
- Modal create/edit note, form validasi lengkap
- Setiap note bisa public/private (toggle switch di detail)
- Public note bisa diakses siapa saja lewat URL khusus
- Private note hanya bisa diakses owner & user yang di-share
- Rich text editor (CKEditor) untuk isi catatan
- Responsive UI (Tailwind CSS)

## 3. Share Notes
- Share note ke user lain via email (autocomplete/suggestion, keyboard support: panah/tab/enter)
- Daftar user yang menerima share tampil di detail note
- Bisa unshare (hapus akses) user tertentu, context-aware redirect
- Di halaman index, tab khusus untuk notes yang di-share ke user
- Tombol "Detail" untuk langsung buka halaman detail note yang di-share

## 4. Komentar
- Setiap note bisa dikomentari (owner, shared user, public jika public)
- Komentar tampil di detail note (real-time refresh setelah submit, AJAX)
- Notifikasi badge jika ada komentar baru (owner/shared)
- Komentar otomatis di-mark as read saat dibuka

## 5. Notifikasi & UX
- Notifikasi "new" jika note baru di-share ke user
- Notifikasi "updated" jika note yang di-share diupdate owner
- Badge notifikasi komentar baru di tab dan tombol
- Modal untuk edit profile, create/edit note, komentar
- Semua aksi CRUD, share, komentar, dsb. sudah AJAX/UX friendly (tanpa reload halaman utama)
- Redirect context-aware (misal: unshare dari detail tetap di detail)

## 6. Fitur Lain & Teknologi
- Email suggestion pada form share mendukung keyboard (panah/tab/enter)
- Semua relasi antar tabel pakai UUID (bukan auto increment)
- Validasi form lengkap (server & client)
- Responsive UI (Tailwind CSS)
- Vite untuk build asset frontend
- jQuery untuk AJAX & interaksi dinamis
- Semua fitur utama sudah didokumentasikan di README

---