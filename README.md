# MASEKO / AKFIOKE — Sistem Manajemen Sekolah Terpadu & Penilaian Kinerja (AKFI)
### Yayasan Islam Al Kahfi Batam

> *Terakhir diperbarui: 26 November 2026 (Sinkronisasi codebase hingga Kirim Email PDF FR 8001C Kepsek & Deputi, Timestamp Versi Data Impor Tagihan TU, Endpoint Streaming Aman `/tagihan/signed-doc/:id/:type` & Rangkap Kepsek Opsi A)*

Aplikasi web enterprise yang dikembangkan khusus untuk **Yayasan Islam Al Kahfi Batam** menggunakan **PHP 8.x Native + MariaDB/MySQL**, dirancang responsif dengan orientasi **Sidebar**, dukungan **Progressive Web App (PWA)**, multi-role switcher, **Delegasi Izin Switch Akun**, filter **Tahun Ajaran & Semester (Opsi B: 1 TA = 2 Semester Juli–Juni)** global, penyimpanan multi-tier (**Hot & Cold Storage NAS**), serta berjalan secara **offline-ready** tanpa ketergantungan CDN eksternal.

---

## 1. Spesifikasi Teknis & Stack

- **Platform:** Native PHP 8.x + MySQL / MariaDB (`PDO` Singleton dengan `ATTR_EMULATE_PREPARES = false`)
- **Arsitektur:** Custom Model-View-Controller (MVC) ringan & modular (`public/index.php` + `Core\Router`) dengan lapisan layanan (`app/Services/`) dan driver penyimpanan (`core/Storage/`)
- **UI & Layout:** Modern Responsive Sidebar Layout, Tabler/Bootstrap 5, Chart.js 4.4.4 (Lokal UMD), FontAwesome 6 (Webfonts lokal), PWA (`/manifest.json`, `/sw.js`, `/offline`)
- **Library Vendor (Composer Terpasang):**
  - `dompdf/dompdf`: Mesin cetak PDF A4 & Landscape resmi (Rapor Kinerja AKFI, Rapor Deputi Pendidikan, Struktur Kurikulum `FR-9023a`, Kalender Kerja Deputi, Sertifikat Syahadah Tahfidz landscape, Instrumen Supervisi Kelas, Rapor Bulanan/Tahfidz/Gabungan Santri, Surat Tagihan SPP `FR 8001B`/`C`/`D`, Kartu Cicilan, Kartu Login Orang Tua, SK Panitia, PO & Berita Acara Scrap `F30`).
  - `phpoffice/phpspreadsheet`: Generator ekspor & impor data Excel `.xlsx` (Pegawai, Siswa, Alumni, Aset, Tagihan `FR 8001A`, Nilai Bulanan, Absensi, Rekap AKFI Yayasan, Ledger Kelas) dengan proteksi anti-*Formula Injection* dan **Sistem Verifikasi Import Duplikat**.
  - `phpmailer/phpmailer`: Pengiriman email resmi notifikasi tagihan SPP `FR 8001B` beserta **lampiran PDF otomatis**, Kartu Cicilan, undangan rapat MoM Deputi, dan uji coba SMTP unit dengan *SSL/TLS `SMTPOptions`* dan auto-deteksi port `465`/`587`.
- **Penyimpanan Multi-Tier (`Core\Storage\StorageManager`):**
  - *Hot Storage*: `storage/uploads/` untuk berkas Tahun Ajaran aktif (di-stream aman via `Uploader`).
  - *Cold Storage*: Arsip Tahun Ajaran lampau ke NAS lokal/UNC (`LocalStorageDriver`) atau Synology/Nextcloud (`WebDavStorageDriver`) lengkap dengan transfer bertahap & snapshot DB terenkripsi.
- **Zona Waktu:** `Asia/Jakarta` (WIB)
- **Mata Uang & Tanggal:** Format Rupiah (`Rp 1.000.000`) & Tanggal Standar Indonesia (`dd-mm-yyyy`, bulan akademik **Juli–Juni**).

---

## 2. Fitur Utama & Modul Sistem

### 1. Autentikasi, Keamanan & Delegasi Switch Akun
- **Multi-Role & Multi-Unit Switcher (`Peran — Unit`) & Impersonasi:** Pengguna dengan banyak peran maupun penugasan lintas unit dapat berpindah peran dan konteks unit seketika di header tanpa logout. **Operator Yayasan** dapat melakukan *Switch User / Impersonasi* penuh ke akun mana saja.
- **Penugasan Peran Lintas Unit (`/master/cross-unit`, `user_roles.unit_id`):** Operator Yayasan dapat menugaskan guru atau Kepala Sekolah untuk bertugas operasional (misal Guru Mapel / Guru Tahfidz / **Wali Kelas** di `/classes`) **maupun merangkap sebagai `Kepala Sekolah`** di satuan pendidikan lain (Opsi A: seluruh unggah 18 dokumen KS, approval guru/tagihan/PO, penilaian AKFI bawahan, dan penandatanganan resmi unit rangkap berjalan penuh via switcher header `Kepala Sekolah — [Unit]`) tanpa membuat akun ganda atau Rapor AKFI ganda; Unit Induk (`employees.unit_id`) tetap menjadi basis 1 Rapor AKFI tunggal.
- **Delegasi Izin Switch Akun (`/settings/switch-permissions`):** Operator Yayasan dapat mengatur izin *Switch Account* khusus untuk akun **Tata Usaha**, **Operator Sekolah**, **Guru**, dan **Kepala Sekolah** agar hanya dapat beralih ke akun yang diizinkan (akun **Orang Tua** dan **Siswa** dikecualikan), dilengkapi filter **Unit Satuan Pendidikan** dan filter **Peran/Role**.
- **Pill Filter Switcher Multi-Peran & Unit (2 Dimensi):** Switch User (`Core\View`, `views/layouts/main.php`) membaca & menampilkan **seluruh peran** tiap akun (bukan hanya peran utama) serta klasifikasi **Unit** (`Semua Unit`, `Yayasan`, tiap unit `KB/TK/SD/SMP/SMA`, dan **`Lintas Unit`**) yang dapat dikombinasikan dengan filter **Peran** (`Pimpinan/Deputi`, `Kepala Sekolah`, `Waka & Koor`, `Guru & Walas`, `Tata Usaha`, `Operator`, `Staf Unit`, `Ortu & Siswa`).
- **Pertahanan Keamanan 14 Lapisan:** CSRF token pada seluruh `POST` (`Core\CSRF`), Same-Origin check pada `GET` state-changing, session hardening (`httponly`, `samesite=Lax`, `User-Agent fingerprint`, idle timeout 30 menit, absolute limit 8 jam), dual-layer brute-force & password spraying lockout (`login_attempts`), kebijakan password minimal 10 karakter + *force change password* akun default, Anti-IDOR terpusat (`assertUnitOwnership`, `assertStudentAccess`), enkripsi `AES-256-CBC` + `HMAC-SHA256` berbasis HKDF, proteksi unggahan berkas (tolak SVG, validasi `finfo` + `getimagesize`), CSP & HTTP Security Headers, serta `AuditLog` append-only.

