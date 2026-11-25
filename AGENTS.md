# Aturan Workspace Proyek MASEKO / AKFIOKE

## 1. Sinkronisasi Dokumentasi Wajib di Setiap Pembaruan
- Setiap kali menyelesaikan penambahan, pengubahan, atau perbaikan fitur, arsitektur, fungsi, alur input/output, skema database, rute, maupun tata cara penggunaan, **WAJIB** memperbarui kedua berkas dokumentasi proyek berikut sebelum menyelesaikan tugas:
  1. `README.md`
  2. `laporan_full_scan_maseko_akfioke.md`
- Pastikan isi kedua file tetap faktual dan 100% sesuai kode sumber aktual (`public/index.php`, controller, view, skrip migrasi, dan skema tabel), serta cantumkan tanggal pembaruan terbaru pada bagian header maupun riwayat perubahan (*changelog*).

## 2. Konvensi Teknis & Produksi
- **Migrasi Database Aman & Idempoten:** Perubahan tabel/kolom harus selalu disertakan dalam skrip migrasi idempoten di `database/` (termasuk integrasi ke `database/run_migration.php`) serta guard pengecekan defensif di aplikasi bila dibutuhkan agar deployment produksi tidak mengalami error 500.
- **Model Pegawai & Penugasan Lintas Unit:** Satu pegawai tetap menggunakan **1 akun (`users`)**, **1 baris pegawai (`employees`)** dengan **1 Unit Induk (`employees.unit_id`)**, dan **1 Rapor AKFI** pada Unit Induknya. Penugasan mengajar/operasional di unit lain dikelola melalui kolom `user_roles.unit_id` oleh `operator_yayasan` di `/master/cross-unit`.
- **Verifikasi Sebelum Selesai:** Selalu jalankan pemeriksaan sintaks PHP (`php -l`) pada seluruh berkas PHP yang dibuat atau dimodifikasi sebelum melaporkan hasil akhir.
