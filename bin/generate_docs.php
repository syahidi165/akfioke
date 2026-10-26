<?php
// bin/generate_docs.php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

echo "Memulai penyusunan Buku Panduan Operasional AKFIOKE...\n";

$html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Panduan Operasional Sistem Informasi AKFIOKE</title>
    <style>
        @page {
            margin: 22mm 18mm 22mm 18mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            line-height: 1.55;
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }
        
        /* Cover Page */
        .cover-page {
            text-align: center;
            padding-top: 50px;
            page-break-after: always;
        }
        .cover-badge {
            display: inline-block;
            background: #0d9488;
            color: #ffffff;
            font-weight: bold;
            font-size: 11pt;
            padding: 6px 18px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 25px;
        }
        .cover-title {
            font-size: 24pt;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 10px 0;
            line-height: 1.25;
            text-transform: uppercase;
        }
        .cover-subtitle {
            font-size: 13pt;
            color: #0d9488;
            font-weight: 600;
            margin: 0 0 35px 0;
        }
        .cover-divider {
            width: 80px;
            height: 4px;
            background: #0d9488;
            margin: 0 auto 35px auto;
            border-radius: 2px;
        }
        .cover-desc {
            font-size: 10.5pt;
            color: #475569;
            max-width: 520px;
            margin: 0 auto 60px auto;
            line-height: 1.6;
        }
        .cover-meta {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px 24px;
            display: inline-block;
            text-align: left;
            font-size: 9.5pt;
            width: 80%;
            margin-bottom: 50px;
        }
        .cover-meta table {
            width: 100%;
            border-collapse: collapse;
        }
        .cover-meta td {
            padding: 4px 6px;
        }
        .cover-meta td.label {
            width: 130px;
            font-weight: bold;
            color: #334155;
        }
        .cover-footer {
            margin-top: 40px;
            font-size: 9pt;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }

        /* Headings */
        h1.chapter-title {
            font-size: 16pt;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 2.5px solid #0d9488;
            padding-bottom: 6px;
            margin-top: 25px;
            margin-bottom: 14px;
            text-transform: uppercase;
            page-break-before: always;
        }
        h1.chapter-title.first {
            page-break-before: avoid;
        }
        h2.section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #0d9488;
            margin-top: 18px;
            margin-bottom: 8px;
            border-left: 3.5px solid #0d9488;
            padding-left: 8px;
        }
        h3.sub-title {
            font-size: 10.5pt;
            font-weight: bold;
            color: #334155;
            margin-top: 12px;
            margin-bottom: 6px;
        }

        p {
            margin: 0 0 10px 0;
            text-align: justify;
        }

        /* Callouts / Alerts */
        .callout {
            padding: 10px 14px;
            border-radius: 6px;
            margin: 12px 0;
            font-size: 9.5pt;
        }
        .callout-info {
            background-color: #f0fdfa;
            border-left: 4px solid #0d9488;
            color: #134e4a;
        }
        .callout-warning {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }
        .callout-success {
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
            color: #166534;
        }
        .callout-danger {
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
            color: #991b1b;
        }

        /* Tables */
        table.doc-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 16px 0;
            font-size: 9pt;
        }
        table.doc-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            padding: 8px 10px;
            border: 1px solid #1e293b;
            text-align: left;
        }
        table.doc-table td {
            padding: 7px 10px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }
        table.doc-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        
        /* Steps list */
        ol.step-list {
            margin: 6px 0 12px 20px;
            padding: 0;
        }
        ol.step-list li {
            margin-bottom: 6px;
            padding-left: 4px;
        }
        ul.bullet-list {
            margin: 6px 0 12px 20px;
            padding: 0;
        }
        ul.bullet-list li {
            margin-bottom: 4px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 7.5pt;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-primary { background: #0d9488; color: #fff; }
        .badge-blue { background: #0284c7; color: #fff; }
        .badge-warning { background: #f59e0b; color: #fff; }
        .badge-success { background: #10b981; color: #fff; }
        .badge-danger { background: #ef4444; color: #fff; }
        .badge-dark { background: #334155; color: #fff; }

        /* TOC */
        .toc-list {
            list-style-type: none;
            padding: 0;
            margin: 20px 0;
            font-size: 9.5pt;
        }
        .toc-list li {
            padding: 5px 0;
            border-bottom: 1px dotted #cbd5e1;
            display: flex;
            justify-content: space-between;
        }
        .toc-chapter {
            font-weight: bold;
            color: #0f172a;
        }
    </style>
</head>
<body>

    <!-- HALAMAN COVER -->
    <div class="cover-page">
        <div class="cover-badge">Buku Panduan Resmi</div>
        <h1 class="cover-title">PANDUAN OPERASIONAL<br>SISTEM INFORMASI AKFIOKE</h1>
        <div class="cover-subtitle">Al Kahfi Indikator Kinerja, Administrasi, &amp; Penilaian Terpadu</div>
        <div class="cover-divider"></div>
        <div class="cover-desc">
            Panduan komprehensif tata kelola sistem informasi sekolah berbasis web terpadu untuk Direktur Pendidikan, Kepala Sekolah, Waka Kurikulum, Waka Kesiswaan, Wali Kelas, Guru Mata Pelajaran, Guru Diniyah, Guru Tahfidz, Staf Tata Usaha, Cleaning Services, Security, dan Orang Tua Santri di lingkungan Yayasan Islam Al Kahfi Batam.
        </div>

        <div class="cover-meta">
            <table>
                <tr>
                    <td class="label">Satuan Kerja :</td>
                    <td>Yayasan Islam Al Kahfi Batam (KB-TK, SD, SMP, SMA)</td>
                </tr>
                <tr>
                    <td class="label">Versi Sistem :</td>
                    <td>AKFIOKE Production Release v2.5</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Rilis :</td>
                    <td>September 2026</td>
                </tr>
                <tr>
                    <td class="label">Basis Teknologi :</td>
                    <td>PHP 8.2+ MVC Native, MySQL Database, Tailwind/Custom CSS, Dompdf Engine</td>
                </tr>
                <tr>
                    <td class="label">Keamanan :</td>
                    <td>Role-Based Access Control (RBAC), CSRF Protection, Anti-Formula Injection</td>
                </tr>
            </table>
        </div>

        <div class="cover-footer">
            &copy; 2026 Yayasan Islam Al Kahfi Batam &bull; Hak Cipta Dilindungi Undang-Undang
        </div>
    </div>

    <!-- HALAMAN DAFTAR ISI -->
    <h1 class="chapter-title first">DAFTAR ISI</h1>
    <ul class="toc-list">
        <li>
            <span class="toc-chapter">BAB 1: ARSITEKTUR SISTEM &amp; KEAMANAN HAK AKSES</span>
        </li>
        <li>
            <span class="toc-chapter">BAB 2: MANAJEMEN AKUN &amp; OTENTIKASI PENGGUNA</span>
        </li>
        <li>
            <span class="toc-chapter">BAB 3: PANDUAN KEPALA SEKOLAH &amp; DEPUTI PENDIDIKAN</span>
        </li>
        <li>
            <span class="toc-chapter">BAB 4: PANDUAN WALI KELAS (WALAS)</span>
        </li>
        <li>
            <span class="toc-chapter">BAB 5: PANDUAN GURU MATA PELAJARAN &amp; GURU DINIYAH</span>
        </li>
        <li>
            <span class="toc-chapter">BAB 6: PANDUAN GURU TAHFIDZ (MUSHAF MADINAH)</span>
        </li>
        <li>
            <span class="toc-chapter">BAB 7: PANDUAN WAKIL KURIKULUM &amp; WAKIL KESISWAAN</span>
        </li>
        <li>
            <span class="toc-chapter">BAB 8: PANDUAN TATA USAHA &amp; KEUANGAN (SPP BULANAN)</span>
        </li>
        <li>
            <span class="toc-chapter">BAB 9: PANDUAN CLEANING SERVICES (CS) &amp; SECURITY</span>
        </li>
        <li>
            <span class="toc-chapter">BAB 10: PANDUAN OPERATOR YAYASAN, PORTAL ORTU &amp; PEMELIHARAAN</span>
        </li>
    </ul>

    <div class="callout callout-info">
        <strong>Penting bagi Pengguna:</strong> Seluruh menu yang muncul pada bilah navigasi (sidebar) disesuaikan secara otomatis menurut <em>Peran Aktif (Active Role)</em> pengguna saat masuk ke dalam sistem. Apabila Anda memiliki lebih dari satu tugas/jabatan, gunakan fitur <strong>Peran (Role Switcher)</strong> di bilah atas untuk beralih ruang kerja.
    </div>

    <!-- BAB 1 -->
    <h1 class="chapter-title">BAB 1: ARSITEKTUR SISTEM &amp; KEAMANAN HAK AKSES</h1>
    <p>
        Sistem Informasi <strong>AKFIOKE</strong> dibangun khusus guna memenuhi standar manajemen pendidikan modern di lingkungan Yayasan Islam Al Kahfi Batam. Sistem ini mengintegrasikan seluruh jenjang pendidikan yang bernaung di bawah yayasan, yaitu:
    </p>
    <ul class="bullet-list">
        <li><strong>KB-TK Islam Al Kahfi</strong> (Kelompok Bermain &amp; Taman Kanak-Kanak)</li>
        <li><strong>SD Islam Al Kahfi</strong> (Sekolah Dasar)</li>
        <li><strong>SMP Islam Al Kahfi</strong> (Sekolah Menengah Pertama)</li>
        <li><strong>SMA Islam Al Kahfi</strong> (Sekolah Menengah Atas)</li>
    </ul>

    <h2 class="section-title">1.1 Matriks Peran &amp; Kewenangan Pengguna</h2>
    <table class="doc-table">
        <thead>
            <tr>
                <th style="width: 25%;">Peran (Role)</th>
                <th style="width: 20%;">Cakupan Unit</th>
                <th>Fungsi Utama dalam Sistem</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Direktur &amp; Deputi Pendidikan</strong></td>
                <td>Lintas Unit Yayasan</td>
                <td>Pemantauan eksekutif seluruh unit sekolah, monitoring rekapitulasi penilaian AKFI, persetujuan pembukaan Tahun Ajaran baru, serta evaluasi audit mutu pendidikan.</td>
            </tr>
            <tr>
                <td><strong>Kepala Sekolah</strong></td>
                <td>Unit Sekolah Masing-Masing</td>
                <td>Penilaian Rapor AKFI Guru/Karyawan, pencatatan kegiatan di luar KBM (Dokumen 9), penerbitan SK Penugasan Pembina Lomba, penetapan Guru Berprestasi, dan evaluasi checklist harian unit.</td>
            </tr>
            <tr>
                <td><strong>Wakil Kurikulum &amp; Kesiswaan</strong></td>
                <td>Unit Sekolah Masing-Masing</td>
                <td>Supervisi akademik kelas, presensi pembinaan &amp; kajian Sabtu guru, pelaporan prestasi siswa lomba resmi (OSN, O2SN, FLS2N), serta verifikasi perangkat ajar.</td>
            </tr>
            <tr>
                <td><strong>Wali Kelas (Walas)</strong></td>
                <td>Kelas Binaan</td>
                <td>Pengumpulan perangkat administrasi kelas, rekap absensi kehadiran bulanan siswa (target 100%), mutaba'ah harian, serta penerbitan rapor siswa (Bulanan, Tahfidz, Gabungan).</td>
            </tr>
            <tr>
                <td><strong>Guru Mapel / Diniyah / Tahfidz</strong></td>
                <td>Kelas / Halaqah yang Diampu</td>
                <td>Pengumpulan RPP/Modul Ajar, pencatatan jurnal mengajar, penilaian santri, setoran hafalan Qur'an standar Mushaf Madinah, dan rapor AKFI pribadi.</td>
            </tr>
            <tr>
                <td><strong>Tata Usaha (TU)</strong></td>
                <td>Unit Sekolah Masing-Masing</td>
                <td>Pencatatan tagihan &amp; pembayaran SPP, impor data siswa via Excel, cetak surat penagihan tunggakan ber-Kop Unit resmi, serta checklist administrasi kantor.</td>
            </tr>
            <tr>
                <td><strong>Cleaning Services &amp; Security</strong></td>
                <td>Lingkungan Unit Sekolah</td>
                <td>Pengisian checklist kebersihan harian per zona (pagi, siang, sore), pencatatan buku tamu &amp; patroli keamanan lingkungan sekolah.</td>
            </tr>
            <tr>
                <td><strong>Orang Tua Santri (Wali Murid)</strong></td>
                <td>Santri Bersangkutan</td>
                <td>Memantau kehadiran anak, mengakses rapor periodik, mengecek tagihan &amp; histori pembayaran SPP, serta pengisian survey kepuasan layanan sekolah.</td>
            </tr>
        </tbody>
    </table>

    <h2 class="section-title">1.2 Standar Keamanan Data</h2>
    <p>
        Sistem dilengkapi dengan serangkaian protokol keamanan siber aktif guna menjamin integritas data institusi:
    </p>
    <ul class="bullet-list">
        <li><strong>Proteksi Brute-Force:</strong> Akun otomatis terkunci sementara selama 15 menit jika terjadi 5 kali kegagalan kata sandi beruntun.</li>
        <li><strong>Proteksi CSRF (Cross-Site Request Forgery):</strong> Seluruh formulir perubahan data mewajibkan token token valid yang diverifikasi ketat di sisi server.</li>
        <li><strong>Anti-Formula Injection:</strong> Data ekspor Excel diproteksi secara otomatis dari karakter berbahaya (seperti <code>=, +, -, @</code>) untuk mencegah eksploitasi spreadsheet.</li>
        <li><strong>Sanitasi XSS &amp; Unggah Berkas:</strong> Sistem memverifikasi tipe MIME file asli, membatasi ukuran maksimal (5MB), serta mengubah nama file menjadi acak dan aman.</li>
    </ul>

    <!-- BAB 2 -->
    <h1 class="chapter-title">BAB 2: MANAJEMEN AKUN &amp; OTENTIKASI PENGGUNA</h1>
    
    <h2 class="section-title">2.1 Masuk ke Sistem (Login)</h2>
    <ol class="step-list">
        <li>Buka peramban (browser Google Chrome / Mozilla Firefox / Microsoft Edge) dan akses alamat web sistem: <code>http://[domain_sekolah]/akfioke</code>.</li>
        <li>Ketikkan <strong>Nama Pengguna (Username / NIPY)</strong> dan <strong>Kata Sandi (Password)</strong> Anda pada kotak yang disediakan.</li>
        <li>Klik tombol <strong>"Masuk ke Sistem"</strong>.</li>
        <li>Jika Anda login untuk pertama kali dengan password standar, sistem akan mengarahkan Anda ke formulir <strong>Wajib Ganti Kata Sandi</strong> demi keamanan akun Anda.</li>
    </ol>

    <div class="callout callout-warning">
        <strong>Ketentuan Kata Sandi Baru:</strong> Kata sandi baru minimal terdiri dari 8 karakter, mengandung kombinasi huruf besar, huruf kecil, dan angka. Jangan berikan kata sandi Anda kepada pihak lain.
    </div>

    <h2 class="section-title">2.2 Beralih Peran (Role Switcher) &amp; Tahun Ajaran</h2>
    <p>
        Bagi pegawai yang memiliki amanah rangkap (misalnya: seorang <em>Wali Kelas</em> yang sekaligus bertugas sebagai <em>Guru Mapel</em> atau <em>Panitia Lomba</em>):
    </p>
    <ol class="step-list">
        <li>Lihat pada pojok kanan atas bilah navigasi utama (Header).</li>
        <li>Temukan dropdown berlabel <strong>"Peran"</strong>.</li>
        <li>Pilih peran yang ingin Anda gunakan. Halaman akan otomatis disegarkan (*refresh*) dan menu bilah sisi akan menyesuaikan dengan peran terpilih.</li>
        <li>Di sebelah dropdown peran, terdapat dropdown <strong>"TA (Tahun Ajaran)"</strong> yang memungkinkan Anda melihat arsip data tahun-tahun sebelumnya tanpa mengubah data tahun ajaran aktif.</li>
    </ol>

    <!-- BAB 3 -->
    <h1 class="chapter-title">BAB 3: PANDUAN KEPALA SEKOLAH &amp; DEPUTI PENDIDIKAN</h1>

    <h2 class="section-title">3.1 Penilaian AKFI Guru &amp; Tenaga Kependidikan</h2>
    <p>
        AKFI (Al Kahfi Indikator Kinerja) merupakan instrumen evaluasi komprehensif bagi pendidik dan tenaga kependidikan dengan proporsi baku:
    </p>
    <table class="doc-table">
        <thead>
            <tr>
                <th>Komponen Evaluasi</th>
                <th>Bobot Standar</th>
                <th>Mekanisme Pengisian</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Bagian A: Kinerja Utama</strong></td>
                <td>60%</td>
                <td>Terdiri dari target perangkat ajar, ketepatan deadline, capaian kehadiran santri, dan kelulusan tahfidz. Dilengkapi kartu <em>Auto-Data Collector</em> yang otomatis menarik data riil dari sistem.</td>
            </tr>
            <tr>
                <td><strong>Bagian B: Sikap &amp; Loyalitas</strong></td>
                <td>20%</td>
                <td>Penilaian kualitatif oleh Kepala Sekolah meliputi integritas, keteladanan akhlak, kedisiplinan sholat berjamaah, dan keaktifan majelis kajian Sabtu.</td>
            </tr>
            <tr>
                <td><strong>Bagian C: Absensi &amp; Kegiatan</strong></td>
                <td>20%</td>
                <td>Terakumulasi otomatis dari rekapitulasi kehadiran mesin/fingerprint serta partisipasi kegiatan di luar KBM.</td>
            </tr>
        </tbody>
    </table>

    <div class="callout callout-info">
        <strong>Rumus Kegiatan di Luar KBM untuk AKFI:</strong><br>
        <code>Nilai (%) = (Jumlah Kegiatan yang Diikuti Guru &divide; Total Agenda Kegiatan Unit pada TA tersebut) &times; 100%</code><br>
        <em>Contoh:</em> Bila sekolah mengadakan 4 kegiatan dan Guru A hadir pada 1 kegiatan, maka skor indikator partisipasinya adalah 25%.
    </div>

    <h2 class="section-title">3.2 Dokumen No. 9: Kegiatan di Luar KBM &amp; Presensi</h2>
    <ol class="step-list">
        <li>Akses menu: <strong>Kepala Sekolah &rarr; Kegiatan di Luar KBM</strong>.</li>
        <li>Pada form <strong>"Tambah Kegiatan Luar KBM"</strong> di sisi kiri:
            <ul class="bullet-list">
                <li>Isi <strong>Nama Acara / Agenda</strong> (contoh: <em>Manasik Haji Cilik, Rihlah Akbar, Tarhib Ramadhan</em>).</li>
                <li>Tentukan <strong>Tanggal Pelaksanaan</strong> dan <strong>Lokasi Acara</strong>.</li>
                <li>Centang daftar guru/pegawai yang hadir pada acara tersebut (tersedia fitur <em>Pilih Semua</em> / <em>Batal</em>).</li>
                <li>Klik tombol <strong>"Simpan Kegiatan &amp; Presensi"</strong>.</li>
            </ul>
        </li>
        <li>Pada tabel <strong>"Daftar Kegiatan di Luar KBM"</strong>:
            <ul class="bullet-list">
                <li>Klik baris kegiatan atau tombol <strong>"X Pegawai"</strong> untuk membuka modal jendela rincian daftar hadir pegawai yang mengikuti kegiatan tersebut.</li>
            </ul>
        </li>
    </ol>

    <h2 class="section-title">3.3 Penugasan Panitia Lomba &amp; Cetak SK Resmi</h2>
    <ol class="step-list">
        <li>Akses menu: <strong>Kepala Sekolah &rarr; Penugasan Panitia Lomba</strong>.</li>
        <li>Pilih guru pembina, pilih ajang lomba (<strong>OSN / O2SN / FLS2N</strong>), tentukan nomor SK, dan centang santri-santri yang dibina.</li>
        <li>Klik <strong>"Simpan &amp; Terbitkan SK"</strong>.</li>
        <li><strong>Mencetak SK (Format PDF):</strong> Pada tabel penugasan, klik tombol biru <strong>"SK"</strong>. Sistem otomatis menghasilkan Surat Keputusan resmi ber-Kop Surat Unit Sekolah lengkap dengan rincian siswa kontingen dan tanda tangan Kepala Sekolah.</li>
        <li><strong>Menandai Lomba Selesai:</strong> Ketika kejuaraan selesai diselenggarakan, Kepala Sekolah cukup menekan tombol hijau <strong>"Tandai Lomba Selesai"</strong> pada kolom Status Lomba.</li>
    </ol>

    <h2 class="section-title">3.4 Penetapan Guru Berprestasi</h2>
    <ol class="step-list">
        <li>Akses menu: <strong>Kepala Sekolah &rarr; Guru Berprestasi</strong>.</li>
        <li>Pilih guru penerima apresiasi, masukkan nama penghargaan, tingkat (Satuan Unit / Yayasan / Kota / Nasional), tahun, dan berkas bukti piagam/sertifikat (PDF/JPG).</li>
        <li>Data penghargaan ini otomatis muncul sebagai lencana prestasi pada dasbor guru yang bersangkutan.</li>
        <li>Kepala Sekolah dapat memperbarui status atau berkas bukti prestasi kapan saja melalui tombol <strong>"Edit"</strong> di tabel penetapan.</li>
    </ol>

    <h2 class="section-title">3.5 Pratinjau Rapor AKFI Mandiri Kepala Sekolah</h2>
    <p>
        Kepala Sekolah kini dapat melihat rapor kinerja pribadinya secara mandiri melalui menu <strong>Rapor AKFI Saya</strong> untuk mengevaluasi indikator kepemimpinan, manajerial sekolah, dan supervisi pendidik.
    </p>

    <!-- BAB 4 -->
    <h1 class="chapter-title">BAB 4: PANDUAN WALI KELAS (WALAS)</h1>

    <h2 class="section-title">4.1 Pengumpulan Perangkat Administrasi Walas</h2>
    <p>
        Perangkat administrasi wali kelas telah dioptimalkan dengan orientasi waktu yang memudahkan pemenuhan target berkas:
    </p>
    <ul class="bullet-list">
        <li><strong>Dokumen Sifat Bulanan:</strong> Menampilkan matriks grid 12 bulan (Juli s.d. Juni). Wali kelas dapat langsung mengklik tombol bulan bersangkutan untuk mengunggah berkas laporan bulanan kelas.</li>
        <li><strong>Dokumen Sifat Semesteran:</strong> Menyediakan kolom khusus Semester Ganjil dan Semester Genap.</li>
        <li><strong>Dokumen Sifat Tahunan:</strong> Menampilkan kolom target tunggal tahun ajaran aktif.</li>
    </ul>

    <h2 class="section-title">4.2 Absensi Bulanan Siswa &amp; Visual Tracker 12 Bulan</h2>
    <ol class="step-list">
        <li>Akses menu: <strong>Wali Kelas &rarr; Absensi Siswa Bulanan</strong>.</li>
        <li>Di bagian atas halaman, terdapat <strong>Matriks Indikator Status 12 Bulan</strong> (Juli s.d. Juni). Bulan yang telah diinput ditandai dengan warna hijau centang (<span class="badge badge-success">Terisi</span>), sedangkan bulan yang belum diisi ditandai warna abu-abu (<span class="badge badge-dark">Kosong</span>).</li>
        <li>Pilih bulan yang ingin diisi, lalu masukkan data rekapitulasi: <em>Sakit (S), Izin (I), dan Alpa (A)</em> untuk tiap santri.</li>
        <li>Klik <strong>"Simpan Presensi Kelas"</strong>.</li>
    </ol>

    <div class="callout callout-success">
        <strong>Akumulasi Kehadiran Santri untuk AKFI:</strong><br>
        Persentase capaian kehadiran santri yang ditarik oleh sistem AKFI dihitung secara kumulatif selama 1 tahun penuh: <code>% Capaian = (Total Hari Efektif Hadir Seluruh Siswa &divide; Total Hari Efektif Wajib) &times; 100%</code>.
    </div>

    <h2 class="section-title">4.3 Cetak Rapor Santri</h2>
    <p>
        Wali kelas memiliki kewenangan mengunduh dan mencetak dokumen evaluasi santri dalam format PDF resmi:
    </p>
    <ul class="bullet-list">
        <li><strong>Rapor Bulanan:</strong> Rekap perkembangan belajar, ketertiban, dan catatan akhlak santri per bulan.</li>
        <li><strong>Rapor Tahfidz:</strong> Rincian hafalan ziyadah, muroja'ah, kelancaran tajwid, dan status kelulusan target juz.</li>
        <li><strong>Rapor Gabungan:</strong> Laporan komprehensif hasil belajar akademik dan keislaman santri.</li>
    </ul>

    <!-- BAB 5 -->
    <h1 class="chapter-title">BAB 5: PANDUAN GURU MATA PELAJARAN &amp; DINIYAH</h1>

    <h2 class="section-title">5.1 Pengumpulan Perangkat Mengajar</h2>
    <ol class="step-list">
        <li>Akses menu: <strong>Guru Mapel / Guru Diniyah &rarr; Perangkat Mengajar</strong>.</li>
        <li>Pilih jenis dokumen (Modul Ajar/RPP, Program Tahunan, Program Semester, Silabus, Kriteria Ketercapaian Tujuan Pembelajaran).</li>
        <li>Unggah berkas dokumen (PDF/Word maksimal 5MB) sesuai tenggat waktu yang ditetapkan Waka Kurikulum.</li>
        <li>Periksa status verifikasi berkas: <span class="badge badge-warning">Menunggu Review</span> atau <span class="badge badge-success">Disetujui</span>.</li>
    </ol>

    <h2 class="section-title">5.2 Jurnal Mengajar &amp; Presensi Kelas</h2>
    <ol class="step-list">
        <li>Akses menu: <strong>Jurnal Mengajar Harian</strong>.</li>
        <li>Pilih rombel kelas, jam pelajaran, dan materi pokok bahasan yang diajarkan hari ini.</li>
        <li>Tandai kehadiran santri di kelas tersebut.</li>
        <li>Tuliskan catatan kejadian khusus atau rangkuman hasil pembelajaran, lalu klik <strong>"Simpan Jurnal"</strong>.</li>
    </ol>

    <!-- BAB 6 -->
    <h1 class="chapter-title">BAB 6: PANDUAN GURU TAHFIDZ (MUSHAF MADINAH)</h1>

    <h2 class="section-title">6.1 Standar Al-Qur'an &amp; Target Hafalan</h2>
    <p>
        Seluruh pencatatan dan evaluasi tahfidz di lingkungan Al Kahfi merujuk pada standar baku <strong>Mushaf Madinah Rasm Utsmani (1 Juz = 20 Halaman, 1 Halaman = 15 Baris)</strong>.
    </p>

    <h2 class="section-title">6.2 Pencatatan Setoran Hafalan Harian</h2>
    <ol class="step-list">
        <li>Akses menu: <strong>Guru Tahfidz &rarr; Setoran Hafalan</strong>.</li>
        <li>Pilih nama santri di halaqah Anda.</li>
        <li>Pilih kategori setoran: <strong>Ziyadah</strong> (hafalan baru) atau <strong>Muroja'ah</strong> (pengulangan hafalan lama).</li>
        <li>Pilih nama Surah, Ayat Awal, Ayat Akhir, Halaman, serta nilai kelancaran (Mumtaz / Jayyid Jiddan / Jayyid / Maqbul).</li>
        <li>Klik <strong>"Simpan Setoran"</strong>. Sistem otomatis menghitung akumulasi juz yang telah diselesaikan santri.</li>
    </ol>

    <h2 class="section-title">6.3 Presensi Bulanan Santri Halaqah</h2>
    <p>
        Khusus bagi Guru Tahfidz, menu presensi bulanan dirancang spesifik hanya memuat santri-santri yang terdaftar di dalam kelompok halaqah bimbingan masing-masing, memudahkan pengawasan kedisiplinan halaqah Al-Qur'an.
    </p>

    <h2 class="section-title">6.4 Perangkat Mengajar Guru Tahfidz</h2>
    <p>
        Sama halnya dengan guru mapel, guru tahfidz wajib mengunggah administrasi bimbingan Al-Qur'an (Rencana Program Tahfidz, Matriks Target Juz, dan Buku Agenda Mutaba'ah) pada menu <strong>Perangkat Mengajar Tahfidz</strong>.
    </p>

    <!-- BAB 7 -->
    <h1 class="chapter-title">BAB 7: PANDUAN WAKIL KURIKULUM &amp; KESISWAAN</h1>

    <h2 class="section-title">7.1 Kehadiran &amp; Kajian Pembinaan Guru (Sabtu)</h2>
    <ol class="step-list">
        <li>Akses menu: <strong>Waka Kurikulum &rarr; Kehadiran &amp; Kajian Guru</strong>.</li>
        <li>Gunakan <strong>Visual Tracker Status Bulanan</strong> untuk melihat bulan-bulan yang sudah tercatat.</li>
        <li>Pilih tanggal pelaksanaan kajian majelis taklim guru, isi judul tema pembinaan dan pemateri.</li>
        <li>Centang presensi kehadiran asatidz/pegawai yang hadir, lalu klik <strong>"Simpan Presensi Kajian"</strong>. Data ini langsung terhubung ke penilaian Sikap &amp; Loyalitas AKFI guru.</li>
    </ol>

    <h2 class="section-title">7.2 Supervisi Akademik Guru</h2>
    <ol class="step-list">
        <li>Akses menu: <strong>Supervisi &rarr; Instrumen Supervisi</strong>.</li>
        <li>Pilih guru yang disupervisi, rombel kelas, dan mata pelajaran.</li>
        <li>Lakukan penilaian terhadap 3 tahapan kegiatan:
            <ul class="bullet-list">
                <li><strong>Tahap A: Kegiatan Pendahuluan (Bobot 20)</strong> - Apersepsi, kesiapan siswa, motivasi.</li>
                <li><strong>Tahap B: Kegiatan Inti Pembelajaran (Bobot 70)</strong> - Penguasaan materi, metode interaktif, integrasi nilai Islam.</li>
                <li><strong>Tahap C: Kegiatan Penutup (Bobot 10)</strong> - Refleksi, kesimpulan, tindak lanjut evaluasi.</li>
            </ul>
        </li>
        <li>Simpan hasil supervisi. Nilai supervisi otomatis terakumulasi ke dalam kartu AKFI guru bersangkutan.</li>
    </ol>

    <!-- BAB 8 -->
    <h1 class="chapter-title">BAB 8: PANDUAN TATA USAHA &amp; KEUANGAN (SPP BULANAN)</h1>

    <h2 class="section-title">8.1 Manajemen Tagihan SPP Bulanan</h2>
    <p>
        Staf Tata Usaha dapat mengelola tagihan siswa melalui dua metode:
    </p>
    <ol class="step-list">
        <li><strong>Metode Impor Massal via Excel (Disarankan):</strong>
            <ul class="bullet-list">
                <li>Akses menu <strong>Tata Usaha &rarr; Input Tagihan &amp; SPP</strong>.</li>
                <li>Klik tombol <strong>"Unduh Template Siswa Unit (XLSX)"</strong>. File Excel yang diunduh sudah otomatis terisi seluruh nama santri aktif dan NIS pada unit sekolah Anda.</li>
                <li>Buka file Excel, sesuaikan nominal tagihan (default Rp 500.000) dan tanggal jatuh tempo jika diperlukan.</li>
                <li>Klik tombol <strong>"Unggah Berkas Excel"</strong> dan pilih file yang telah diisi. Sistem akan memverifikasi dan menyimpan tagihan seluruh siswa dalam hitungan detik.</li>
            </ul>
        </li>
        <li><strong>Metode Input Manual:</strong> Masukkan tagihan per santri melalui formulir entri satuan.</li>
    </ol>

    <h2 class="section-title">8.2 Pencatatan Pembayaran &amp; Rekapitulasi</h2>
    <ol class="step-list">
        <li>Pada tabel tagihan SPP, cari nama santri atau gunakan filter status (Tertunggak / Lunas).</li>
        <li>Ketika wali santri melakukan pembayaran (tunai atau transfer), klik tombol <strong>"Bayar"</strong>.</li>
        <li>Pilih metode pembayaran dan masukkan tanggal bayar, lalu klik konfirmasi. Status otomatis berubah menjadi <span class="badge badge-success">Lunas</span>.</li>
    </ol>

    <h2 class="section-title">8.3 Cetak Surat Penagihan SPP Resmi (Kop Unit PDF)</h2>
    <ol class="step-list">
        <li>Untuk santri yang memiliki tunggakan, klik tombol <strong>"Cetak Surat"</strong> pada baris tagihan.</li>
        <li>Sistem akan mencetak dokumen <strong>Surat Pemberitahuan Kewajiban Pembayaran SPP</strong> berformat PDF resmi yang dilengkapi Kop Surat Satuan Unit, rincian tunggakan bulan, dan tanda tangan Kepala Sekolah/Bendahara.</li>
    </ol>

    <h2 class="section-title">8.4 Checklist Harian Tata Usaha</h2>
    <p>
        Staf TU wajib mengisi checklist pemeliharaan operasional kantor harian (pengarsipan surat masuk/keluar, kebersihan ruang tata usaha, rekonsiliasi kas harian, dan layanan orang tua) yang menjadi komponen penilaian AKFI Tendik.
    </p>

    <!-- BAB 9 -->
    <h1 class="chapter-title">BAB 9: PANDUAN CLEANING SERVICES (CS) &amp; SECURITY</h1>

    <h2 class="section-title">9.1 Checklist Kebersihan Harian Cleaning Services (CS)</h2>
    <ol class="step-list">
        <li>Akses menu: <strong>Checklist CS</strong>.</li>
        <li>Sistem menyediakan pemantauan zona kebersihan yang terbagi ke dalam 3 sesi kerja:
            <ul class="bullet-list">
                <li><strong>Sesi Pagi (06.30 - 08.00):</strong> Penyapuan, pengepelan lantai ruang kelas, dan pembersihan toilet santri.</li>
                <li><strong>Sesi Siang (11.30 - 13.30):</strong> Pengosongan tempat sampah, sanitasi wastafel, dan pengecekan toilet guru.</li>
                <li><strong>Sesi Sore (15.00 - 16.30):</strong> Pembersihan koridor, penguncian jendela kelas, dan area halaman luar.</li>
            </ul>
        </li>
        <li>Tandai checklist yang telah dikerjakan dan klik <strong>"Simpan Checklist Kebersihan"</strong>.</li>
    </ol>

    <h2 class="section-title">9.2 Checklist Keamanan Harian Security</h2>
    <ol class="step-list">
        <li>Akses menu: <strong>Checklist Security</strong>.</li>
        <li>Petugas keamanan wajib mencatat dan memverifikasi rutinitas pengamanan lingkungan sekolah:
            <ul class="bullet-list">
                <li>Pengaturan kelancaran lalu lintas antar-jemput santri (pagi &amp; sore).</li>
                <li>Pencatatan identitas tamu luar pada buku tamu digital / pos sekuriti.</li>
                <li>Patroli keliling perimeter pagar sekolah tiap 2 jam.</li>
                <li>Pemeriksaan kunci pintu gerbang utama dan instalasi penerangan malam.</li>
                <li>Serah terima buku mutasi jaga antar shift.</li>
            </ul>
        </li>
        <li>Klik <strong>"Simpan Laporan Jaga"</strong>.</li>
    </ol>

    <!-- BAB 10 -->
    <h1 class="chapter-title">BAB 10: PANDUAN OPERATOR YAYASAN &amp; PEMELIHARAAN SISTEM</h1>

    <h2 class="section-title">10.1 Manajemen Tahun Ajaran &amp; Persetujuan Transisi (Approval)</h2>
    <p>
        Guna mencegah terjadinya kekacauan data saat pergantian tahun ajaran baru, sistem AKFIOKE menerapkan mekanisme <em>Inter-Unit Consensus Approval</em>:
    </p>
    <ol class="step-list">
        <li>Operator Yayasan mengajukan Tahun Ajaran baru pada menu <strong>Master Tahun Ajaran</strong>.</li>
        <li>Sistem otomatis mengirimkan notifikasi permintaan persetujuan (*approval request*) ke seluruh Kepala Sekolah dari 5 satuan unit (KB, TK, SD, SMP, SMA).</li>
        <li>Tahun ajaran baru baru dapat diaktifkan secara global setelah seluruh Kepala Sekolah menyatakan kesiapan data dan menyetujui transisi.</li>
    </ol>

    <h2 class="section-title">10.2 Manajemen Backup Database &amp; Audit Log</h2>
    <ol class="step-list">
        <li>Akses menu: <strong>Pengaturan &rarr; Backup Database</strong>.</li>
        <li>Klik tombol <strong>"Buat Cadangan Baru"</strong>. Sistem akan menghasilkan arsip snapshot database MySQL (.sql) terenkripsi yang dapat diunduh untuk penyimpanan luar (*off-site backup*).</li>
        <li>Menu <strong>Audit Log</strong> mencatat rekam jejak setiap aksi penting (login, perubahan nilai, penghapusan data, cetak dokumen) lengkap dengan alamat IP, stempel waktu, dan identitas pengguna.</li>
    </ol>

    <h2 class="section-title">10.3 Portal Orang Tua (Wali Santri)</h2>
    <p>
        Orang tua santri dapat masuk menggunakan Nomor Induk Siswa (NIS) atau akun yang didaftarkan pihak sekolah untuk:
    </p>
    <ul class="bullet-list">
        <li>Melihat grafik persentase kehadiran harian dan bulanan putra-putrinya.</li>
        <li>Mengunduh salinan resmi Rapor Bulanan dan Rapor Tahfidz.</li>
        <li>Mengecek riwayat pelunasan tagihan SPP dan mengunduh invoice kwitansi.</li>
        <li>Mengisi instrumen kuesioner evaluasi kepuasan layanan sekolah pada akhir semester.</li>
    </ul>

    <div class="callout callout-info" style="margin-top: 30px;">
        <strong>Pusat Layanan Bantuan &amp; Dukungan Teknis:</strong><br>
        Bila mengalami kendala teknis atau membutuhkan bantuan pengaturan akun sistem, silakan menghubungi Tim IT Yayasan Islam Al Kahfi Batam melalui Bagian Administrasi Yayasan atau email resmi operasional sekolah.
    </div>

</body>
</html>
HTML;

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Canvas untuk nomor halaman
$canvas = $dompdf->getCanvas();
$font = $dompdf->getFontMetrics()->getFont('Helvetica', 'normal');
$canvas->page_text(297, 810, "Halaman {PAGE_NUM} dari {PAGE_COUNT}", $font, 8.5, [0.4, 0.45, 0.5]);
$canvas->page_text(50, 810, "AKFIOKE - Yayasan Islam Al Kahfi Batam", $font, 8, [0.5, 0.55, 0.6]);

$output = $dompdf->output();

$targetPath = dirname(__DIR__) . '/public/docs/Buku_Panduan_Pengoperasian_AKFIOKE.pdf';
file_put_contents($targetPath, $output);

echo "Buku Panduan berhasil dibuat: {$targetPath}\n";
echo "Ukuran file: " . round(strlen($output) / 1024, 2) . " KB\n";