### 2. Master Data, Verifikasi Import & Siklus Tahun Ajaran
- **Master Unit, Identitas Aplikasi & SMTP:** Kelola 5 satuan pendidikan (KB, TK, SD, SMP, SMA) beserta Kop Surat dual logo, fitur *Soft Delete & Restore* unit sekolah, pengaturan nama/logo aplikasi (`/settings/app`), dan konfigurasi SMTP + uji coba kirim email per unit (`/settings/smtp`).
- **Tahun Ajaran Opsi B (1 TA = 2 Semester):** Dukungan toggle Semester Ganjil/Genap dalam satu Tahun Ajaran, persetujuan aktivasi TA oleh seluruh Operator Sekolah, promosi/kenaikan kelas otomatis (`PromotionHelper`), serta pengarsipan *Cold Storage NAS*.
- **Verifikasi Import Excel Duplikat (Siswa & Pegawai):**
  - **Import & Perbaikan Massal Data Siswa (`/students`, `/students/export`, `/students/verify-import`, `/students/confirm-import`):** Ekspor siswa kini berformat identik dengan template impor (`A1:M1`, teks eksplisit anti-scientific notation), sehingga operator tinggal mengedit NIS, No. HP, NIK, No. KK, dll. lalu mengunggahnya ulang. Melalui modal verifikasi **Opsi 1 (3 Kategori: Siswa Baru / Update Data Lama dengan pratinjau perubahan Lama $\rightarrow$ Baru / Bermasalah)**, sistem mencocokkan data lama via `NIS` $\rightarrow$ `NISN` $\rightarrow$ `NIK` $\rightarrow$ `Nama Lengkap`, mencegah bentrok lintas siswa, dan otomatis menyinkronkan akun login Orang Tua (`users.username = no_kk`, `phone`, `email`, serta *sibling relink*).
  - **Import Guru & Karyawan (`/master/employees`):** Menggunakan sistem verifikasi modal/tab **Data Bermasalah** yang sama untuk mendeteksi duplikat **Nama, NIPY, NIK, dan Email** serta validasi dropdown master sebelum *commit*.
- **Pemisahan Manajemen Akun Sekolah & Orang Tua:**
  - **Akun Sekolah (`/users/school-accounts`):** Pengelolaan akun guru dan staf beserta shortcut tombol **`+`** untuk penambahan peran (*Assign Role*) langsung per pegawai—kini turut menampilkan **Guru Lintas Unit** (`user_roles.unit_id`) di unit tujuan sehingga Operator Sekolah dapat menambah/mencabut peran operasional khusus unitnya (terkecuali `kepala_sekolah` & akun yayasan), serta mendukung peran **Guru TK (`guru_tk`)** dan **Guru Mapel KB-TK (`guru_mapel_kbtk`)**.
  - **Akun Orang Tua (`/users/parent-accounts`):** Halaman khusus pengelolaan akun wali santri, reset password massal/individu, dan cetak kartu akun orang tua.

### 3. Mesin Penilaian Kinerja AKFI (Al Kahfi Focus Indicator)
- **10 Posisi / Template Kinerja AKFI:**
  1. Guru Mapel SD/SMP/SMA (Kinerja 65%, Sikap 20%, Presensi 15%)
  2. Guru Tahfidz / Tahta (65% / 20% / 15%)
  3. Guru Wali Kelas (65% / 20% / 15%)
  4. Guru TK / KB (65% / 20% / 15%)
  5. Tata Usaha (60% / 20% / 20%)
  6. Cleaning Services (60% / 20% / 20%)
  7. Security (50% / 30% / 20%)
  8. Kepala Sekolah SD (60% / 30% / 10%)
  9. Kepala Sekolah TK (60% / 30% / 10%)
  10. Kepala Sekolah SMP & SMA (60% / 30% / 10%)
