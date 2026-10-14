# SPESIFIKASI TEKNIS & DOKUMEN PERANCANGAN SISTEM
## UTS Web Berita User-Generated Content (UGC)
**Arsitektur:** Decoupled Architecture (Laravel 13 REST API Backend + Sneat Vuetify Vue 3 Frontend)  
**Lingkungan Lokal:** Laravel Herd (`http://web_uts.test`) & Vite (`http://localhost:5173`)

---

## 1. SPESIFIKASI LINGKUNGAN & TEKNOLOGI

### Backend (Laravel 13 REST API)
- **Framework:** Laravel 13
- **Autentikasi API:** Laravel Sanctum (Bearer Token)
- **Web Server & Domain Lokal:** Laravel Herd (`http://web_uts.test`)
- **Database Engine:** MySQL (`web_berita_db`)
- **File Storage:** Local Disk Public Symlink (`storage/app/public/posts`)

### Frontend (Sneat Vuetify Vue.js Laravel Admin Template)
- **Framework:** Vue 3 (Composition API `<script setup>`) + TypeScript / JavaScript
- **UI Framework:** Vuetify 3 (Material Design Components)
- **Template Base:** Sneat Vuetify Admin Template
- **Build Tool:** Vite
- **State Management:** Pinia Store
- **Routing:** Vue Router (dengan Navigation Guard)
- **HTTP Client:** Axios (dikustomisasi dengan Request & Response Interceptors)

---

## 2. ARSITEKTUR DATABASE & STRUKTUR TABEL

### Diagram Relasi Entitas (ERD)
[Users] 1 <--- Memiliki banyak --- * [Posts] * --- Milik satu ---> 1 [Categories]

### Detail Struktur Tabel Database

#### 1. Tabel `users`
- `id` (BigInteger, Primary Key, Auto Increment)
- `name` (String, Required)
- `email` (String, Unique, Required)
- `password` (String, Hashed, Required)
- `timestamps` (`created_at`, `updated_at`)

#### 2. Tabel `categories`
- `id` (BigInteger, Primary Key, Auto Increment)
- `name` (String, Required)
- `slug` (String, Unique, Required)
- `timestamps` (`created_at`, `updated_at`)

#### 3. Tabel `posts`
- `id` (BigInteger, Primary Key, Auto Increment)
- `user_id` (Foreign Key -> `users.id`, `onDelete('cascade')`)
- `category_id` (Foreign Key -> `categories.id`, `onDelete('cascade')`)
- `title` (String, Required)
- `slug` (String, Unique, Required)
- `content` (LongText, Required)
- `image` (String, Nullable) — Menyimpan path/URL publik berkas gambar.
- `status` (Enum: `'published'`, `'draft'`, Default: `'published'`)
- `timestamps` (`created_at`, `updated_at`)

---

## 3. MATRIX OTORISASI & ATURAN HAK AKSES (POLICY)

| Aksi / Fitur | Endpoint API | Method | Hak Akses | Logika Otorisasi & Respon Sistem |
| :--- | :--- | :--- | :--- | :--- |
| **Registrasi Akun** | `/api/register` | `POST` | Publik | Membuat akun baru & mengembalikan Bearer Token. |
| **Login Akun** | `/api/login` | `POST` | Publik | Memvalidasi kredensial & mengembalikan Bearer Token. |
| **Jelajah Berita** | `/api/posts` | `GET` | Publik | Hanya menampilkan berita status `'published'`. Mendukung query `?category={slug}` dan `?search={keyword}`. |
| **Detail Berita** | `/api/posts/{slug}` | `GET` | Publik | Menampilkan isi lengkap artikel berdasarkan slug. |
| **Daftar Kategori** | `/api/categories` | `GET` | Publik | Menampilkan seluruh daftar kategori untuk filter. |
| **Profil Saya** | `/api/me` | `GET` | Auth User | Mengambil data pengguna aktif dari token. |
| **Keluar (Logout)** | `/api/logout` | `POST` | Auth User | Menghapus token aktif dari database. |
| **Berita Saya** | `/api/my-posts` | `GET` | Auth User | Menampilkan daftar seluruh berita (draft & published) milik user aktif. |
| **Tulis Berita** | `/api/posts` | `POST` | Auth User | Membuat berita baru. `user_id` otomatis diset dari `auth()->id()`. |
| **Ubah Berita** | `/api/posts/{id}` | `POST` / `PUT` | Pemilik Data | Memeriksa `PostPolicy::update`. Jika `post.user_id !== auth()->id()`, kembalikan `403 Forbidden`. |
| **Hapus Berita** | `/api/posts/{id}` | `DELETE` | Pemilik Data | Memeriksa `PostPolicy::delete`. Jika `post.user_id !== auth()->id()`, kembalikan `403 Forbidden`. |

