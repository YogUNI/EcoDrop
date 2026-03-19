# 🌱 EcoDrop

**Platform Manajemen Sampah Berbasis Reward**

Platform digital yang memudahkan pengguna untuk mengelola sampah mereka sambil mendapatkan poin reward yang dapat ditukarkan. Dibangun dengan sistem multi-role yang tangguh dan fitur komunikasi interaktif.

---

## 📋 Daftar Isi

- [Tentang Proyek](#tentang-proyek)
- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Instalasi](#instalasi)
- [Penggunaan](#penggunaan)
- [Struktur Database](#struktur-database)
- [Tim Pengembang](#tim-pengembang)
- [Lisensi](#lisensi)

---

## 💡 Tentang Proyek

EcoDrop adalah aplikasi web yang dirancang untuk mendukung gaya hidup ramah lingkungan dengan memberikan reward kepada pengguna yang aktif mengelola sampah. Proyek ini dikembangkan sebagai tugas mata kuliah **Pemrograman Web**.

### Tujuan:
- ♻️ Meningkatkan kesadaran pengelolaan sampah
- 🏆 Memberikan insentif melalui sistem poin reward
- 👨‍💼 Memudahkan alur komunikasi dan verifikasi antara User, Admin, dan Super Admin

---

## ✨ Fitur Utama

### 👤 User (Pengguna Biasa)
- ✅ Register, Login & Edit Profil
- ✅ Dashboard interaktif dengan informasi saldo poin
- ✅ Ajukan setor sampah baru (jenis, berat, tanggal)
- ✅ Riwayat setoran dan pembatalan setoran pending
- ✅ **[NEW]** Fitur Live Chat untuk bertanya atau komplain ke Admin

### 🛡️ Admin (Petugas)
- ✅ Dashboard dengan statistik keseluruhan
- ✅ Verifikasi, kelola (Approve/Reject), dan hapus setoran sampah
- ✅ **[NEW]** Fitur Live Chat Box untuk menangani pesan dari User
- ✅ **[NEW]** Anti-Tabrakan (Race Condition Protection): Mencegah dua admin mengambil chat yang sama secara bersamaan

### 👑 Super Admin (Pemilik Sistem)
- ✅ **[NEW]** Dashboard Monitoring keseluruhan sistem
- ✅ **[NEW]** Verifikasi/Approve pendaftaran akun Admin baru
- ✅ **[NEW]** Notifikasi Email Otomatis: Dikirim ke admin saat akunnya diverifikasi (menggunakan Laravel Queue)
- ✅ **[NEW]** Manajemen User: Fitur Ban/Unban akun user nakal
- ✅ **[NEW]** Activity Logs: Memantau jejak aktivitas para Admin

---

## 🛠️ Tech Stack

| Layer | Teknologi |
|-------|-----------|
| **Frontend** | HTML5, CSS3, Tailwind CSS, Alpine.js |
| **Backend** | PHP Laravel (v11) |
| **Database** | MySQL |
| **Authentication** | Laravel Breeze (Session-based) |
| **Background Jobs** | Laravel Queues & Mailable (Notifikasi Email) |
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

4. **Konfigurasi Database & Email**
   Edit file `.env` dan sesuaikan kredensial database serta pengaturan SMTP Email Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ecodrop
   DB_USERNAME=root
   DB_PASSWORD=

   # Setup Email (Contoh Mailtrap)
   MAIL_MAILER=smtp
   MAIL_HOST=sandbox.smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your_username
   MAIL_PASSWORD=your_password
   ```

5. **Migrasi Database & Seeder**
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Build Assets**
   ```bash
   npm run build
   ```

7. **Jalankan Background Worker (Penting untuk Email)**
   Buka terminal baru dan jalankan untuk memproses antrian email:
   ```bash
   php artisan queue:work
   ```

8. **Jalankan Server**
   Buka terminal lainnya dan jalankan:
   ```bash
   php artisan serve
   ```
   Akses: `http://localhost:8000`

---

## 🚀 Penggunaan

### Akun Demo (Jika Menggunakan Seeder)
- **Super Admin:** `superadmin@example.com` | Pass: `password`
- **Admin:** `admin@example.com` | Pass: `password`
- **User:** `user@example.com` | Pass: `password`

### Workflow Dasar
1. **User** mendaftar, mengajukan setoran sampah, atau memulai chat jika ada kendala.
2. **Admin** menangani setoran (verifikasi poin) dan merespon chat dari user.
3. **Super Admin** memantau log aktivitas, memverifikasi admin baru, dan menindak user nakal.

---

## 📚 Struktur Database Inti

- **`users`**: Data otentikasi (id, name, email, role, is_verified, is_banned, points).
- **`pickups`**: Data transaksi setoran sampah (user_id, type, weight, status, handled_by).
- **`conversations` & `messages`**: Menyimpan room chat dan riwayat pesan User-Admin.
- **`activity_logs`**: Mencatat log aktivitas Admin.
- **`rewards`**: Data penukaran poin.

---

## 👥 Tim Pengembang

| Nama | Role | GitHub |
|------|------|--------|
| Yoga Gusti R | Full Stack | [@YogUNI](https://github.com/YogUNI) |
| M Vicky Haikal | Frontend | - |
| Thomas Setiawan | UI/UX Designer | - |

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

## 📞 Kontak & Support

Untuk pertanyaan atau masukan, silakan buat **Issue** di repository ini atau hubungi tim pengembang.

**Happy Coding! 🚀**