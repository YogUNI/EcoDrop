# 🌱 EcoDrop

**Platform Manajemen Sampah Berbasis Reward**

Platform digital yang memudahkan pengguna untuk mengelola sampah mereka sambil mendapatkan poin reward yang dapat ditukarkan.

---

## 📋 Daftar Isi

- [Tentang Proyek](#tentang-proyek)
- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Instalasi](#instalasi)
- [Penggunaan](#penggunaan)
- [Tim Pengembang](#tim-pengembang)
- [Lisensi](#lisensi)

---

## 💡 Tentang Proyek

EcoDrop adalah aplikasi web yang dirancang untuk mendukung gaya hidup ramah lingkungan dengan memberikan reward kepada pengguna yang aktif mengelola sampah. Proyek ini dikembangkan sebagai tugas mata kuliah **Pemrograman Web**.

### Tujuan:
- ♻️ Meningkatkan kesadaran pengelolaan sampah
- 🏆 Memberikan insentif melalui sistem poin reward
- 👨‍💼 Memudahkan admin dalam verifikasi setoran sampah

---

## ✨ Fitur Utama

### 👤 User (Pengguna)
- ✅ Register & Login
- ✅ Dashboard dengan saldo poin
- ✅ Ajukan setor sampah baru (dengan jenis, berat, tanggal)
- ✅ Lihat riwayat setoran sampah
- ✅ Batalkan setoran yang masih pending
- ✅ Edit profil pengguna

### 👑 Admin
- ✅ Dashboard dengan statistik keseluruhan
- ✅ Verifikasi & kelola semua setoran sampah
- ✅ Approve atau reject setoran dengan poin
- ✅ Hapus data setoran
- ✅ Lihat riwayat semua user

---

## 🛠️ Tech Stack

| Layer | Teknologi |
|-------|-----------|
| **Frontend** | HTML5, CSS3, Tailwind CSS, Alpine.js |
| **Backend** | PHP Laravel (v11) |
| **Database** | MySQL |
| **Authentication** | Laravel Breeze (Session-based) |
| **Version Control** | Git & GitHub |

---

## 📦 Instalasi

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & npm
- MySQL/MariaDB

### Langkah-langkah

1. **Clone Repository**
   ```bash
   git clone https://github.com/YogUNI/EcoDrop.git
   cd EcoDrop
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi Database**
   Edit file `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ecodrop
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Migrasi Database**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build Assets**
   ```bash
   npm run build
   ```

7. **Jalankan Server**
   ```bash
   php artisan serve
   ```

   Akses: `http://localhost:8000`

---

## 🚀 Penggunaan

### Akun Demo

**User:**
- Email: `user@example.com`
- Password: `password`

**Admin:**
- Email: `admin@example.com`
- Password: `password`

### Workflow

1. **Register** akun baru atau login
2. **Ajukan Setor** sampah dengan mengisi form (jenis, berat, tanggal)
3. **Admin Verifikasi** setoran dan berikan poin
4. **User Terima** poin ke saldo mereka
5. **Lihat Riwayat** untuk tracking semua setoran

---

## 📚 Struktur Database

### Tabel `users`
- id (Primary Key)
- name
- email
- password
- role (admin/user)
- points
- timestamps

### Tabel `pickups`
- id (Primary Key)
- user_id (Foreign Key)
- type (Plastik/Kertas)
- weight (Kg)
- pickup_date
- status (pending/approved/rejected)
- points_earned
- timestamps

### Tabel `rewards` (Optional)
- id
- user_id
- points_used
- reward_name
- timestamps

---

## 👥 Tim Pengembang

| Nama | Role | GitHub |
|------|------|--------|
| YogUNIHalo | Full Stack | [@YogUNI](https://github.com/YogUNI) |

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

## 📞 Kontak & Support

Untuk pertanyaan atau masukan, silakan buat **Issue** di repository ini atau hubungi tim pengembang.

**Happy Coding! 🚀**