# D'AKSARA TECH - Platform Jasa Akademik & Project IT

D'AKSARA TECH adalah sebuah sistem informasi berbasis web responsif yang menyediakan dan mengelola layanan pembuatan tugas akademik (makalah, presentasi, jurnal, dsb.) serta pengembangan proyek IT (website, aplikasi mobile, desain UI/UX). Sistem ini dirancang untuk memudahkan interaksi, transaksi, dan pertukaran file antara penyedia jasa (Admins) dan pelanggan (Customers).

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

<img width="1600" height="765" alt="image" src="https://github.com/user-attachments/assets/2b7b3f34-0cfc-417b-b24c-27359a87c216" />
<img width="1600" height="773" alt="image" src="https://github.com/user-attachments/assets/d96e6784-3c51-4e27-9eb6-277dc320b000" />
<img width="1600" height="774" alt="image" src="https://github.com/user-attachments/assets/a678655b-8a3b-46e7-aeba-68661575a7fb" />
<img width="1600" height="762" alt="image" src="https://github.com/user-attachments/assets/79cfa420-2a25-458d-b774-428d08b99928" />
<img width="1600" height="774" alt="image" src="https://github.com/user-attachments/assets/b9fdffbe-7ea0-49d1-91af-7663d65a6abf" />
<img width="1600" height="776" alt="image" src="https://github.com/user-attachments/assets/beba148d-3789-4789-9240-788b45caa9f3" />
<img width="1600" height="765" alt="image" src="https://github.com/user-attachments/assets/121e9018-37c7-4f9c-a762-aaa7ae6ee0d3" />
<img width="1600" height="764" alt="image" src="https://github.com/user-attachments/assets/a13ee52a-f4fa-4f50-87be-78c76292c86b" />
<img width="1600" height="773" alt="image" src="https://github.com/user-attachments/assets/99e46ac1-a875-4042-bf88-2f1663dce156" />



### 👤 Customer Panel
- **Pemesanan Layanan**: Antarmuka pemesanan yang mudah. Pelanggan dapat menyertakan deskripsi dan file pendukung (referensi tugas).
- **Transparansi Biaya (Itemized Billing)**: Rincian perhitungan total harga ditampilkan secara jelas sejak *Form Order* hingga *Invoice*, termasuk kalkulasi *Biaya Prioritas Deadline (Urgency Fee)* sesuai pilihan waktu (Reguler, Cepat +10%, atau Kilat +50%).
- **Pembayaran QRIS & Rekening**: Halaman *upload* bukti pembayaran dengan dukungan metode scan *barcode* QRIS otomatis maupun transfer bank konvensional.
- **Riwayat Order**: Melacak status pengerjaan pesanan secara terinci.
- **Bantuan & Konsultasi (Chat)**: Fitur "Pesan (Chat)" terintegrasi yang memungkinkan pelanggan berdiskusi dua arah dengan Admin mengenai revisi file atau progres pengerjaan tugas (mendukung lampiran file).
- **Cetak Bukti (Invoice)**: Mencetak struk bukti pesanan / kwitansi resmi bergaya *invoice* untuk pesanan yang telah selesai (berguna untuk laporan pertanggungjawaban/LPJ keuangan).
- **Notifikasi**: Panel notifikasi *in-app* dengan indikator (*badge*) jumlah pesan yang belum dibaca dari Admin atau pembaruan status sistem.
- **Unduh Hasil**: Mengunduh langsung file *output* tugas dari Admin.

<img width="1600" height="766" alt="image" src="https://github.com/user-attachments/assets/cc3e042f-7835-485f-98f0-ab99f2274654" />
<img width="1600" height="772" alt="image" src="https://github.com/user-attachments/assets/be5012be-48bd-4151-ae2f-78aeefac2286" />
<img width="1600" height="763" alt="image" src="https://github.com/user-attachments/assets/135a3061-61ae-4d00-b68d-85e91795726e" />
<img width="1600" height="772" alt="image" src="https://github.com/user-attachments/assets/7bc02a01-df4f-4dfc-b590-8e4a8e64217e" />
<img width="1600" height="765" alt="image" src="https://github.com/user-attachments/assets/f92854ae-073d-49fa-93df-7a99f37bfc7e" />
<img width="1600" height="774" alt="image" src="https://github.com/user-attachments/assets/cbbe72f3-ac81-4893-8d37-cf82ad2f6c63" />
<img width="1600" height="773" alt="image" src="https://github.com/user-attachments/assets/4f8a0511-9983-4dc2-98b5-760bc4c643ca" />
<img width="1600" height="763" alt="image" src="https://github.com/user-attachments/assets/9eb99716-52fa-4e9a-92d0-18902aa765be" />
<img width="1600" height="773" alt="image" src="https://github.com/user-attachments/assets/b388a146-8cce-4e1d-9f9b-7c05070fd626" />
<img width="1600" height="767" alt="image" src="https://github.com/user-attachments/assets/9e3c2ae3-9214-48dd-b22f-d1b0ac3b3892" />



### 🌐 Fitur Umum (Autentikasi & Landing Page)
- **Registrasi & Login Terpadu**: Halaman pendaftaran (*Register*) dan masuk (*Login*) dengan desain *card* modern di tengah layar. Dilengkapi validasi kelengkapan form otomatis.
- **Role-Based Access Control (RBAC)**: Sistem otomatis membedakan sesi login antara `Admin` dan `Customer`, mengarahkan pengguna ke *dashboard* masing-masing, dan memblokir akses lintas-*role*.
- **Enkripsi Kata Sandi**: Kata sandi (*password*) pengguna pada halaman pendaftaran tidak pernah disimpan dalam bentuk teks biasa, melainkan di-hash menggunakan algoritma `Bcrypt` bawaan PHP yang kuat.
- **Tema Dinamis (Dark/Light Mode)**: Tersedia di seluruh tampilan sistem termasuk halaman Login/Register (diaktifkan melalui Navbar atau *toggle* bulan/matahari).
- **Landing Page Modern**: Memiliki animasi *marquee* daftar layanan (*infinite scroll*) dengan desain yang bersih dan responsif (*glassmorphism*).
- **100% Mobile Responsive**: Tata letak form login/register, *navbar*, tabel, serta konten dasbor akan menyesuaikan otomatis untuk pengalaman mulus di Desktop, Tablet, hingga Layar Smartphone (iPhone/Android).

<img width="1600" height="772" alt="image" src="https://github.com/user-attachments/assets/d0bb3b81-f777-49a1-8ffc-46c0552ea6ea" />
<img width="1600" height="772" alt="image" src="https://github.com/user-attachments/assets/4236b12a-5168-4639-b603-59c18ea03bde" />
<img width="1600" height="772" alt="image" src="https://github.com/user-attachments/assets/525d0c2b-67bd-40e5-9c7b-4574a5e570df" />
<img width="1600" height="763" alt="image" src="https://github.com/user-attachments/assets/02949af0-5cc6-47af-b1fd-634c38d397d2" />
<img width="1600" height="763" alt="image" src="https://github.com/user-attachments/assets/2d449b1c-3c73-4d54-b307-8e7a2c3ca07e" />



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