- **Auto-Data Collector (Termasuk Kebijakan 1 Rapor AKFI Lintas Unit & Rangkap Kepala Sekolah — Opsi A):** Mengambil otomatis data presensi, kajian Sabtu, skor supervisi, ketuntasan tahfidz, ketepatan upload administrasi, rasio tunggakan SPP, checklist harian, capaian SPMB, jejak alumni, hingga **AKFI Kepala Sekolah TK** (`% Capaian National Kurikulum (Target 90%)` dan `% Capaian Baca Latin (Target 90%)`). Pegawai yang bertugas lintas unit tetap hanya memiliki **1 Rapor AKFI pada Unit Induknya**: bagi guru yang mengajar di 2 unit, capaian Bagian A terakumulasi otomatis lintas unit, sedangkan Kepala Sekolah yang membantu mengajar atau **merangkap Kepala Sekolah di unit lain** tetap dinilai melalui **1 Rapor AKFI Unit Induk** sementara tugas manajerial/dokumennya di unit rangkap tetap menyuplai KPI Deputi dan AKFI guru unit rangkap.
- **Aturan Pengajuan & Format Rapor AKFI:**
  - Saat guru, karyawan, atau kepala sekolah mengajukan nilai AKFI ke atasan (`/akfi/pribadi`), **yang diajukan HANYA Bagian A (Kinerja)**; sedangkan **Bagian B (Sikap)** dan **Bagian C (Absensi)** murni diisi/divalidasi oleh atasan penilai.
  - Pada seluruh rapor dan formulir penilaian AKFI **Bagian B**, kolom *Realisasi Aktual* ditiadakan.
- **Alur Pengesahan 4 Tanda Tangan Digital:** Pegawai $\rightarrow$ Atasan Langsung / Kepala Sekolah $\rightarrow$ Deputi Pendidikan (khusus Kepala Sekolah) $\rightarrow$ Pengesahan Akhir Direktur Pendidikan / HRD (`/akfi/approval-hr` menjadi status `Final`), disertai pratinjau Kanit dengan isolasi unit binaan Deputi, cetak PDF A4, dan ekspor `.xlsx` Rekap Yayasan.

### 4. Modul Eksekutif Deputi Pendidikan, Direktur Pendidikan & RAPBS
- **Pengelolaan Unit Binaan & Rapor KPI Deputi:** Penetapan unit binaan Deputi A/B, pengaturan bobot Rapor Deputi (wajib 100%), penilaian 23 indikator master pada 4 area (Kurikulum, Kesiswaan, SDM, Keuangan RAPBS) oleh Direktur Pendidikan, dan **KPI Mutu / KKM Mapel (`/deputi/kpi-mutu`)** dengan deteksi fleksibel modul Halaqah Tahfidz/Tahsin serta pemetaan sinonim mapel dwibahasa & hitung ketuntasan siswa unik.
- **Kalender Kerja & *Minutes of Meeting* (MoM) Deputi (`/deputi/calendar`):** Tampilan grid 12 bulan berbasis Tahun Ajaran **Juli–Juni** (Semester 1 & Semester 2), modal multi-event harian, fitur **Auto-Carryover komitmen antar bulan/rapat** dengan progres persentase, kirim undangan email, unggah & **hapus lampiran MoM**, serta ekspor PDF.
- **RAPBS Unit (Akun 501–509):** Penyusunan draf RAPBS oleh Kepala Sekolah (termasuk hapus poin usulan), monitoring realisasi anggaran, dan alur persetujuan berjenjang 3 tingkat.
- **Monitoring Yayasan:** Target Hari Kerja & Kajian Tahunan, Dashboard Kehadiran Guru Yayasan (terisolasi per unit binaan Deputi), Rekap Supervisi Guru seluruh unit, dan pemberian lencana (*Badge*) SDM.

### 5. Struktur Kurikulum (`FR-9023a`) & Jadwal Pelajaran Interaktif
- **Struktur Kurikulum (`FR-9023a`):** Pengaturan kurikulum per jenjang oleh Kepala Sekolah / Waka Kurikulum yang sinkron otomatis ke master mata pelajaran unit, dilengkapi pratinjau cetak & PDF resmi.
- **Jadwal Pelajaran & Radar Guru Badal (`/jadwal/kelas`, `/jadwal/tahfidz`):** Penjadwalan pelajaran kelas dan halaqah tahfidz, pengaturan slot jam pelajaran (pop-up modal dengan layering z-index aman di atas kartu), serta radar status mengajar & penugasan guru pengganti (inval/badal).

### 6. Modul Administrasi Dokumen & Batas Waktu Berkala
- **18 Dokumen Administrasi KS, 8 Item Guru Mapel/Tahfidz, 6 Item Walas, dan Dokumen TU:**
  - Batas waktu (*deadline*) administrasi KS diatur multi-slot oleh Deputi (`/deputi/deadlines-ks`).
  - **Form Deadline Waka Kurikulum (`/administrasi/deadlines`):** Disesuaikan dengan tipe frekuensi dokumen: **1x Setahun**, **2x Setahun** (Semester 1 & Semester 2), dan **Setiap Bulan**.
  - **Format Bulan Akademik Juli–Juni:** Seluruh halaman unggah administrasi bulanan (`ks_uploads`, `mapel_uploads`, `walas_uploads`, `tu_uploads`) menggunakan urutan bulan **Juli s/d Juni**.
  - Verifikasi & approval dokumen satu-persatu maupun *batch approval* oleh Kepala Sekolah dan Deputi/Pengawas (menu Approval Admin KS `/administrasi/review-ks` otomatis menampilkan nama asli, NIPY, serta badge **`Lintas Unit (Induk: ...)`** bagi Kepala Sekolah yang merangkap).

