# PANDUAN PENERAPAN SISTEM MULTI-TIER STORAGE
## Hot Storage (Hosting Server) & Cold Storage (NAS Lokal Sekolah)
### Yayasan Islam Al Kahfi Batam — Sistem Manajemen Sekolah Terpadu (MASEKO)

---

## 1. Konsep dan Arsitektur Penyimpanan

Sistem penyimpanan terpadu MASEKO menggunakan strategi **Tiered Storage (Penyimpanan Bertingkat)** yang membagi data menjadi dua tingkatan:

```
+-----------------------------------------------------------------------------------+
|                           SISTEM TERPADU AKFIOKE (MASEKO)                        |
+-----------------------------------------------------------------------------------+
                                          |
                +-------------------------+-------------------------+
                |                                                   |
                v                                                   v
    [ HOT STORAGE (HOSTING) ]                           [ COLD STORAGE (NAS LOKAL) ]
  Media : Hosting NVMe/SSD Server                     Media : NAS Sekolah (Synology/QNAP/TrueNAS)
  Data  : Tahun Ajaran AKTIF BERJALAN                 Data  : Seluruh Tahun Ajaran BERLALU / ARSIP
  Sifat : Kecepatan tinggi, latensi rendah            Sifat : Kapasitas gigantis (TB), hemat kuota hosting
  Isi   : Dokumen harian, rapor berjalan,              Isi   : Dokumen administrasi lampau, sertifikat lama,
          nilai, absensi, berkas transaksi                      snapshot database SQL mandiri per tahun ajaran
```

### Keunggulan Sistem Ini:
1. **Hosting Hemat & Bebas Penuh Kuota**: Hosting web tidak akan kehabisan ruang disk karena file-file dokumen lama (seperti PDF administrasi guru, bukti tagihan, sertifikat lomba) otomatis dipindahkan ke NAS lokal sekolah.
2. **Pengambilan Berkas Transparan (*Transparent Retrieval*)**: Ketika guru, kepala sekolah, atau orang tua membuka dokumen dari tahun ajaran lampau, sistem **tidak error 404**. `StorageManager` secara otomatis mengalirkan (*chunk stream*) berkas dari NAS lokal langsung ke browser pengguna.
3. **Snapshot Database Mandiri**: Saat tahun ajaran diarsipkan, sistem secara otomatis mengekstrak seluruh data tabel tahun ajaran tersebut ke dalam file SQL snapshot di NAS lokal sebagai cadangan permanen.
4. **Fitur Rehydrate Fleksibel**: Kapan saja jika dokumen tahun ajaran lampau perlu diedit kembali, Operator Yayasan dapat mengembalikannya ke Hot Storage dengan 1 klik tombol.

---

## 2. Pilihan Skenario Penerapan Hardware & Jaringan

Pilih salah satu skenario berikut sesuai infrastruktur sekolah Anda:

### Skenario A: Server Web Berada di Jaringan Lokal Sekolah (On-Premises / Hybrid LAN)
Cocok jika server hosting web terpasang di jaringan sekolah atau satu data center dengan NAS.
- **Protokol**: Local Directory, Windows UNC Share (`\\192.168.1.100\akfi_cold_storage`), Mapped Network Drive (`Z:\akfi_cold_storage`), atau Linux Mount NFS/SMB (`/mnt/nas/akfi_cold_storage`).
- **Driver di Web**: `local_path`.
- **Kelebihan**: Kecepatan transfer gigabit LAN, sangat mudah dikonfigurasi, tanpa memerlukan koneksi internet.

### Skenario B: Web Berada di Cloud Hosting (cPanel / VPS) dan NAS Berada di Kantor Sekolah
Cocok jika web MASEKO dihosting di cloud (misal cPanel/VPS IDCloudHost, Niagahoster, AWS, DigitalOcean) dan NAS fisik berada di kantor Yayasan/Sekolah Al Kahfi Batam.

