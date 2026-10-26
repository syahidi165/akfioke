# AKFIOKE - Sistem Informasi Penilaian Kinerja (AKFI) & Manajemen Terpadu
### Yayasan Islam Al Kahfi Batam

Aplikasi web enterprise yang dikembangkan khusus untuk **Yayasan Islam Al Kahfi Batam** menggunakan **PHP 8.2 + MariaDB/MySQL (XAMPP)**, dirancang responsif dengan orientasi **Sidebar**, multi-role switcher, filter Tahun Ajaran global, dan berjalan sepenuhnya secara **offline** tanpa ketergantungan CDN eksternal.

---

## 1. Spesifikasi Teknis & Stack

- **Platform:** Native PHP 8.x + MySQL / MariaDB (XAMPP for Windows)
- **Arsitektur:** Custom Model-View-Controller (MVC) ringan & modular
- **UI & Layout:** Modern Responsive Sidebar Layout, Tabler/Bootstrap 5, Chart.js 4.4.4 (Lokal UMD), FontAwesome 6 (Webfonts lokal)
- **Library Vendor (Composer Terpasang):**
  - `dompdf/dompdf`: Mesin cetak PDF A4 resmi (Rapor Kinerja AKFI, Sertifikat Syahadah Tahfidz landscape, Instrumen Supervisi Kelas, Rapor Bulanan Santri, Surat Tagihan SPP).
  - `phpoffice/phpspreadsheet`: Generator ekspor data ke format Microsoft Excel (.xlsx) dengan proteksi anti-*Formula Injection*.
  - `phpmailer/phpmailer`: Pengiriman email resmi notifikasi tagihan SPP dan persetujuan berkas via SMTP.
- **Zona Waktu:** `Asia/Jakarta` (WIB)
- **Mata Uang & Tanggal:** Format Rupiah (`Rp 1.000.000`) & Tanggal Standar Indonesia (`dd-mm-yyyy`, nama hari dan bulan bahasa Indonesia).

---

## 2. Fitur Utama & Modul Sistem

1. **Autentikasi & Keamanan Tingkat Lanjut:**
   - Multi-role switcher di header (pengguna dengan lebih dari 1 role dapat berganti hak akses seketika).
   - Filter Tahun Ajaran aktif & Satuan Unit global.
   - Proteksi CSRF otomatis pada semua request `POST`.
   - Rate limiting login (maksimal 5 kali kegagalan $\rightarrow$ lockout akun 15 menit).
   - Sanitasi input terhadap serangan XSS dan SQL Injection via PDO Singleton prepared statements.
   - Proteksi IDOR (Insecure Direct Object References) pada akses dokumen dan data santri/pegawai.
   - Penyamaran (masking) NIK dan No. KK pada tampilan publik.
   - Log Audit Append-Only dengan perekaman perubahan data JSON secara mendalam.

2. **Mesin Penilaian Kinerja AKFI (Al Kahfi Focus Indicator):**
   - Mendukung **10 Posisi / Template Kinerja**:
     1. Guru Mapel SD/SMP/SMA (Bobot: Kinerja 65%, Sikap 20%, Presensi 15%)
     2. Guru Tahfidz / Tahta (Bobot: 65% / 20% / 15%)
     3. Guru Wali Kelas (Bobot: 65% / 20% / 15%)
     4. Guru TK / KB (Bobot: 65% / 20% / 15%)
     5. Tata Usaha (Bobot: 60% / 20% / 20%)
     6. Cleaning Services (Bobot: 60% / 20% / 20%)
     7. Security (Bobot: 50% / 30% / 20%)
     8. Kepala Sekolah SD (Bobot: 60% / 30% / 10%)
     9. Kepala Sekolah TK (Bobot: 60% / 30% / 10%)
     10. Kepala Sekolah SMP & SMA (Bobot: 60% / 30% / 10%)
   - **Auto-Data Collector**: Mengintegrasikan data presensi otomatis, partisipasi kajian Sabtu, rata-rata skor supervisi kelas, ketuntasan hafalan Al-Qur'an, kelengkapan administrasi, rasio tunggakan SPP, dan checklist harian pos jaga ke dalam formulir evaluasi atasan.
   - Alur Pengesahan 3 Tingkat: Pegawai yang Dinilai $\rightarrow$ Atasan Langsung / Penilai $\rightarrow$ Pengesahan Akhir Direktur Pendidikan Yayasan.
   - Generator Rapor Penilaian Kinerja AKFI resmi (PDF A4) dan Rekapitulasi Yayasan (.XLSX).

