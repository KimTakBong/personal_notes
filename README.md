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

Setiap field dibuat agar aplikasi bisa: simpan data user, catatan, share ke user lain, dan notifikasi komentar secara efisien.