Terdapat 2 opsi koneksi:
1. **Opsi 1: Menggunakan VPN Mesh Privat (Rekomendasi Terbaik — Contoh: Tailscale / WireGuard)**
   - Pasang aplikasi **Tailscale** (gratis) di server VPS dan di NAS Synology/TrueNAS.
   - VPS dan NAS akan berada di dalam satu jaringan IP privat virtual (misal `100.x.x.x`).
   - Server VPS dapat langsung mengakses folder NAS via mount `/mnt/nas` atau direct path menggunakan driver `local_path`.
   - **Kelebihan**: Sangat aman (enkripsi WireGuard end-to-end), tidak perlu membuka port router sekolah, stabil dan otomatis reconnect.

2. **Opsi 2: Menggunakan Protokol WebDAV over HTTPS**
   - Aktifkan paket **WebDAV Server** di NAS Synology / QNAP.
   - Atur port forwarding di router sekolah (default port WebDAV HTTPS: `5006`) atau gunakan Cloudflare Tunnel / DDNS (misal: `https://nas.alkahfibatam.sch.id:5006`).
   - **Driver di Web**: `webdav`. Masukkan URL, username, dan password NAS.

---

## 3. Langkah-Langkah Penyiapan NAS Lokal (Synology / QNAP / TrueNAS / Windows)

### Langkah 1: Buat Folder Bersama (*Shared Folder*) di NAS
1. Masuk ke dashboard admin NAS Anda (misal Synology DSM).
2. Buka **Control Panel** > **Shared Folder** > Klik **Create**.
3. Beri nama folder: `akfi_cold_storage`.
4. Atur kuota penyimpanan (misal: `1 TB` atau *Unlimited*).

### Langkah 2: Buat Akun Pengguna Khusus Aplikasi
1. Buka **Control Panel** > **User & Group** > Klik **Create User**.
2. Masukkan username: `akfi_nas_user` dan password yang kuat.
3. Berikan izin **Read & Write (Baca/Tulis)** ke folder `akfi_cold_storage`.

### Langkah 3: Aktifkan Layanan Berbagi Berkas
- **Jika menggunakan SMB/Windows Share**:
  Buka **File Services** > Centang **Enable SMB Service**.
  Folder Anda siap diakses di alamat: `\\192.168.1.100\akfi_cold_storage`.
- **Jika menggunakan WebDAV**:
  Buka **Package Center** > Pasang **WebDAV Server** > Aktifkan HTTPS pada port 5006.

---

## 4. Langkah-Langkah Konfigurasi di Web Aplikasi MASEKO

1. Login ke aplikasi sebagai **Operator Yayasan**.
2. Buka menu **Master Data** > **Tahun Ajaran** (`/master/academic-years`).
3. Perhatikan panel baru di bagian atas:
   **"Sistem Penyimpanan Data Bertingkat: Hot Storage (Hosting) & Cold Storage (NAS Lokal)"**.
4. Klik tombol **`[⚙️ Konfigurasi NAS]`** di pojok kanan atas panel.
5. Pada modal yang muncul:
   - Centang **Aktifkan Integrasi Cold Storage (NAS Lokal Sekolah)**.
   - Pilih **Metode / Driver**:
     * **Direktori Lokal / Mount Drive / Samba UNC Share**:
       Masukkan path folder, contoh:
       - Windows: `\\192.168.1.100\akfi_cold_storage` atau `Z:\akfi_cold_storage`
       - Linux/cPanel: `/mnt/nas/akfi_cold_storage` atau path folder arsip yang telah dimount.
     * **WebDAV Remote**:
       Masukkan URL (contoh: `https://nas.sekolah.id:5006`), Path folder (`/akfi_cold_storage`), Username, dan Password.
6. Klik tombol **`[🔌 Uji Koneksi Sekarang]`**.
   - Sistem akan melakukan uji tulis, verifikasi permission, dan menghitung sisa kapasitas disk NAS secara otomatis.
   - Jika muncul pesan **"Koneksi ke NAS BERHASIL"**, klik **`[Simpan Konfigurasi]`**.

---