3. **Modul Tahfidz & Kalkulator Mushaf Madinah (15 Baris / Halaman):**
   - Dataset lengkap 114 Surah Al-Qur'an dan 604 Halaman Mushaf Standar Madinah.
   - Perhitungan otomatis rentang halaman dan jumlah baris hafalan (`lines_count = pages * 15`).
   - Ambang ketuntasan bulanan: minimal 150 baris (setengah juz) untuk TK/KB dan 300 baris (1 juz) untuk SD/SMP/SMA.
   - Alur pengajuan ujian tasmi' santri $\rightarrow$ antrean pengujian Tutor Siswa $\rightarrow$ Penerbitan Syahadah Kelulusan Tahfidz (PDF landscape dengan nomor otomatis).
   - Baseline Tahfidz santri 3 kali setahun (Dokumen Administrasi KS No. 11).

4. **Modul Supervisi Pembelajaran Kelas:**
   - Sesuai instrumen resmi: Bagian A (Administrasi, bobot 10), Bagian B (Pendahuluan, bobot 20), Bagian C (Kegiatan Inti, bobot 70).
   - **Enforcement Aturan Khusus Indikator 12:** Jika terdapat salah satu dari indikator 1 s/d 11 di Bagian C yang bernilai kurang dari 2 (Cukup/Kurang), maka indikator ke-12 otomatis divalidasi dan tidak diperbolehkan bernilai "Sangat Baik" (skor 3).
   - Cetak berkas instrumen supervisi kelas resmi ke format PDF A4.

5. **Modul 18 Arsip Dokumen Administrasi Kepala Sekolah:**
   - 18 jenis dokumen administrasi kepala sekolah lengkap dengan pemenuhan frekuensi (1x, 2x, 3x setahun, atau setiap bulan).
   - Pengunggahan administrasi bagi Guru Mapel, Guru Tahfidz, Wali Kelas, dan TU.
   - Fitur telaah (review), verifikasi, dan persetujuan (approval) oleh Kepala Sekolah dan Pengawas Yayasan.

6. **Modul Keuangan & Tagihan SPP:**
   - Input manual tagihan oleh Tata Usaha (TU).
   - Perhitungan rasio santri menunggak terhadap total santri (indikator performa AKFI TU).
   - Alur rekomendasi surat tagihan: TU mengajukan $\rightarrow$ Kepala Sekolah menyetujui $\rightarrow$ Sistem mengirim email resmi ke orang tua via PHPMailer dengan lampiran Surat Tagihan SPP ber-kop sekolah (PDF).

7. **Kuesioner Survei Kepuasan (53 Butir Pertanyaan Lengkap):**
   - Instrumen resmi kepuasan orang tua santri mencakup 10 bidang (Layanan Pembayaran, UKS, Fasilitas, Keamanan, Kebersihan CS, Akademik, Tahfidz, Komunikasi, Kepemimpinan, dan Loyalitas).
   - Survei dewan guru terhadap kepemimpinan Kepala Sekolah.
   - Survei kepuasan santri.

8. **Checklist Harian Keamanan (Security) & Kebersihan (CS):**
   - Checklist security harian yang terkunci otomatis pada tanggal server hari ini dengan penanda ketepatan waktu jam jaga sebelum 07:15 WIB.
   - Buku jurnal patroli keliling pos keamanan.
   - Buku mutasi kejadian dan penanganan insiden keamanan.
   - Checklist rutin harian, piket bulanan, dan gotong royong petugas kebersihan.
   - Buku Rawat Jaga Belanja (Braja) Tata Usaha untuk monitoring nol kesalahan belanja.

9. **Kegiatan Luar KBM, Panitia Lomba, SPMB & Jejak Alumni:**
   - Presensi keterlibatan pegawai dalam kegiatan luar KBM (PHBI, manasik, outbond).
   - SK Penugasan guru pembina lomba resmi (OSN, O2SN, FLS2N).
   - Pencatatan prestasi kejuaraan siswa (lomba kedinasan & lomba swasta oleh Walas).
   - Penetapan guru berprestasi tahunan oleh Kepala Sekolah.
   - SPMB: Penetapan kuota target oleh Deputi $\rightarrow$ Input pendaftaran $\rightarrow$ 1-Click konversi calon santri menjadi siswa aktif resmi (auto-generate NIS).
   - Jejak alumni: Rekomendasi studi lanjut oleh wali kelas dan pelacakan kampus/sekolah favorit alumni.

10. **Portal Orang Tua Santri:**
    - Pemantauan nilai dan capaian hafalan anak secara real-time.
    - Pengunduhan Rapor Bulanan dan Syahadah Tahfidz resmi.
    - Informasi tagihan SPP dan unduh surat pemberitahuan tagihan.

