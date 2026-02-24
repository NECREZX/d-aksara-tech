# D'AKSARA TECH - Platform Jasa Akademik & Project IT

D'AKSARA TECH (sebelumnya *Jasa Joki*) adalah sebuah sistem informasi berbasis web responsif yang menyediakan dan mengelola layanan pembuatan tugas akademik (makalah, presentasi, jurnal, dsb.) serta pengembangan proyek IT (website, aplikasi mobile, desain UI/UX). Sistem ini dirancang untuk memudahkan interaksi, transaksi, dan pertukaran file antara penyedia jasa (Admins) dan pelanggan (Customers).

---

## 🚀 Fitur Utama

### 👨‍💻 Admin Panel
- **Dashboard Interaktif**: Menampilkan statistik pesanan, total pendapatan, dan grafik tren pemesanan interaktif (menggunakan *Chart.js*).
- **Kelola Layanan (CRUD)**: Menambah, mengedit, dan menghapus daftar layanan beserta harganya. Mendukung fitur **Export ke PDF** lengkap dengan format *invoice* dan tanda tangan resmi.
- **Kelola Pengguna**: Melihat atau menghapus daftar pelanggan yang telah mendaftar. Mendukung fitur Export PDF.
- **Manajemen Pesanan (Orders)**:
  - Memverifikasi bukti pembayaran yang diunggah pelanggan.
  - Memperbarui tahapan/status pesanan (`Menunggu` -> `Diproses` -> `Selesai`).
  - Mengunggah / melampirkan file hasil kerja (*output requirement*) langsung ke akun pelanggan.
  - Export laporan/rekap pesanan ke PDF.
- **Konsultasi Langsung (Pesan Masuk)**: Halaman chat bergaya *Split-View* (ala WhatsApp Web) untuk memantau pesan masuk dari pelanggan secara realtime, lengkap dengan indikator *badge* pesan belum dibaca. Admin dapat merespons dan melampirkan progres tugas (*image/document*).
- **Notifikasi Sistem Otomatis**: Secara otomatis mengirimkan notifikasi *real-time* ke akun pelanggan atas setiap perubahan status order mereka.

### 👤 Customer Panel
- **Pemesanan Layanan**: Antarmuka pemesanan yang mudah. Pelanggan dapat menyertakan deskripsi dan file pendukung (referensi tugas).
- **Transparansi Biaya (Itemized Billing)**: Rincian perhitungan total harga ditampilkan secara jelas sejak *Form Order* hingga *Invoice*, termasuk kalkulasi *Biaya Prioritas Deadline (Urgency Fee)* sesuai pilihan waktu (Reguler, Cepat +10%, atau Kilat +50%).
- **Pembayaran QRIS & Rekening**: Halaman *upload* bukti pembayaran dengan dukungan metode scan *barcode* QRIS otomatis maupun transfer bank konvensional.
- **Riwayat Order**: Melacak status pengerjaan pesanan secara terinci.
- **Bantuan & Konsultasi (Chat)**: Fitur "Pesan (Chat)" terintegrasi yang memungkinkan pelanggan berdiskusi dua arah dengan Admin mengenai revisi file atau progres pengerjaan tugas (mendukung lampiran file).
- **Cetak Bukti (Invoice)**: Mencetak struk bukti pesanan / kwitansi resmi bergaya *invoice* untuk pesanan yang telah selesai (berguna untuk laporan pertanggungjawaban/LPJ keuangan).
- **Notifikasi**: Panel notifikasi *in-app* dengan indikator (*badge*) jumlah pesan yang belum dibaca dari Admin atau pembaruan status sistem.
- **Unduh Hasil**: Mengunduh langsung file *output* tugas dari Admin.

### 🌐 Fitur Umum (Landing Page)
- **Tema Dinamis (Dark/Light Mode)**: Tersedia di seluruh tampilan sistem (diaktifkan melalui Navbar).
- **Landing Page Modern**: Memiliki animasi *marquee* daftar layanan (*infinite scroll*) dengan desain yang bersih dan responsif (*glassmorphism*).
- **100% Mobile Responsive**: Tata letak *navbar*, tabel, serta konten dasbor akan menyesuaikan otomatis untuk pengalaman mulus di Desktop, Tablet, hingga Layar Smartphone (iPhone/Android).

---

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 8+ (Gaya bahasa *Native* terstruktur dengan pola *Routing* menyerupai MVC dasar)
- **Database**: MySQL Server
- **Database Interface**: diakses tangguh menggunakan **PHP Data Objects (PDO)** dengan pengamanan *Prepared Statements*.
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Framework CSS**: Bootstrap 5.3 CDN
- **Icons**: FontAwesome 5
- **Data Visualization**: Chart.js (Grafik garis & *Doughnut* pada dasbor).

---

## 📦 Instalasi & Konfigurasi (Local Server)

Ikuti langkah-langkah di bawah ini untuk menjalankan *project* ini di *local server* (XAMPP / Laragon).

1. **Clone repositori ini** atau unduh *source code* sebagai `.zip`. Kemudian ekstrak/simpan ke dalam folder `htdocs` (jika menggunakan XAMPP) atau folder `www` (jika menggunakan Laragon).
   ```bash
   git clone https://github.com/username/jasa-joki-daksara.git
   ```

