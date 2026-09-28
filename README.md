# Website Portofolio & Admin Control - Mas Putra

Website portofolio profesional dan panel kendali admin berbasis PHP yang dirancang khusus sesuai dengan desain Curriculum Vitae (CV) modern SAPUTRA (**Programmer / Welder**).

---

## 🚀 Akses Cepat Website & Admin

- **Website Portofolio:** [http://localhost/MasPutra/](http://localhost/MasPutra/)
- **Tampilan CV Asli (Siap Cetak / PDF A4):** [http://localhost/MasPutra/cv.php](http://localhost/MasPutra/cv.php)
- **Halaman Login Admin:** [http://localhost/MasPutra/MrSvz1404/login.php](http://localhost/MasPutra/MrSvz1404/login.php)

### Kredensial Login Default:
- **Username:** `admin`
- **Password:** `admin123`

*(Password dapat diubah kapan saja melalui menu **Ganti Password** di panel admin)*

---

## ✨ Fitur-Fitur Utama

### 1. Tampilan Depan (Portofolio Modern & Responsif)
- **Desain Header Kurva & Ombak Biru:** Identik dengan aksen biru laut dan cyan pada CV asli.
- **Dual Identity Showcase:** Mempertegas kombinasi keahlian di bidang **Web Automation / Software Developer** dan **Welder 6G (GTAW, SMAW, GMAW)**.
- **Menu Utama:**
  - **About (Tentang Saya):** Menampilkan ringkasan profil, biodata lengkap, kartu kontak langsung (WhatsApp, Email, Lokasi, LinkedIn).
  - **Skills & Bahasa:** Indikator kemahiran teknis (Welding 100%, Node JS 85%, VB.Net 80%, PHP & HTML 85%, C# 75%, Microsoft Office 85%, Typing 100%, Computer 100%) dan penguasaan bahasa.
  - **Pengalaman Kerja & Pendidikan:** Timeline vertikal lengkap dengan tanggal, nama perusahaan industri, dan rincian tanggung jawab.
  - **Project Portofolio:** Filter kategori proyek (*Semua*, *Web Automation*, *Web Development*, *Welding & Fabrikasi*, *Desktop Application*), lengkap dengan tech stack badge, live demo, github, dan modal detail popup.
  - **Certified (Sertifikasi):** Galeri sertifikat resmi berstandar industri dan IT dengan nomor ID kredensial serta modal viewer sertifikat.
  - **Kontak & WhatsApp Generator:** Formulir pesan yang tersimpan di inbox admin dan tombol otomatis untuk meneruskan pesan ke WhatsApp.
- **Multi-Language (English Default & Indonesia):**
  - Bahasa bawaan (*default*) adalah **English (EN)**.
  - Tombol toggle cepat `[EN / ID]` tersedia di Navbar desktop, Mobile menu, dan Toolbar CV.
  - Pilihan bahasa tersimpan otomatis di Session & Cookie browser (30 hari).
  - Bio profesional dapat dikelola dalam dua bahasa di admin panel.
- **Tampilan Responsif:** Sangat fleksibel di layar smartphone (mobile drawer), tablet, dan monitor desktop.

### 2. Format CV Sesuai Screenshot Asli (`cv.php`)
- Dibuat persis 1-to-1 dengan desain gambar screenshot yang diunggah (kurva ombak biru, foto avatar lingkaran, aksen lingkaran tosca/cyan pada judul bab, tata letak dua kolom).
- Dioptimalkan khusus untuk **Cetak (Print) / Simpan sebagai PDF ukuran kertas A4**.

### 3. Panel Admin (Admin Control)
- **Dashboard Ringkasan:** Statistik jumlah project, sertifikat, riwayat kerja, dan pesan masuk.
- **Kelola Certified (`MrSvz1404/certified.php`):**
  - Tambah, edit, dan hapus sertifikat dengan dukungan **Bilingual (Indonesia & English)** untuk judul dan deskripsi kompetensi.
  - Unggah foto / dokumen sertifikat tanpa dependensi ekstensi finfo (aman di semua environment PHP).
  - Kelola penerbit, tahun terbit, ID kredensial, dan URL verifikasi.
- **Kelola Projects (`MrSvz1404/projects.php`):**
  - Tambah, edit, dan hapus project portofolio.
  - **Dukungan Multi-Image (Banyak Foto per Project):** Unggah banyak foto sekaligus dalam satu project (sangat ideal untuk dokumentasi bertahap pengelasan: root pass, capping, visual inspection, fit-up, NDT, dsb).
  - Kelola galeri foto yang sudah ada (tinjau thumbnail & hapus foto individual).
  - Tampilan depan dilengkapi **Interactive Gallery Viewer & Filmstrip Slider** (preview foto besar, navigasi panah kiri/kanan, indikator jumlah foto, dan thumbnail filmstrip yang dapat diklik).
  - Dukungan **Bilingual (Indonesia & English)** untuk judul dan deskripsi project.
  - Tentukan kategori, tech stack (alat/bahasa yang dipakai), demo link, dan repo GitHub.
- **Kelola Pengalaman Kerja (`MrSvz1404/experience.php`):**
  - Tambah dan sesuaikan riwayat perusahaan, posisi, dan deskripsi kerja dengan dukungan **Bilingual (Indonesia & English)**.
- **Kelola Pendidikan (`MrSvz1404/education.php`):**
  - Tambah dan sesuaikan sekolah / pelatihan pengelasan & IT.
- **Kelola Skills & Bahasa (`MrSvz1404/skills.php`):**
  - Sesuaikan persentase penguasaan kemampuan (1-100%).
- **Kelola Biodata (`MrSvz1404/profile.php`):**
  - Edit nama lengkap, title, nomor WhatsApp, email, alamat domisili, profil LinkedIn/GitHub, dan ganti foto avatar.

---

## 🌐 Panduan Upload ke GitHub & Deploy ke Vercel

Proyek ini telah dilengkapi dengan file konfigurasi serverless (`vercel.json` & `api/index.php`) sehingga dapat langsung di-deploy secara gratis ke **Vercel** via **GitHub**.

### 1. Upload ke GitHub

#### Cara A: Melalui Web Browser (Paling Mudah, Tanpa Install Git)
1. Buka [github.com](https://github.com/) dan login ke akun Anda.
2. Klik tombol **"+"** di pojok kanan atas > pilih **New repository**.
3. Beri nama repositori, misalnya: `masputra-portfolio`.
4. Pilih **Public**, jangan centang opsi *Add a README file*. Klik **Create repository**.
5. Di halaman repositori yang muncul, klik tautan **"uploading an existing file"**.
6. Buka folder `C:\AppServ\www\MasPutra` di File Explorer komputer Anda.
7. Pilih semua file dan folder (Ctrl + A), lalu seret (*drag and drop*) ke halaman browser GitHub.
8. Tunggu hingga proses upload selesai, lalu klik tombol hijau **Commit changes**.

#### Cara B: Menggunakan Git CLI / Terminal
1. Buka terminal di folder project `C:\AppServ\www\MasPutra`.
2. Jalankan perintah berikut:
   ```bash
   git init
   git add .
   git commit -m "Initial commit: Portfolio & CV Mas Putra"
   git branch -M main
   git remote add origin https://github.com/USERNAME_ANDA/masputra-portfolio.git
   git push -u origin main
   ```

---

### 2. Deploy ke Vercel

1. Buka [vercel.com](https://vercel.com/) dan login menggunakan akun GitHub Anda (**Continue with GitHub**).
2. Di dashboard Vercel, klik tombol **"Add New..."** lalu pilih **Project**.
3. Di bawah daftar repositori, cari repositori `masputra-portfolio`, kemudian klik **Import**.
4. Pada halaman konfigurasi:
   - **Framework Preset:** Pilih `Other`
   - **Root Directory:** `./` (default)
   - **Build & Development Settings:** Biarkan kosong / default
5. Klik tombol **Deploy**.
6. Tunggu sekitar 1 menit hingga proses build selesai. Website portofolio Anda langsung aktif dan dapat diakses publik dengan domain gratis ber-SSL (HTTPS), misalnya:
   `https://masputra-portfolio.vercel.app`

> [!NOTE]
> **Sifat Serverless Vercel:**
> Vercel berjalan pada arsitektur *stateless serverless*. Tampilan halaman portofolio publik, lembar CV, galeri proyek, dan multi-foto sertifikat akan berjalan sangat cepat di CDN global. Jika Anda ingin menambah proyek/sertifikat baru di kemudian hari, Anda dapat mengelolanya di komputer lokal (AppServ), lalu lakukan `git push` ke GitHub. Vercel akan otomatis memperbarui (*auto-deploy*) website online Anda dalam hitungan detik!