### 7. Modul Tahfidz & Kalkulator Mushaf Madinah (15 Baris/Halaman)
- Dataset 114 Surah & 604 Halaman Mushaf Madinah (`lines_count = pages * 15`), target ketuntasan bulanan/semester (TK/KB $\ge 150$ baris, SD/SMP/SMA $\ge 300$ baris).
- Input, edit, dan hapus (*CRUD*) setoran hafalan santri (`/tahfidz/setoran-siswa`), pengelolaan Halaqah Siswa & Halaqah Guru, Target Tahunan, Baseline Tahfidz 3x setahun, pengajuan Ujian Tasmi' ke **Tutor Siswa**, pencetakan **Sertifikat Syahadah PDF** (landscape bernomor otomatis) dan Rapor Tahfidz PDF.

### 8. Modul Supervisi Kelas & Nilai Bulanan / Ledger
- **Supervisi Kelas:** Jadwal supervisi oleh Waka Kurikulum $\rightarrow$ penilaian oleh Guru Supervisor mencakup Bagian A (Administrasi), B (Pendahuluan), C (Kegiatan Inti, dengan validasi aturan khusus **Indikator 12**), dan D (Penutup) + cetak PDF instrumen.
- **Nilai Bulanan & Ledger:** Input nilai manual/impor Excel oleh Guru Mapel, rekap Ledger Nilai Kelas oleh Wali Kelas (ekspor Excel/PDF), serta cetak Rapor Bulanan dan Rapor Gabungan Santri.

### 9. Modul Keuangan, Tagihan SPP Multi-Tahap & Buku Cicilan
- **Input, Format & Sortir Tagihan (`FR 8001A`, `/tagihan/manage`, `/tagihan/template`):** Mendukung urutan kolom biaya baku templat `FR 8001A` (`Buku Paket`, `Gedung`, `Kegiatan Siswa`, `Komite`, `Peningkatan Mutu`, `Seragam`, `SPP`, `Uang Ujian`, `Tunggakan Tahun Lalu`) maupun item kustom dinamis, filter tab/pill **Per Kelas** beserta kolom **Kelas**, urutan alfabetis **A–Z per kelas**, filter **Sembunyikan Lunas**, pencarian siswa by Nama/NIS, indikator status email orang tua, perbaikan sinkronisasi gender (`L/P`) pada templat Excel, serta tombol **Backup Offline (.xlsx)** (`/tagihan/backup-offline`) 3 sheet (Tagihan, Buku Cicilan, Riwayat Pembayaran).
- **Sinkronisasi Saldo Langsung dari Braja / `FR 8001A` (Model Timpa, `/tagihan/verify-import`, `/tagihan/confirm-import`):** Satu mode impor tunggal berbasis **Model Timpa (*Snapshot Sync*)** yang menerima langsung ekspor mentah **Braja** (`.xlsx`, `.xls`, `.csv`) maupun `FR 8001A`. Parser otomatis mengabaikan kop sekolah dan baris `TOTAL`, memetakan pos biaya tambahan (*Gedung*, *Seragam*, dll.) ke `custom_items`, menjaga isolasi ketat antar unit (**KB, TK, SD, SMP, SMA** tidak dicampur—siswa unit lain dilewati otomatis), menandai tunggakan lama menjadi **Lunas** bila saldo Braja `Rp 0`, serta menyinkronkan **Buku Cicilan Aktif** (Skenario A: menambah `total_debt` & `remaining_debt` sesuai penambahan hutang disertai catatan otomatis pada kolom `notes`).
- **Pengingat `FR 8001B` Satuan & Kirim Massal (`/tagihan/kirim-fr8001b`, `/tagihan/bulk-kirim-fr8001b`):** Mendukung pengiriman satuan maupun **Kirim Massal FR 8001B** ke banyak santri terpilih sekaligus via **PHPMailer SMTP** berlampiran **PDF resmi FR 8001B** (lengkap dengan `SMTPOptions`, auto-deteksi port `465`/`587`, serta laporan jumlah terkirim/kosong/gagal).
- **Eskalasi Kepala Sekolah & Deputi (`FR 8001C` & `FR 8001D`) + Upload Scan TTD:**
  - Penandatangan resmi pada cetakan PDF (Kepala Sekolah, Kepala Tata Usaha, Deputi Pendidikan) diambil langsung dari database (`employees`/`units`) berdasarkan `unit_id` agar tetap akurat meskipun operator/admin menggunakan *Switch User*.
  - Kepala Sekolah (`/tagihan/approval`), Deputi Pendidikan (`/tagihan/eskalasi-deputi`), dan Tata Usaha (`/tagihan/upload-signed-doc`) dapat mengunggah scan **FR 8001C & FR 8001D** bertanda tangan orang tua secara opsional sewaktu-waktu tanpa wajib mengisi nominal cicilan manual.
  - Cetakan PDF **`FR 8001D`** menggunakan format resmi 2 halaman / 2 dokumen: (1) *Berita Acara Kesepakatan Pembayaran Tunggakan Biaya Sekolah* (`.../BA-KEU/AIS/...`) dan (2) *Surat Pernyataan Komitmen Pembayaran Tunggakan Biaya Sekolah* (No. KTP/NIK, Alamat Domisili, No. HP, Opsi A Lunas / Opsi B Dicicil Angsuran I–IV).
- **Buku & Kartu Cicilan Siswa (`/tagihan/cicilan`):** Mendukung pelunasan presisi hingga nominal non-kelipatan ribuan (`step="1"`, tombol cepat **Lunasi Pas**), cetak Kartu Cicilan PDF (`/tagihan/cicilan/cetak-kartu`), serta pengiriman email status cicilan (`/tagihan/cicilan/kirim-email`) beserta **lampiran PDF Kartu Cicilan**.