11. **Pusat Log Audit & Backup / Restore Database:**
    - Rekaman kronologis aktivitas pengguna (IP address, role, waktu, perubahan data).
    - Pencadangan (backup) skema dan data database ke berkas `.sql`.
    - Pemulihan (restore) database dari berkas cadangan.

---

## 3. Petunjuk Instalasi di XAMPP

1. **Pastikan Apache & MySQL aktif di XAMPP Control Panel.**
2. Letakkan folder aplikasi ini di dalam direktori htdocs XAMPP:
   ```
   c:\xampp\htdocs\akfioke
   ```
3. **Database Setup:**
   - Buka phpMyAdmin di browser: `http://localhost/phpmyadmin`
   - Buat database baru bernama: `akfioke_db` (Collation: `utf8mb4_unicode_ci`)
   - Impor berkas skema: `c:\xampp\htdocs\akfioke\database\schema.sql`
   - Impor berkas data awal (seed): `c:\xampp\htdocs\akfioke\database\seed.sql`
   *(Atau jalankan via command prompt: `mysql -u root akfioke_db < database/schema.sql` lalu `database/seed.sql`)*
4. **Buka Aplikasi di Peramban Web (Browser):**
   ```
   http://localhost/akfioke
   ```
   Aplikasi akan otomatis mengarahkan ke halaman login.

---

## 4. Akun Default Pengujian (Test Accounts)

Seluruh akun di bawah ini telah disiapkan di dalam database dengan kata sandi bawaan yang sama:
```
Password: password123
```
*(Tersedia pula tombol 1-Click Instant Login pada halaman login untuk kemudahan pengujian tanpa perlu mengetik).*

| Role / Jabatan | Username | Deskripsi Hak Akses Utama |
|---|---|---|
| **Operator Yayasan** | `operator.yayasan` | Akses penuh superadmin, kelola user, unit, audit log, backup/restore DB |
| **Direktur Pendidikan** | `direktur.pendidikan` | Pengesahan akhir Rapor AKFI, rekap yayasan, approval eksekutif |
| **Deputi Pendidikan** | `deputi.pendidikan` | Monitoring seluruh satuan unit (KB s/d SMA), target SPMB |
| **Kepala Sekolah (SD)** | `kepsek.sd` | Verifikasi 18 dokumen administrasi, penilaian AKFI bawahan, supervisi |
| **Kepala Sekolah (TK)** | `kepsek.tk` | Manajemen dan evaluasi unit TK & KB |
| **Kepala Sekolah (SMP)**| `kepsek.smp` | Manajemen dan evaluasi unit SMP |
| **Kepala Sekolah (SMA)**| `kepsek.sma` | Manajemen dan evaluasi unit SMA |
| **Waka Kurikulum** | `waka.kurikulum` | Jadwal supervisi, presensi guru & kajian Sabtu, ledger nilai |
| **Waka Kesiswaan** | `waka.kesiswaan` | Kegiatan luar KBM, pembina lomba, presensi siswa |
| **Wali Kelas** | `walas.sd` | Input nilai rapor siswa, presensi kelas, PTK, rekomendasi studi alumni |
| **Guru Mata Pelajaran**| `guru.sd` | Upload RPP/Modul ajar, input nilai bulanan, Best Practice, supervisi |
| **Guru Tahfidz** | `guru.tahfidz` | Input setoran santri (Kalkulator Madinah), mutaba'ah hafalan |
| **Koordinator Tahfidz** | `koor.tahfidz` | Kelola halaqah santri, baseline tahfidz 3x setahun |
| **Tutor Siswa** | `tutor.siswa` | Pengujian tasmi' santri, penerbitan Syahadah Tahfidz PDF |
| **Tutor Sebaya** | `tutor.sebaya` | Input nilai capaian tahfidz bulanan guru (target 1/2 - 1 juz) |
| **Tata Usaha (TU)** | `tu.sd` | Upload administrasi TU, input manual tagihan SPP, pengajuan surat tagihan |
| **Security** | `security.sd` | Checklist pos jaga harian (server date), buku patroli, buku insiden |
| **Cleaning Service** | `cs.sd` | Checklist kebersihan rutin, piket bulanan, gotong royong |
| **Panitia Lomba** | `panitia.lomba` | Input data kejuaraan dan sertifikat lomba kedinasan (OSN/O2SN/FLS2N) |
| **Operator SPMB** | `operator.spmb` | Pendaftaran santri baru dan konversi instan menjadi siswa aktif |
| **Orang Tua Santri** | `ortu.santri` | Portal rapor anak, syahadah tahfidz, informasi SPP, kuesioner 53 butir |
| **Santri / Siswa** | `santri.sd` | Portal kuesioner pembelajaran santri |
| **Alumni** | `alumni.sd` | Portal pelacakan jejak alumni dan kampus favorit |
| **Pembina Yayasan** | `pembina.yayasan` | Dashboard eksekutif dan audit yayasan |
| **Pengawas Yayasan** | `pengawas.yayasan`| Verifikasi dan review administrasi sekolah |
| **Pengurus Yayasan** | `pengurus.yayasan`| Monitoring rekapitulasi performa yayasan |
| **Keuangan Yayasan** | `keuangan.yayasan` | Rekapitulasi pembayaran dan tunggakan seluruh unit |