---

## 4. STANDAR KEAMANAN & PENANGANAN DATA

1. **Mass Assignment Protection:**
   Model Eloquent (`User`, `Category`, `Post`) mengisolasi atribut yang dapat diisi melalui properti `$fillable`.
2. **Form Request & Validasi Input:**
   Validasi input ketat pada level API untuk mencegah XSS dan SQL Injection.
3. **Penyimpanan Berkas Gambar:**
   - Ekstensi: `jpeg`, `png`, `jpg`, `webp` (Maks: `2048 KB`).
   - Direktori: `storage/app/public/posts` dipublikasikan via `php artisan storage:link`.
4. **Rate Limiting:**
   Middleware `throttle:6,1` pada rute `/api/login` dan `/api/register`.
5. **Handling Multipart Form-Data Update:**
   Proses ubah berita dengan gambar baru menggunakan method `POST` dengan menyertakan atribut `_method = PUT` dalam `FormData`.

---

## 5. INTEGRASI TEMPLATE SNEAT VUETIFY & ROUTER FRONTEND

### A. Struktur Layout Sneat
- **Layout Utama:** `resources/js/layouts/DefaultLayoutWithVerticalNav.vue` (digunakan untuk area Dasbor / Kelola Berita Penulis).
- **Layout Blank:** `resources/js/layouts/blank.vue` (digunakan untuk halaman Login, Register, dan Tampilan Publik).
- **Navigasi Sidebar:** Mengonfigurasi berkas di `resources/js/navigation/vertical/` untuk menambahkan menu "Berita Saya" dan "Tulis Berita".

### B. Axios Instance (`resources/js/plugins/axios.ts` / `axios.js`)
- **Base URL:** `http://web_uts.test/api`
- **Request Interceptor:** Membaca `access_token` dari `localStorage` dan menyisipkan header `Authorization: Bearer <token>`.
- **Response Interceptor:** Jika menerima HTTP `401 Unauthorized`, hapus token dan alihkan ke rute `/login`.

### C. Pinia Auth Store (`resources/js/stores/auth.ts`)
- **State:** Data `user` dan string `token`.
- **Actions:** `login()`, `register()`, dan `logout()`.

### D. Router Guard (`resources/js/plugins/router/index.ts`)
- Rute terproteksi (`meta: { requiresAuth: true }`): `/my-posts`, `/posts/create`, `/posts/edit/:slug`.
- Navigasi otomatis dialihkan ke `/login` jika token tidak valid.

---

## 6. SPESIFIKASI TAMPILAN UI/UX DENGAN KOMPONEN VUETIFY

### A. Halaman Publik (Pembaca)
1. **Beranda / Jelajah Berita:**
   - `VTextField` untuk pencarian berita.
   - `VChipGroup` & `VChip` untuk filter tombol kategori.
   - `VRow` & `VCol` memuat `VCard` berita (VImg untuk gambar sampul, VCardTitle, VCardText, dan VBtn "Baca Selengkapnya").
2. **Detail Berita:**
   - `VCard` besar memuat gambar utama, `VChip` kategori, nama penulis, tanggal terbit, dan konten berita.

### B. Dasbor Pengguna (Penulis)
1. **Kelola Berita Saya:**
   - `VDataTable` atau `VTable` untuk menampilkan data berita.
   - `VChip` status indikator berwarna hijau (`published`) atau abu-abu (`draft`).
   - `VBtn` ikon aksi untuk "Ubah" dan "Hapus".
2. **Form Tambah & Ubah Berita:**
   - `VTextField` untuk Judul Berita.
   - `VSelect` untuk Kategori Berita.
   - `VTextarea` untuk Isi Berita.
   - `VRadioGroup` & `VRadio` untuk Pilihan Status.
   - `VFileInput` untuk Upload Gambar Sampul + Komponen `VImg` untuk Live Preview.
3. **Modal Hapus Berita:**
   - `VDialog` konfirmasi hapus data dengan tombol Batal dan Hapus.

### C. Feedback UI & State
- `VSnackbar` untuk Toast Notification (Pesan Sukses/Gagal).
- `VProgressCircular` / `VSkeletonLoader` untuk indikator memuat data dari API.
- Error message bawaan Vuetify di bawah komponen form input.