### 10. Modul Sarpras, Inventaris Aset, PO & Berita Acara Scrap (`F30`)
- Kategori & master inventaris aset (`/sarpras/assets` dengan impor/ekspor Excel).
- Alur **Purchase Order (PO)** 3 tingkat persetujuan: Sarpras Unit mengajukan $\rightarrow$ Kepala Sekolah $\rightarrow$ Direktur Pengadaan $\rightarrow$ Direktur Sarpras (jika melibatkan kontraktor) $\rightarrow$ Realisasi & unggah Surat Jalan (*Auto-Stocked* ke `assets`).
- Berita Acara Penghapusan Barang / Scrap **`F30`** (`/sarpras/scrap`) dengan persetujuan berjenjang dan cetak PDF.

### 11. Survei Kepuasan, Checklist Operasional, SPMB, Alumni, Portal Ortu & Audit/Backup
- **Survei Kepuasan:** Kuesioner Orang Tua **53 Butir (10 Bidang)**, Survei Kepemimpinan Kepala Sekolah oleh Dewan Guru, dan Survei Pembelajaran Santri (`/survey/siswa`).
- **Checklist Operasional:** Checklist harian Security (terkunci tanggal server, cutoff 07:15 WIB), Jurnal Patroli, Buku Insiden, Checklist Kebersihan CS (rutin, piket, gotong royong + foto), dan Checklist Braja Tata Usaha.
- **Kegiatan Luar KBM, Panitia Lomba, Prestasi, SPMB & Alumni:** Presensi kegiatan non-KBM, SK Pembina & kejuaraan lomba (OSN/O2SN/FLS2N), Guru Berprestasi & *Best Practice* / PTK, target & registrasi SPMB (1-click konversi ke siswa aktif auto-NIS), serta rekomendasi studi & pelacakan alumni.
- **Portal Orang Tua (`/parent/rapor`, `/parent/bills`):** Dukungan *Multi-Child / Sibling Linkage* untuk melihat rapor akademik/tahfidz/gabungan, mengunduh Syahadah & surat tagihan, serta informasi rekening BSI unit.
- **Profil, Notifikasi, Audit Log & Backup/Restore:** Unggah tanda tangan digital di `/profile`, pusat notifikasi in-app (`/notifications`), log audit JSON mendalam (`/audit/logs`), dan cadangan database SQL/Full ZIP + transfer ke NAS (`/backup`).

---

## 3. Petunjuk Instalasi & Konfigurasi

1. **Persyaratan Lingkungan:**
   - Apache / Nginx dengan `mod_rewrite` aktif (DocumentRoot atau alias mengarah ke `public/` atau root proyek dengan `.htaccess`).
   - PHP 8.1+ (ekstensi `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`, `fileinfo`, `curl`).
   - MySQL / MariaDB (InnoDB).
2. **Penempatan Berkas:**
   Letakkan proyek pada direktori web server, misalnya di XAMPP:
   ```text
   c:\xampp\htdocs\akfioke
   ```
   Jika dependensi vendor belum ada, jalankan:
   ```bash
   composer install
   ```
3. **Setup & Migrasi Database:**
   - Buat database baru bernama `akfioke_db` (`utf8mb4_unicode_ci`).
   - Impor skema dasar dan data awal:
     ```bash
     mysql -u root akfioke_db < database/schema.sql
     mysql -u root akfioke_db < database/seed.sql
     ```
   - Jalankan skrip migrasi agar seluruh tabel fitur terbaru (Deputi, RAPBS, Struktur Kurikulum `FR-9023a`, Sarpras/PO, Jadwal, Cold Storage, Izin Switch Akun, Lintas Unit) terbentuk lengkap:
     ```bash
     php database/run_migration.php
     php database/migrate_user_switch_permissions.php
     php database/migrate_cross_unit_roles.php
     ```
4. **Konfigurasi Produksi (VPS / Hosting):**
   - Buat file `config/database.local.php` (override kredensial DB agar tidak menimpa `config/database.php`) dan `config/app.local.php` sesuai URL domain produksi.
   - Set environment variable `APP_KEY` yang unik pada server produksi untuk kunci enkripsi AES-256-CBC (`config/security.php`).
   - Pastikan direktori `storage/` (`uploads/`, `backups/`, `logs/`, `nas_cold/`) dan `public/uploads/` memiliki izin tulis PHP.
5. **Akses Aplikasi:**
   Buka peramban web ke `http://localhost/akfioke` (atau URL domain produksi).

---

## 4. Daftar Peran (Role) & Akun Pengujian Awal (`seed.sql`)

Pada instalasi awal berbasis `database/seed.sql`, seluruh akun uji coba menggunakan kata sandi bawaan:
```text
password123
```
> *Catatan keamanan: Akun baru dengan password bawaan `password123` akan ditandai `force_change_password = 1` dan wajib mengganti kata sandi saat login pertama (minimal 10 karakter kombinasi huruf besar/kecil, angka, atau simbol).*