2. **Setup Database**:
   - Jalankan modul **Apache** dan **MySQL** Anda.
   - Buka browser dan pergi ke `http://localhost/phpmyadmin`.
   - Buat database baru. Anda bisa menamainya `db_jasa_joki` (atau sesuai keinginan asal diatur ulang pada file `config`).
   - Pilih *database* baru tersebut, kemudian klik **Import** dan pilih file `database.sql` yang ada di direktori utama repositori ini.

3. **Konfigurasi Koneksi Database**:
   - Buka file `config/database.php` menggunakan *code editor* Anda (VSCode, dsb).
   - Sesuaikan *credentials* database Anda, terutama pada variabel `$dbname`, `$username`, dan `$password` (Secara *default* di XAMPP, gunakan pengaturan di bawah ini):
   ```php
   $host = "localhost";
   $dbname = "db_jasa_joki";  // Sesuaikan nama dengan yang Anda buat
   $username = "root";        // Default XAMPP/Laragon
   $password = "";            // Kosongkan jika tanpa password
   ```

4. **Jalankan Aplikasi Web**:
   - Buka browser dan akses alamat atau URL folder *project* Anda:
   ```text
   http://localhost/namakloningdirektori
   ```
   *Contoh: `http://localhost/jasa-joki-daksara`*

---

## 🔑 Akun Default (Testing & Administrasi)

Untuk keperluan *testing* dan evaluasi awal, Anda dapat masuk melalui halaman **Login** menggunakan akun administrator bawaan berikut:

- **Email**: `admin@admin.com`
- **Password**: `admin123`

Untuk pengujian sisi *Customer*, Anda bebas melakukan pendaftaran (*Register*) akun baru kapan pun dari halaman awal aplikasi.

---

## 📂 Struktur Direktori (*Codebase*)

```text
/
├── assets/             # Berisi file CSS kustom, JavaScript pendukung, gambar QRIS, dan aset web statis logo pendukung.
├── config/             # Inti konfigurasi sistem (khusus file koneksi database).
├── controllers/        # Modul/logika inti penanganan Form (Contoh: AuthController untuk Register/Login).
├── uploads/            # Direktori penyimpanan file dinamis:
│   ├── messages/       # Tempat menyimpan gambar dan file dokumen lampiran yang dikirim via Chat (Konsultasi).
│   ├── payments/       # Tempat menyimpan gambar unggahan "Bukti Transfer" pelanggan.
│   ├── references/     # Tempat menyimpan file referensi pengerjaan awal bawaan dari pelanggan.
│   └── results/        # Tempat menyimpan file hasil kerja final (Output) yang diunggah oleh Pihak Admin.
├── views/              # Berisi seluruh berkas GUI (Antarmuka Pengguna/Frontend):
│   ├── admin/          # Folder tampilan Dasbor & CRUD bagian backoffice Admin.
│   ├── auth/           # Formulir Login & Registrasi pengguna.
│   ├── customer/       # Folder tampilan Dasbor Pelanggan, Pemesanan, dan Invoice.
│   ├── layouts/        # Template struktur bawaan seperti tag <head>, Header, Sidebar, Wrapper body, dan penutup Footer.
│   └── public/         # Tampilan muka peramban publik atau Landing page.
├── database.sql        # File Skema Tabel DDL dan Dumpping data *dummy* awal pelengkap instalasi.
├── reset_pw.php        # Modul mandiri skrip utilitas untuk melakukan *hashing password* paksa via browser.
└── index.php           # Titik pusat program berjalan sebagai "Router" utama ke file *views* yang dituju parameter halaman.
```

---

## 🔒 Konfigurasi Keamanan

Sistem "D'AKSARA TECH" ini telah mengantisipasi standar eksploitasi dasar guna kelayakan *hosting* dengan menyempurnakan:
- **Prepared Statements (Melalui Ekstensi PHP Data Objects / PDO)**: Seluruh paramater argumen SQL dirangkai menggunakan pengikat eksekusi (`?` / bindParam) sehingga mustahil memanipulasinya dengan celah *SQL Injection*.
- **Modern Password Hashing**: Kata sandi rahasia tidak pernah diteks kan utuh dalam *database*, perlindungannya digawangi algoritma kriptanalisis menggunakan standar fungsi `password_hash()` bawaan mutakhir PHP (biasanya berbasis *Bcrypt*).
- **Session Hijacking Prevention**: Pengecekan status kedaluwarsa sesi (`is_logged_in()`) dijalankan di setiap peralihan halaman sensitif. Selain itu, akses yang tidak lazim via ekskalasi pengetikkan URL *(Role-Based Access Control / RBAC)* juga akan memblokir *Customer* mencoba mengakses panel Admin.
- **Cross-Site Scripting (XSS) Protection**: Seluruh *input* pengguna yang menampil di layar dievaluasi ketat dengan sanitasi standar `htmlspecialchars()`. Pembungkusan berlapis ini membatalkan perintah jahat skrip DOM eksternal (seperti `<script>alert('hack');</script>`).

---

**Diterbitkan pada Tahun 2026 © Hak Cipta oleh D'AKSARA TECH**