---

## 5. Struktur Direktori Proyek

```
akfioke/
├── app/
│   └── Controllers/
│       ├── AbsensiController.php       # Presensi guru, staf, siswa & kajian Sabtu
│       ├── AdministrasiController.php  # 18 berkas KS, upload guru & TU, approval
│       ├── AkfiController.php          # 10 template AKFI, auto-data, rapor PDF
│       ├── AlumniController.php        # Rekomendasi studi & pelacakan alumni
│       ├── AuditBackupController.php   # Audit logs & database backup/restore
│       ├── AuthController.php          # Login, ganti password, rate limiter
│       ├── ChecklistController.php     # Security, patroli, insiden, CS, Braja TU
│       ├── DashboardController.php     # Dynamic dashboard per role & Chart.js
│       ├── MasterController.php        # Unit, TA, pegawai, siswa, template AKFI
│       ├── NilaiBulananController.php  # Input nilai, ledger, rapor bulanan PDF
│       ├── PanitiaLombaController.php  # Prestasi siswa & guru, Best Practice
│       ├── ParentController.php        # Portal orang tua santri
│       ├── SpmbController.php          # Target & registrasi penerimaan santri
│       ├── SupervisiController.php     # Supervisi kelas (A/B/C) & aturan poin 12
│       ├── SurveyController.php        # 53 butir survei orang tua, kepsek, siswa
│       ├── TagihanController.php       # Tagihan SPP, approval, PHPMailer dispatch
│       └── TahfidzController.php       # Kalkulator Madinah, antrean ujian, syahadah
├── config/
│   ├── app.php                         # Pengaturan aplikasi & zona waktu WIB
│   ├── database.php                    # Konfigurasi PDO MariaDB/MySQL
│   ├── mail.php                        # Konfigurasi default SMTP PHPMailer
│   └── security.php                    # Enkripsi AES-256-CBC, session security
├── core/
│   ├── AuditLog.php                    # Pencatatan audit trail aman
│   ├── Auth.php                        # Sesi aman, anti-IDOR, role switcher
│   ├── Controller.php                  # Base Controller & flash message
│   ├── CSRF.php                        # Proteksi token CSRF
│   ├── Database.php                    # Singleton PDO connection wrapper
│   ├── ExportHelper.php                # Ekspor Excel anti formula injection & PDF
│   ├── Model.php                       # Base Model
│   ├── Notification.php                # Mesin notifikasi in-app
│   ├── Router.php                      # HTTP Router dengan parameter dinamis
│   ├── SecurityHelper.php              # XSS escape, format Rupiah & tanggal Indo
│   ├── Uploader.php                    # Pengunggah berkas aman dengan MIME finfo
│   └── View.php                        # Template engine renderer
├── database/
│   ├── schema.sql                      # Skema 50 tabel database InnoDB
│   └── seed.sql                        # Data awal: surah, hal. madinah, akun, indikator
├── public/
│   ├── assets/
│   │   ├── css/app.css                 # Style kustom modern & tema sidebar
│   │   ├── js/chart.min.js             # Chart.js 4.4.4 offline
│   │   └── webfonts/                   # FontAwesome 6 fonts lokal
│   ├── index.php                       # Single entry point
│   └── .htaccess                       # Rewrite engine & security headers
├── storage/
│   ├── backups/                        # Arsip cadangan database (.sql)
│   └── uploads/                        # Berkas dokumen & bukti piagam tersimpan
├── vendor/                             # Dependensi Composer (offline ready)
├── views/                              # Template antarmuka (75 berkas view)
│   ├── administrasi/
│   ├── akfi/
│   ├── alumni/
│   ├── attendance/
│   ├── audit/
│   ├── auth/
│   ├── checklist/
│   ├── dashboard/
│   ├── layouts/
│   ├── master/
│   ├── nilai/
│   ├── panitia/
│   ├── parent/
│   ├── spmb/
│   ├── supervisi/
│   ├── survey/
│   └── tahfidz/
├── composer.json
└── README.md
```

---

## 6. Lisensi & Hak Cipta

Aplikasi ini dikembangkan untuk kepentingan operasional dan evaluasi kinerja **Yayasan Islam Al Kahfi Batam**. Seluruh hak cipta dilindungi undang-undang.