| Role (`roles.name`) | Username Awal (`seed.sql`) | Deskripsi Hak Akses Utama |
| :--- | :--- | :--- |
| **`operator_yayasan`** | `operator.yayasan` | Superadmin yayasan: kelola unit, TA & NAS Cold Storage, pegawai, master AKFI, delegasi izin switch akun, impersonasi user, audit log, backup/restore |
| **`direktur_pendidikan`** | `direktur.pendidikan` | Penetapan unit binaan Deputi, bobot & penilaian Rapor AKFI Deputi, pengesahan akhir AKFI, rekap yayasan & supervisi |
| **`deputi_pendidikan`** | `deputi.pendidikan` | Monitoring unit binaan, target kerja & deadline KS, kalender kerja & MoM, KPI mutu, RAPBS, nilai sikap KS, eskalasi tagihan `FR 8001C/D` & upload berkas TTD |
| **`hrd`** | *(role migrasi)* | Pengesahan final Rapor AKFI pegawai seluruh unit (`/akfi/approval-hr`) |
| **`direktur_pengadaan`** | *(role migrasi)* | Verifikasi & approval Purchase Order (PO) Sarpras tahap 2 & rekap aset |
| **`direktur_sarpras`** | *(role migrasi)* | Approval PO tahap 3 (kontraktor), approval Berita Acara Scrap `F30`, monitoring aset yayasan |
| **`kepala_sekolah`** | `kepsek.tk`, `kepsek.sd`, `kepsek.smp`, `kepsek.sma` | Upload 18 dokumen KS, Struktur Kurikulum `FR-9023a`, RAPBS, approval perangkat guru/TU, approval PO tahap 1, eskalasi tagihan `FR 8001C/D` + upload berkas TTD, penilaian AKFI bawahan |
| **`operator_sekolah`** | `operator.tk`, `operator.sd`, `operator.smp`, `operator.sma` | Kelola siswa (impor + verifikasi duplikat), kelas, mapel, akun sekolah (`+` peran), akun ortu, SPMB, alumni, approval pindah TA, konfigurasi SMTP |
| **`waka_kurikulum`** | `waka.kurikulum` | Jadwal pelajaran & slot jam, guru badal/inval, jadwal supervisi, pengaturan deadline berkala (1x/2x/bulanan), rekap pelanggaran/kajian guru |
| **`waka_kesiswaan`** | `waka.kesiswaan` | Kegiatan luar KBM, verifikasi prestasi lomba siswa, pemantauan kehadiran & kajian |
| **`guru_supervisor`** | `guru.supervisor` | Penilaian instrumen supervisi pembelajaran kelas (A/B/C/D + validasi poin 12) & cetak PDF |
| **`koor_tahfidz`** | `koor.tahfidz` | Pengelolaan halaqah siswa, target baris per jenjang, baseline tahfidz 3x, rekap & cetak rapor tahfidz |
| **`guru_tahfidz`** | `guru.tahfidz` | Input/edit/hapus setoran hafalan santri (Kalkulator Madinah), pengajuan ujian tasmi', perangkat mengajar 8 item |
| **`tutor_siswa`** | `tutor.siswa` | Pengujian tasmi' santri, penerbitan Sertifikat Syahadah PDF, input hafalan guru |
| **`tutor_sebaya`** | `tutor.sebaya` | Kelola halaqah guru, target hafalan guru, input setoran tahfidz guru bulanan |
| **`walas`** | `walas.sd` | Ledger nilai kelas, absensi bulanan siswa, rapor akademik, 6 item admin walas, PTK, prestasi lomba & rekomendasi alumni |
| **`guru_mapel`** | `guru.mapel` | Input nilai bulanan/KKM, 8 item perangkat ajar, *Best Practice*, pengajuan AKFI pribadi (Bagian A), survei Kepsek |
| **`guru_diniyah`** | `guru.diniyah` | Input nilai mapel diniyah bulanan, perangkat ajar 8 item, *Best Practice*, AKFI pribadi |
| **`guru_tk`** / **`guru_mapel_kbtk`** | *(role unit TK/KB)* | Pengelolaan nilai, perangkat mengajar & penilaian AKFI khusus Guru KB-TK |
| **`panitia_lomba`** | `panitia.lomba` | Pencatatan keikutsertaan & kejuaraan lomba resmi (OSN, O2SN, FLS2N, dll.) |
| **`tata_usaha`** | `tu.sd` | Tagihan `FR 8001A` (impor Braja Model Timpa & verifikasi, filter per kelas, backup offline `.xlsx`), reminder satuan/massal `FR 8001B` (PDF + Email SMTP), upload TTD `FR 8001C/D`, Buku Cicilan `FR 8001D` (`Lunasi Pas`, log pembaruan otomatis, lampiran Kartu PDF), dokumen TU, Braja, absensi & kajian |
| **`sarpras`** | *(role unit)* | Inventaris aset unit, pengajuan Purchase Order (PO), realisasi aset *auto-stocked*, pengajuan Scrap `F30` |
| **`security`** | `security.sd` | Checklist keamanan harian (tanggal server), buku patroli pos, buku insiden |
| **`cleaning_service`** | `cs.sd` | Checklist kebersihan rutin harian, piket bulanan, gotong royong Sabtu |
| **`orang_tua`** | `ortu.santri` | Portal rapor akademik/tahfidz/gabungan & Syahadah anak, cek tagihan SPP & cicilan, survei kepuasan 53 butir |
| **`siswa`** | `siswa.smp` | Portal pengisian survei pengalaman belajar santri |

---

## 5. Tata Cara Penggunaan Singkat (Alur Operasional & Input–Output)

