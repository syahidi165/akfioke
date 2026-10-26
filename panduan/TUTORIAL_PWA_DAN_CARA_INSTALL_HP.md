# PANDUAN LENGKAP PROGRESSIVE WEB APP (PWA) & RESPONSIVE MOBILE
## Cara Memasang di HP & Penyesuaian Tampilan Mobile
### Yayasan Islam Al Kahfi Batam — Sistem Manajemen Sekolah Terpadu (MASEKO)

---

## 1. Mengapa Tombol Pasang Awalnya Tidak Muncul di HP (Jaringan Lokal)?

Saat Anda mengakses web melalui jaringan Wi-Fi lokal menggunakan alamat IP (contoh: `http://192.168.x.x/akfioke`):
- **Penyebab Utama:** Browser modern (Google Chrome, Microsoft Edge, Safari) menerapkan standar keamanan ketat di mana event instalasi otomatis (`beforeinstallprompt`) **diblokir jika website menggunakan protokol HTTP biasa** (bukan HTTPS). Browser hanya mengizinkan Service Worker dan instalasi otomatis jika diakses melalui:
  1. `http://localhost` (khusus di komputer server/laptop itu sendiri).
  2. Protokol resmi **HTTPS** (seperti domain sekolah ber-SSL atau tunnel HTTPS).

### Solusi yang Telah Kami Terapkan:
1. **Tombol Pasang Dibuat Selalu Tampil**:
   - Tombol **"Pasang"** di Navbar atas dan tombol **"Pasang Aplikasi MASEKO di HP"** di halaman Login sekarang **selalu tampil di layar**.
2. **Modal Panduan Interaktif Otomatis**:
   - Jika pengguna mengakses via HTTP lokal (`192.168.x.x`) dan menekan tombol tersebut, sistem akan langsung memunculkan **jendela panduan visual langkah demi langkah** cara memasang aplikasi secara manual lewat menu browser.
   - Saat aplikasi nanti sudah online di domain resmi HTTPS sekolah, tombol ini langsung memicu pop-up instalasi otomatis 1-klik!

---

## 2. Cara Memasang Aplikasi di HP Saat Akses via IP Lokal (`192.168.x.x`)

Walaupun dialog otomatis dibatasi di HTTP lokal, Anda **tetap bisa memasang aplikasi di HP detik ini juga** dengan cara manual berikut:

### Di HP Android (Google Chrome):
1. Buka `http://[IP-LAPTOP-ANDA]/akfioke` di Chrome HP Anda.
2. Ketuk ikon **Titik Tiga (⋮)** di pojok kanan atas browser Chrome.
3. Pilih menu **"Tambahkan ke Layar Utama" (Add to Home screen)** atau **"Instal aplikasi"**.
4. Ketuk **Instal / Tambah**.
5. Ikon **MASEKO** dengan logo resmi Al Kahfi akan langsung muncul di layar utama HP Anda dan berjalan dalam mode aplikasi penuh (*standalone*).

### Di iPhone / iPad (Apple Safari):
1. Buka website di Safari.
2. Ketuk tombol **Bagikan (Share)** (ikon kotak dengan panah ke atas di bilah bawah).
3. Gulir ke bawah, lalu pilih **"Tambahkan ke Layar Utama" (Add to Home Screen)**.
4. Ketuk **Tambah**.

---

## 3. Cara Menguji PWA dengan Instalasi Otomatis 1-Klik dari Laptop ke HP

Jika Anda ingin menguji fitur instalasi otomatis 1-klik di HP sebelum web dideploy ke hosting:

### Gunakan Cloudflare Tunnel (Gratis, Cepat & Resmi HTTPS)
1. Buka PowerShell di laptop Anda.
2. Jalankan:
   ```powershell
   cloudflared tunnel --url http://localhost
   ```
3. Cloudflare akan memberikan URL HTTPS gratis resmi, contoh:
   `https://subdomain-acak.trycloudflare.com`
4. Buka URL tersebut di HP Anda:
   `https://subdomain-acak.trycloudflare.com/akfioke/`
5. Karena sudah berprotokol HTTPS resmi, tombol **"Pasang Aplikasi"** akan langsung memunculkan dialog instalasi asli Google Chrome dalam 1 klik!

---

## 4. Perbaikan Tampilan Mobile (Responsiveness) yang Telah Dilakukan

Kami telah merombak total antarmuka untuk layar smartphone (HP) agar nyaman dipandang, lega, dan terasa seperti aplikasi native:

1. **Sidebar Drawer & Backdrop Halus**:
   - Di HP, sidebar kini tersembunyi secara default sehingga ruang baca layar 100% penuh.
   - Menekan tombol menu hamburger (☰) akan meluncurkan menu samping (*drawer*) dari kiri dengan latar belakang gelap (*backdrop blur*).
   - Mengetuk latar belakang atau tombol **(X)** di atas sidebar akan menutup menu kembali secara mulus.
2. **Stat Cards 2 Kolom di Mobile**:
   - Sebelumnya kartu statistik berukuran besar dan bertumpuk 1 kolom panjang.
   - Sekarang dirancang menggunakan **Grid 2 Kolom yang kompak**:
     - Ikon berukuran proporsional (36px).
     - Nilai angka dan rupiah tertata rapi tanpa kepotong.
     - Label teks ringkas dan nyaman dibaca.
3. **Header Navbar Ringkas & Bebas Tumpuk**:
   - Di layar kecil, elemen header disederhanakan:
     - Tombol pasang berubah menjadi ikon pasang yang manis.
     - Dropdown Tahun Ajaran dan Peran diformat menjadi pill kompak.
     - Logo unit sekolah yang lebar otomatis disembunyikan di HP agar tidak memenuhi baris header.
4. **Tabel Responsif (Touch-Friendly Table)**:
   - Tabel Status Persetujuan Tahun Ajaran dan data lainnya telah dibungkus dengan container `.table-responsive` yang mendukung scroll sentuh halus (*touch scroll*), mencegah tabel merusak lebar layar HP.
