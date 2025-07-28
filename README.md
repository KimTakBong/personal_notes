## Plugin / Library yang Digunakan

- Laravel (framework utama)
- Laravel Breeze/Jetstream (digunakan untuk auth)
- jQuery (AJAX & DOM, untuk interaksi dinamis di halaman)
- Tailwind CSS (styling UI)
- CKEditor (rich text editor untuk catatan)
- Vite (build tool untuk asset frontend)

Library lain dapat dilihat di file `composer.json` (PHP) dan `package.json` (JS).

# Struktur Database & Penjelasan Field

1. users
   - id: primary key user
   - name/email/password: data user
   - timestamps: waktu buat/update

2. notes
   - id: primary key note
   - user_id: pemilik note (relasi ke users)
   - title: judul note
   - content: isi note
   - timestamps: waktu buat/update

3. note_user (pivot/shared_notes)
   - id: primary key
   - note_id: relasi ke notes
   - user_id: user yang menerima share
   - is_read: status sudah dibaca/belum (0/1)
   - is_updated: status note diupdate (0/1)
   - timestamps: waktu buat/update

4. comments
   - id: primary key
   - note_id: relasi ke notes
   - user_id: pembuat komentar
   - content: isi komentar
   - is_read_owner: status sudah dibaca owner (0/1)
   - is_read_shared: status sudah dibaca penerima share (0/1)
   - timestamps: waktu buat/update


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