Siklus operasional tahunan berjalan dalam **10 langkah berurutan** (rincian mendalam tiap modul, tabel database, dan interaksi antar-akun dijelaskan di [`laporan_full_scan_maseko_akfioke.md`](file:///c:/xampp/htdocs/akfioke/laporan_full_scan_maseko_akfioke.md)):

1. **Inisialisasi Yayasan & Tahun Ajaran:** Operator Yayasan menyiapkan unit & logo (`/master/units`), mengaktifkan TA/Semester (`/master/academic-years`), mengimpor pegawai via modal verifikasi duplikat (`/master/employees`), mengatur izin switch akun (`/settings/switch-permissions`), serta menetapkan penugasan peran lintas unit (`/master/cross-unit`) bila ada guru/Kepsek mengajar di unit lain. Direktur Pendidikan menetapkan unit binaan & bobot Rapor Deputi.
2. **Target & Standar Tahunan:** Deputi Pendidikan menetapkan target hari kerja/kajian (`/deputi/target-kerja`), deadline 18 dokumen KS multi-slot (`/deputi/deadlines-ks`), target SPMB (`/spmb/target`), dan KPI Mutu/KKM (`/deputi/kpi-mutu`).
3. **Persiapan Unit Sekolah:** Operator Sekolah menyetujui aktivasi TA, mengelola kelas & penempatan siswa (`/classes`), memetakan guru pengampu mapel (`/subjects`), mengimpor siswa via verifikasi duplikat (`/students`), dan mengatur SMTP unit (`/settings/smtp`). Kepala Sekolah menyusun Struktur Kurikulum `FR-9023a` (`/kurikulum/struktur`) dan draf RAPBS (`/deputi/rapbs`).
4. **Penjadwalan & Batas Waktu:** Waka Kurikulum menyusun jadwal pelajaran + slot jam (`/jadwal/kelas`), jadwal supervisi (`/supervisi/jadwal`), dan batas waktu administrasi berkala (1x / 2x / bulanan) di `/administrasi/deadlines`. Koordinator Tahfidz & Tutor Sebaya membagi halaqah serta target baris hafalan.
5. **Unggah Perangkat & Supervisi:** Guru Mapel/Tahfidz/Walas mengunggah dokumen berkala (urutan bulan Juli–Juni) dan *Best Practice*/PTK; Kepala Sekolah melakukan review & approval; Guru Supervisor mengisi rubrik supervisi kelas (`/supervisi/penilaian`).
6. **Operasional Bulanan:** Guru menginput nilai bulanan; Walas menginput absensi siswa; Guru Tahfidz menginput setoran Mushaf Madinah & mengajukan ujian ke Tutor Siswa; TU menginput absensi & kajian pegawai serta checklist Braja; CS & Security mengisi checklist harian; Sarpras mengelola inventaris, PO, & Scrap `F30`.
7. **Penagihan SPP, Sinkronisasi Braja Model Timpa & Cicilan:** TU membuat tagihan `FR 8001A` atau mengunggah berkas ekspor **Braja** (`.xlsx`/`.xls`/`.csv`) melalui pratinjau verifikasi (`/tagihan/verify-import` $\rightarrow$ `/tagihan/confirm-import`) yang otomatis menimpa saldo tagihan aktif unit bersangkutan, memisahkan baris unit lain secara ketat, menandai `Lunas` otomatis bila saldo Braja `Rp 0`, memperbarui total serta catatan otomatis pada Buku Cicilan aktif, dan merekam **Timestamp Versi Data Impor TU** (`tagihan_import_unit_<unit_id>`) yang tampil di akun TU, Kepala Sekolah, dan Deputi Pendidikan. TU dapat memfilter per kelas (A–Z), mengirim `FR 8001B` satuan/massal berlampiran PDF, serta mengunduh **Backup Offline (.xlsx)**. Kepala Sekolah (`/tagihan/approval` via `/tagihan/kepsek/kirim-email`) dan Deputi (`/tagihan/eskalasi-deputi` via `/tagihan/deputi/kirim-email`) dapat mengirim surat pemanggilan **FR 8001C** dalam bentuk **lampiran PDF** melalui email SMTP unit ke orang tua/wali (sedangkan formulir kesepakatan `FR 8001D` dicetak langsung saat pertemuan), serta mengunggah/membuka scan TTD orang tua secara aman melalui `/tagihan/signed-doc/:id/:type` (`TagihanController::viewSignedDoc()`).
8. **Survei Kepuasan:** Orang Tua mengisi kuesioner 53 butir, Santri mengisi survei belajar, dan Dewan Guru mengisi survei kepemimpinan Kepala Sekolah.
9. **Penilaian & Pengesahan AKFI:** Pegawai mengajukan **Bagian A saja** di `/akfi/pribadi` $\rightarrow$ Atasan/Kepala Sekolah menilai Sikap (**Bagian B** tanpa kolom realisasi aktual) & Absensi (**Bagian C**) $\rightarrow$ Deputi menilai sikap KS $\rightarrow$ Direktur Pendidikan/HRD mengesahkan `Final` di `/akfi/approval-hr` dan menilai Rapor Deputi.
10. **Tutup Tahun Ajaran & Arsip Cold Storage:** Operator Yayasan mencadangkan Full Backup ZIP/SQL (`/backup`) dan mentransfer arsip berkas tahun ajaran lampau ke NAS Cold Storage (`/master/academic-years`).

---

## 6. Struktur Direktori Proyek

```text
akfioke/
├── app/
│   ├── Controllers/                        # 24 Controller modular
│   │   ├── AbsensiController.php           # Presensi guru, staf, siswa & kajian Sabtu
│   │   ├── AdministrasiController.php      # 18 berkas KS, upload Juli-Juni, deadline berkala & approval
│   │   ├── AkfiController.php              # 10 template AKFI, auto-collector, ajuan Bagian A, rapor PDF
│   │   ├── AlumniController.php            # Rekomendasi studi & pelacakan alumni
│   │   ├── AuditBackupController.php       # Audit logs, full backup ZIP/SQL & restore
│   │   ├── AuthController.php              # Login, ganti password, rate limiter, switch role/TA/user
│   │   ├── ChecklistController.php         # Checklist Security, patroli, insiden, CS, Braja TU
│   │   ├── DashboardController.php         # Dynamic dashboard per role & isolasi unit Deputi
│   │   ├── DeputiController.php            # Unit binaan, bobot, kalender MoM, KPI mutu, RAPBS, rapor Deputi
│   │   ├── JadwalController.php            # Jadwal pelajaran interaktif, slot jam & radar guru badal
│   │   ├── KurikulumController.php         # Struktur Kurikulum FR-9023a & cetak PDF
│   │   ├── MasterController.php            # Unit, TA & NAS, verifikasi import, akun sekolah/ortu, switch permissions, SMTP
│   │   ├── NilaiBulananController.php      # Input nilai bulanan, ledger kelas, rapor bulanan/gabungan PDF
│   │   ├── NotificationController.php      # Pusat notifikasi in-app
│   │   ├── PanitiaLombaController.php      # Kegiatan luar KBM, SK panitia, prestasi siswa & guru, Best Practice
│   │   ├── ParentController.php            # Portal orang tua (multi-anak, rapor & tagihan)
│   │   ├── ProfileController.php           # Profil pengguna & tanda tangan digital
│   │   ├── PwaController.php               # Manifest PWA, service worker & halaman offline
│   │   ├── SarprasController.php           # Kategori, inventaris aset, PO 3-tahap & Berita Acara Scrap F30
│   │   ├── SpmbController.php              # Target kuota, pendaftaran & konversi santri baru
│   │   ├── SupervisiController.php         # Jadwal & rubrik supervisi kelas (aturan indikator 12)
│   │   ├── SurveyController.php            # Survei ortu 53 butir, guru ke Kepsek & siswa
│   │   ├── TagihanController.php           # Tagihan Braja Model Timpa & FR 8001A, FR 8001B massal, FR 8001C/D, Buku Cicilan & Backup Offline
│   │   └── TahfidzController.php           # Kalkulator Madinah, setoran CRUD, antrean ujian & Syahadah PDF
│   └── Services/
│       ├── AcademicYearArchiveService.php  # Transfer arsip TA ke Cold Storage NAS & snapshot DB
│       └── BackupService.php               # Mesin backup/restore SQL & Full ZIP aman
├── config/
│   ├── app.php                             # Pengaturan aplikasi, runtime limit & zona waktu WIB
│   ├── database.php                        # Konfigurasi PDO MariaDB/MySQL (support database.local.php)
│   ├── mail.php                            # Konfigurasi default SMTP PHPMailer
│   ├── security.php                        # Enkripsi AES-256-CBC + HMAC-SHA256 HKDF & session security
│   └── storage.php                         # Konfigurasi Multi-Tier Hot & Cold Storage (Local/UNC/WebDAV)
├── core/
│   ├── Storage/
│   │   ├── LocalStorageDriver.php          # Driver lokal/SMB/UNC NAS
│   │   ├── StorageDriverInterface.php      # Kontrak driver penyimpanan
│   │   ├── StorageManager.php              # Pengelola perpindahan tier Hot/Cold Storage
│   │   └── WebDavStorageDriver.php         # Driver WebDAV Synology/Nextcloud
│   ├── AuditLog.php                        # Pencatatan audit trail JSON append-only
│   ├── Auth.php                            # Sesi aman, anti-IDOR, switch role & delegasi switch user
│   ├── Controller.php                      # Base Controller, flash message & open-redirect protection
│   ├── CSRF.php                            # Proteksi & validasi token CSRF
│   ├── Database.php                        # Singleton PDO connection wrapper
│   ├── ExportHelper.php                    # Ekspor Excel anti-formula injection, Kop Surat & Dompdf
│   ├── Model.php                           # Base Model
│   ├── Notification.php                    # Mesin notifikasi in-app
│   ├── PromotionHelper.php                 # Promosi kenaikan kelas otomatis antar Tahun Ajaran
│   ├── Router.php                          # HTTP Router & global CSRF enforcement
│   ├── SecurityHelper.php                  # XSS escape, HKDF encrypt/decrypt, security headers, format ID
│   ├── Setting.php                         # Pengaturan dinamis aplikasi (`app_settings`)
│   ├── Uploader.php                        # Unggah berkas aman dengan validasi MIME finfo & streaming
│   └── View.php                            # Template engine renderer & switcher izin akun
├── database/
│   ├── schema.sql                          # Skema dasar 54 tabel InnoDB
│   ├── seed.sql                            # Data awal: surah, halaman Madinah, role, akun, indikator AKFI
│   ├── run_migration.php                   # Migrasi terpadu (Deputi, RAPBS, Struktur Kurikulum, Lintas Unit, dll.)
│   ├── migrate_cross_unit_roles.php        # Migrasi kolom unit_id pada user_roles (Lintas Unit)
│   ├── migrate_user_switch_permissions.php # Migrasi tabel izin delegasi switch akun
│   └── ...                                 # Skrip migrasi & utilitas pendukung lainnya
├── public/
│   ├── assets/                             # CSS app.css, Chart.js 4.4.4 lokal, FontAwesome 6 lokal
│   ├── images/                             # Logo yayasan & aset gambar publik
│   ├── uploads/                            # Logo unit/aplikasi & file publik (.htaccess non-eksekusi PHP)
│   ├── index.php                           # Single entry point & definisi seluruh rute HTTP
│   └── .htaccess                           # Rewrite engine & security headers
├── storage/
│   ├── backups/                            # Arsip cadangan database (.sql / .zip)
│   ├── logs/                               # Log error produksi (`php_error.log`)
│   ├── nas_cold/                           # Direktori fallback Cold Storage lokal
│   └── uploads/                            # Berkas dokumen internal (di luar webroot)
├── views/                                  # 26 direktori antarmuka (120 berkas view PHP)
├── composer.json
├── laporan_full_scan_maseko_akfioke.md
└── README.md
```

---

## 7. Lisensi & Hak Cipta

Aplikasi ini dikembangkan untuk kepentingan operasional, keuangan, akademik, dan evaluasi kinerja **Yayasan Islam Al Kahfi Batam**. Seluruh hak cipta dilindungi undang-undang.