## 5. Standar Operasional Prosedur (SOP) Tutup Tahun Ajaran & Pengarsipan ke NAS

Saat pergantian periode akademik (misal akhir semester atau tahun ajaran baru):

### Tahap 1: Pengajuan dan Persetujuan Transisi
1. Operator Yayasan mengajukan Tahun Ajaran Baru di menu **Tahun Ajaran**.
2. Masing-masing Operator Unit Sekolah (TK, SD, SMP, SMA, Quran Center) memeriksa kesiapan rapor dan ledger, lalu menyetujui pengajuan.

### Tahap 2: Pengaktifan Tahun Ajaran Baru
1. Setelah persetujuan 100% lengkap, Operator Yayasan mengklik tombol **`[▶️ Aktifkan]`** pada Tahun Ajaran Baru.
2. Tahun ajaran lama otomatis beralih status menjadi **Non-Aktif / Arsip**.
3. Tahun ajaran baru otomatis berjalan di **Hot Storage (Hosting Server)**.

### Tahap 3: Pemindahan Data Arsip ke Cold Storage NAS (Metode Aman Copy-Verify & Progress Bar)
1. Pada tabel Tahun Ajaran, baris tahun ajaran yang baru saja non-aktif akan menampilkan status:
   `⚠️ Di Hot (Belum Diarsip)`.
2. Klik tombol **`[❄️ Arsip ke NAS]`** (Dapat dilakukan oleh **Operator Yayasan** maupun **Operator Sekolah**).
3. **Jendela Interaktif Pemindahan Berkas Aman** akan terbuka dengan fitur:
   - **Jaminan Keamanan Berkas (Two-Phase Copy & Verifikasi)**: Sistem menggunakan prinsip *Copy Data* terlebih dahulu. Berkas sumber di hosting **hanya dihapus jika salinan di NAS telah 100% terverifikasi utuh dan ukuran file cocok**. Jika terjadi kegagalan atau koneksi terputus di tengah jalan, berkas asli di hosting **TETAP AMAN 100% dan tidak akan hilang**.
   - **Progress Bar Real-Time (%)**: Menampilkan persentase berjalan `0%` s/d `100%`, counter jumlah berkas (`X / Y Berkas`), dan indikator ukuran data (`MB / MB`).
   - **Live Activity Console**: Menampilkan log baris demi baris berkas yang sedang disalin dan diverifikasi secara langsung.
4. Klik tombol **`[▶️ Mulai Pindahkan Data]`**.
5. Setelah mencapai `100%`, sistem secara otomatis:
   - Memastikan seluruh berkas sumber di hosting telah dibersihkan secara aman (kuota hosting langsung lega!).
   - Membuat salinan **Snapshot Database SQL** tersendiri untuk tahun ajaran tersebut di NAS.
   - Mengubah badge tahun ajaran menjadi **`❄️ Cold Storage (NAS)`**.
6. Klik tombol **`[✓ Selesai & Muat Ulang]`**.

### Tahap 4: Mengakses Dokumen atau Mengembalikan Data (Rehydrate / Balik Hot)
- **Melihat / Mengunduh Berkas Lampau**: Guru atau admin dapat membuka menu administrasi / portofolio seperti biasa. Berkas akan dialirkan otomatis (*transparent streaming*) langsung dari NAS ke browser tanpa perlu memindahkan file manual.
- **Mengunduh Database SQL Arsip**: Di tabel Tahun Ajaran, klik tombol **`[📥 SQL]`** pada baris tahun ajaran cold storage untuk mengunduh dump database data tahun ajaran bersangkutan.
- **Mengembalikan ke Hosting (Balik Hot / Rehydrate)**: Jika ada kebutuhan membuka kembali tahun ajaran lama, klik tombol **`[🔥 Balik Hot]`**. Sistem akan membuka jendela progress bar yang sama dan menerapkan metode aman yang sama (Salin dari NAS ke Hosting &rarr; Verifikasi Integritas &rarr; Bersihkan salinan di NAS). Berkas di NAS tidak akan disentuh sebelum salinan di hosting 100% valid!
