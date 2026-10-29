<?php
// views/layouts/sidebar.php
use Core\SecurityHelper;

$role = $activeRole['name'] ?? 'guest';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '/';
if ($baseUrl && strpos($currentPath, $baseUrl) === 0) {
    $currentPath = substr($currentPath, strlen($baseUrl));
}
$currentPath = '/' . trim($currentPath, '/');
if ($currentPath === '//') $currentPath = '/';

if (!function_exists('isNavActive')) {
    function isNavActive(string $path, string $current): string {
        return ($path === $current || ($path !== '/' && strpos($current, $path) === 0)) ? 'active' : '';
    }
}
?>
<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-header">
        <a href="<?= $baseUrl ?>/dashboard" class="sidebar-logo" title="<?= SecurityHelper::escape($appName ?? 'MASEKO') ?>">
            <?php if (!empty($appLogoUrl)): ?>
                <img src="<?= $appLogoUrl ?>" alt="<?= SecurityHelper::escape($appShortName ?? 'MASEKO') ?>" 
                     style="width: 36px; height: 36px; object-fit: contain; border-radius: 8px; background: rgba(255, 255, 255, 0.08); padding: 2px; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
            <?php else: ?>
                <div class="logo-icon"><i class="fa-solid fa-desktop"></i></div>
            <?php endif; ?>
            <div class="sidebar-logo-text" style="display: flex; flex-direction: column; min-width: 0; line-height: 1.15; overflow: hidden;">
                <span class="nav-text" style="font-weight: 800; font-size: 15px; letter-spacing: 0.5px; color: #34D399;"><?= SecurityHelper::escape($appShortName ?? 'MASEKO') ?></span>
                <span class="nav-text-sub" style="font-size: 9px; color: #A7F3D0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 165px;" title="<?= SecurityHelper::escape($appSubName ?? 'Manajemen Sekolah Berbasis Komputer') ?>"><?= SecurityHelper::escape($appSubName ?? 'Manajemen Sekolah Berbasis Komputer') ?></span>
            </div>
        </a>
        <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" title="Tutup Menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="sidebar-nav">
        <!-- Dashboard Utama -->
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/dashboard" class="nav-link <?= isNavActive('/dashboard', $currentPath) ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </div>
        <!-- Profil Pengguna -->
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/profile" class="nav-link <?= isNavActive('/profile', $currentPath) ?>">
                <i class="fa-solid fa-id-card"></i>
                <span class="nav-text">Profil Saya</span>
            </a>
        </div>

        <?php if ($role === 'operator_yayasan'): ?>
            <div class="nav-section-title">YAYASAN & MASTER</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/master/units" class="nav-link <?= isNavActive('/master/units', $currentPath) ?>">
                    <i class="fa-solid fa-school"></i>
                    <span class="nav-text">Sekolah & Unit</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/master/academic-years" class="nav-link <?= isNavActive('/master/academic-years', $currentPath) ?>">
                    <i class="fa-solid fa-calendar-days"></i>
                    <span class="nav-text">Tahun Ajaran</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/master/employees" class="nav-link <?= isNavActive('/master/employees', $currentPath) ?>">
                    <i class="fa-solid fa-users-gear"></i>
                    <span class="nav-text">Guru & Karyawan</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/users/school-accounts" class="nav-link <?= isNavActive('/users/school-accounts', $currentPath) ?>">
                    <i class="fa-solid fa-user-shield"></i>
                    <span class="nav-text">Akun Operator Sekolah</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/master/turnover" class="nav-link <?= isNavActive('/master/turnover', $currentPath) ?>">
                    <i class="fa-solid fa-people-arrows"></i>
                    <span class="nav-text">Mutasi & Turnover</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/master/akfi-templates" class="nav-link <?= isNavActive('/master/akfi-templates', $currentPath) ?>">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span class="nav-text">Master Template AKFI</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/master/mushaf" class="nav-link <?= isNavActive('/master/mushaf', $currentPath) ?>">
                    <i class="fa-solid fa-book-quran"></i>
                    <span class="nav-text">Mushaf Madinah</span>
                </a>
            <div class="nav-section-title">SARPRAS & PENGADAAN</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/assets" class="nav-link <?= isNavActive('/sarpras/assets', $currentPath) ?>">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span class="nav-text">Data Aset Seluruh Unit</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/categories" class="nav-link <?= isNavActive('/sarpras/categories', $currentPath) ?>">
                    <i class="fa-solid fa-tags"></i>
                    <span class="nav-text">Kategori Aset</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/po" class="nav-link <?= isNavActive('/sarpras/po', $currentPath) ?>">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span class="nav-text">Permintaan Barang (PO)</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/scrap" class="nav-link <?= isNavActive('/sarpras/scrap', $currentPath) ?>">
                    <i class="fa-solid fa-file-excel"></i>
                    <span class="nav-text">Berita Acara Scrap (F30)</span>
                </a>
            </div>
            <div class="nav-section-title">AKFI & LAPORAN</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/akfi/rekap-yayasan" class="nav-link <?= isNavActive('/akfi/rekap-yayasan', $currentPath) ?>">
                    <i class="fa-solid fa-award"></i>
                    <span class="nav-text">Rekap AKFI Yayasan</span>
                </a>
            </div>
            <div class="nav-section-title">SISTEM & KEAMANAN</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/settings/app" class="nav-link <?= isNavActive('/settings/app', $currentPath) ?>">
                    <i class="fa-solid fa-sliders"></i>
                    <span class="nav-text">Identitas & Logo Aplikasi</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/audit/logs" class="nav-link <?= isNavActive('/audit/logs', $currentPath) ?>">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span class="nav-text">Audit Log</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/backup" class="nav-link <?= isNavActive('/backup', $currentPath) ?>">
                    <i class="fa-solid fa-database"></i>
                    <span class="nav-text">Backup & Restore DB</span>
                </a>
            </div>

        <?php elseif ($role === 'direktur_pendidikan'): ?>
            <div class="nav-section-title">DIREKTUR PENDIDIKAN</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/dashboard" class="nav-link <?= isNavActive('/dashboard', $currentPath) ?>">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span class="nav-text">Dashboard Analitik</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/akfi/rekap-yayasan" class="nav-link <?= isNavActive('/akfi/rekap-yayasan', $currentPath) ?>">
                    <i class="fa-solid fa-award"></i>
                    <span class="nav-text">Rekap Nilai AKFI</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/akfi/approval-hr" class="nav-link <?= isNavActive('/akfi/approval-hr', $currentPath) ?>">
                    <i class="fa-solid fa-signature"></i>
                    <span class="nav-text">Approval AKFI (HR)</span>
                </a>
            </div>

        <?php elseif ($role === 'deputi_pendidikan'): ?>
            <div class="nav-section-title">DEPUTI DIREKTUR</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/administrasi/review-ks" class="nav-link <?= isNavActive('/administrasi/review-ks', $currentPath) ?>">
                    <i class="fa-solid fa-folder-check"></i>
                    <span class="nav-text">Approval Administrasi KS</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/spmb/target" class="nav-link <?= isNavActive('/spmb/target', $currentPath) ?>">
                    <i class="fa-solid fa-bullseye"></i>
                    <span class="nav-text">Target & Realisasi SPMB</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/deputi/target-kerja" class="nav-link <?= isNavActive('/deputi/target-kerja', $currentPath) ?>">
                    <i class="fa-solid fa-business-time"></i>
                    <span class="nav-text">Target Hari Kerja & Kajian</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/akfi/kanit-preview" class="nav-link <?= isNavActive('/akfi/kanit-preview', $currentPath) ?>">
                    <i class="fa-solid fa-chart-line"></i>
                    <span class="nav-text">Pratinjau AKFI KS</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/akfi/penilaian-sikap-ks" class="nav-link <?= isNavActive('/akfi/penilaian-sikap-ks', $currentPath) ?>">
                    <i class="fa-solid fa-user-pen"></i>
                    <span class="nav-text">Penilaian Sikap KS</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/tagihan/eskalasi-deputi" class="nav-link <?= isNavActive('/tagihan/eskalasi-deputi', $currentPath) ?>">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span class="nav-text">Eskalasi Penagihan (FR 8001C)</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/rekap/deputi" class="nav-link <?= isNavActive('/rekap/deputi', $currentPath) ?>">
                    <i class="fa-solid fa-table-list"></i>
                    <span class="nav-text">Rekap Indikator Unit</span>
                </a>
            </div>

        <?php elseif ($role === 'operator_sekolah'): ?>
            <div class="nav-section-title">OPERATOR SEKOLAH</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/students" class="nav-link <?= (strpos($currentPath, '/students') === 0 && strpos($currentPath, '/students/alumni-mutasi') === false) ? 'active' : '' ?>">
                    <i class="fa-solid fa-users"></i>
                    <span class="nav-text">Data Siswa Aktif</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/students/alumni-mutasi" class="nav-link <?= isNavActive('/students/alumni-mutasi', $currentPath) ?>">
                    <i class="fa-solid fa-user-xmark"></i>
                    <span class="nav-text">Siswa Lulus & Pindah</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/classes" class="nav-link <?= isNavActive('/classes', $currentPath) ?>">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    <span class="nav-text">Data Kelas & Walas</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/classes/placement" class="nav-link <?= isNavActive('/classes/placement', $currentPath) ?>">
                    <i class="fa-solid fa-users-rectangle"></i>
                    <span class="nav-text">Penempatan Siswa Kelas</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/subjects" class="nav-link <?= isNavActive('/subjects', $currentPath) ?>">
                    <i class="fa-solid fa-book"></i>
                    <span class="nav-text">Mata Pelajaran</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/users/school-accounts" class="nav-link <?= isNavActive('/users/school-accounts', $currentPath) ?>">
                    <i class="fa-solid fa-id-badge"></i>
                    <span class="nav-text">Akun Guru & Kepsek</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/alumni" class="nav-link <?= isNavActive('/alumni', $currentPath) ?>">
                    <i class="fa-solid fa-user-tie"></i>
                    <span class="nav-text">Pelacakan Alumni</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/spmb/operator" class="nav-link <?= isNavActive('/spmb/operator', $currentPath) ?>">
                    <i class="fa-solid fa-user-plus"></i>
                    <span class="nav-text">Input SPMB Siswa Baru</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/master/academic-years" class="nav-link <?= isNavActive('/master/academic-years', $currentPath) ?>">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span class="nav-text">Persetujuan Tahun Ajaran</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/settings/smtp" class="nav-link <?= isNavActive('/settings/smtp', $currentPath) ?>">
                    <i class="fa-solid fa-envelope-open-text"></i>
                    <span class="nav-text">Setting SMTP & Surat</span>
                </a>
            </div>

        <?php elseif ($role === 'tutor_siswa'): ?>
            <div class="nav-section-title">TUTOR SISWA</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/tahfidz/ujian-antrean" class="nav-link <?= isNavActive('/tahfidz/ujian-antrean', $currentPath) ?>">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span class="nav-text">Ujian Tahfidz Siswa</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/tahfidz/guru-nilai" class="nav-link <?= isNavActive('/tahfidz/guru-nilai', $currentPath) ?>">
                    <i class="fa-solid fa-calculator"></i>
                    <span class="nav-text">Nilai Hafalan Guru</span>
                </a>
            </div>

        <?php elseif ($role === 'tutor_sebaya'): ?>
            <div class="nav-section-title">TUTOR SEBAYA GURU</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/tahfidz/halaqah-guru" class="nav-link <?= isNavActive('/tahfidz/halaqah-guru', $currentPath) ?>">
                    <i class="fa-solid fa-users"></i>
                    <span class="nav-text">Halaqah Guru</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/tahfidz/guru-target" class="nav-link <?= isNavActive('/tahfidz/guru-target', $currentPath) ?>">
                    <i class="fa-solid fa-bullseye"></i>
                    <span class="nav-text">Target Hafalan Guru</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/tahfidz/guru-nilai" class="nav-link <?= isNavActive('/tahfidz/guru-nilai', $currentPath) ?>">
                    <i class="fa-solid fa-calculator"></i>
                    <span class="nav-text">Input Nilai Guru</span>
                </a>
            </div>

        <?php elseif ($role === 'tata_usaha'): ?>
            <div class="nav-section-title">TATA USAHA</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/tagihan/manage" class="nav-link <?= isNavActive('/tagihan/manage', $currentPath) ?>">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span class="nav-text">Input Tagihan (FR 8001A)</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/tagihan/cicilan" class="nav-link <?= isNavActive('/tagihan/cicilan', $currentPath) ?>">
                    <i class="fa-solid fa-book-bookmark"></i>
                    <span class="nav-text">Buku & Kartu Cicilan (FR 8001D)</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/administrasi/tu" class="nav-link <?= isNavActive('/administrasi/tu', $currentPath) ?>">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span class="nav-text">Upload Dokumen TU</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/checklist/braja" class="nav-link <?= isNavActive('/checklist/braja', $currentPath) ?>">
                    <i class="fa-solid fa-list-check"></i>
                    <span class="nav-text">Checklist Braja</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/attendance/teachers" class="nav-link <?= isNavActive('/attendance/teachers', $currentPath) ?>">
                    <i class="fa-solid fa-clipboard-user"></i>
                    <span class="nav-text">Absensi & Kajian Guru</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/attendance/staff" class="nav-link <?= isNavActive('/attendance/staff', $currentPath) ?>">
                    <i class="fa-solid fa-id-card-clip"></i>
                    <span class="nav-text">Absensi Staf (TU, CS, Security)</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/akfi/pribadi" class="nav-link <?= isNavActive('/akfi/pribadi', $currentPath) ?>">
                    <i class="fa-solid fa-award"></i>
                    <span class="nav-text">Rapor AKFI Saya</span>
                </a>
            </div>

        <?php elseif ($role === 'cleaning_service'): ?>
            <div class="nav-section-title">CLEANING SERVICE</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/checklist/cs" class="nav-link <?= isNavActive('/checklist/cs', $currentPath) ?>">
                    <i class="fa-solid fa-broom"></i>
                    <span class="nav-text">Checklist CS (Rutin/Piket)</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/akfi/pribadi" class="nav-link <?= isNavActive('/akfi/pribadi', $currentPath) ?>">
                    <i class="fa-solid fa-award"></i>
                    <span class="nav-text">Rapor AKFI Saya</span>
                </a>
            </div>

        <?php elseif ($role === 'security'): ?>
            <div class="nav-section-title">SECURITY</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/checklist/security" class="nav-link <?= isNavActive('/checklist/security', $currentPath) ?>">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span class="nav-text">Checklist Harian</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/security/patrol" class="nav-link <?= isNavActive('/security/patrol', $currentPath) ?>">
                    <i class="fa-solid fa-person-military-pointing"></i>
                    <span class="nav-text">Log Patroli</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/security/incidents" class="nav-link <?= isNavActive('/security/incidents', $currentPath) ?>">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span class="nav-text">Buku Insiden / Kejadian</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/akfi/pribadi" class="nav-link <?= isNavActive('/akfi/pribadi', $currentPath) ?>">
                    <i class="fa-solid fa-award"></i>
                    <span class="nav-text">Rapor AKFI Saya</span>
                </a>
            </div>

        <?php elseif ($role === 'waka_kurikulum'): ?>
            <div class="nav-section-title">WAKA KURIKULUM</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/jadwal/kelas" class="nav-link <?= isNavActive('/jadwal/kelas', $currentPath) ?>">
                    <i class="fa-solid fa-calendar-week"></i>
                    <span class="nav-text">Jadwal Pelajaran Kelas</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/supervisi/jadwal" class="nav-link <?= isNavActive('/supervisi/jadwal', $currentPath) ?>">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span class="nav-text">Jadwal Supervisi Guru</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/administrasi/deadlines" class="nav-link <?= isNavActive('/administrasi/deadlines', $currentPath) ?>">
                    <i class="fa-solid fa-clock"></i>
                    <span class="nav-text">Deadline Administrasi</span>
                </a>
            </div>

        <?php elseif ($role === 'guru_supervisor'): ?>
            <div class="nav-section-title">SUPERVISI MENGAJAR</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/supervisi/penilaian" class="nav-link <?= isNavActive('/supervisi/penilaian', $currentPath) ?>">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span class="nav-text">Instrumen Supervisi Kelas</span>
                </a>
            </div>

        <?php elseif ($role === 'waka_kesiswaan'): ?>
            <div class="nav-section-title">WAKA KESISWAAN</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/prestasi/verifikasi" class="nav-link <?= isNavActive('/prestasi/verifikasi', $currentPath) ?>">
                    <i class="fa-solid fa-medal"></i>
                    <span class="nav-text">Prestasi Lomba Siswa</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/attendance/teachers" class="nav-link <?= isNavActive('/attendance/teachers', $currentPath) ?>">
                    <i class="fa-solid fa-user-clock"></i>
                    <span class="nav-text">Absensi Guru</span>
                </a>
            </div>

        <?php elseif ($role === 'koor_tahfidz'): ?>
            <div class="nav-section-title">KOORDINATOR TAHFIDZ</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/jadwal/tahfidz" class="nav-link <?= isNavActive('/jadwal/tahfidz', $currentPath) ?>">
                    <i class="fa-solid fa-quran"></i>
                    <span class="nav-text">Jadwal Halaqah Tahfidz</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/tahfidz/halaqah-siswa" class="nav-link <?= isNavActive('/tahfidz/halaqah-siswa', $currentPath) ?>">
                    <i class="fa-solid fa-users"></i>
                    <span class="nav-text">Halaqah Siswa</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/tahfidz/target-siswa" class="nav-link <?= isNavActive('/tahfidz/target-siswa', $currentPath) ?>">
                    <i class="fa-solid fa-bullseye"></i>
                    <span class="nav-text">Target Capaian Tahfidz</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/tahfidz/rekap-siswa" class="nav-link <?= isNavActive('/tahfidz/rekap-siswa', $currentPath) ?>">
                    <i class="fa-solid fa-chart-line"></i>
                    <span class="nav-text">Capaian & Rapor Tahfidz</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/tahfidz/baseline" class="nav-link <?= isNavActive('/tahfidz/baseline', $currentPath) ?>">
                    <i class="fa-solid fa-bars-progress"></i>
                    <span class="nav-text">Baseline Tahfidz (3x)</span>
                </a>
            </div>

        <?php elseif ($role === 'walas'): ?>
            <div class="nav-section-title">WALI KELAS</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/nilai/ledger" class="nav-link <?= isNavActive('/nilai/ledger', $currentPath) ?>">
                    <i class="fa-solid fa-table"></i>
                    <span class="nav-text">Ledger & Rapor Kelas</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/attendance/students" class="nav-link <?= isNavActive('/attendance/students', $currentPath) ?>">
                    <i class="fa-solid fa-user-check"></i>
                    <span class="nav-text">Absensi Siswa Bulanan</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/administrasi/walas" class="nav-link <?= isNavActive('/administrasi/walas', $currentPath) ?>">
                    <i class="fa-solid fa-folder-open"></i>
                    <span class="nav-text">Administrasi Walas (6 Item)</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/walas/ptk" class="nav-link <?= isNavActive('/walas/ptk', $currentPath) ?>">
                    <i class="fa-solid fa-book-bookmark"></i>
                    <span class="nav-text">Upload Laporan PTK</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/walas/rekomendasi" class="nav-link <?= isNavActive('/walas/rekomendasi', $currentPath) ?>">
                    <i class="fa-solid fa-arrows-split-up-and-left"></i>
                    <span class="nav-text">Rekomendasi Siswa / Alumni</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/prestasi/walas" class="nav-link <?= isNavActive('/prestasi/walas', $currentPath) ?>">
                    <i class="fa-solid fa-trophy"></i>
                    <span class="nav-text">Prestasi Lomba Siswa</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/guru/prestasi" class="nav-link <?= isNavActive('/guru/prestasi', $currentPath) ?>">
                    <i class="fa-solid fa-medal"></i>
                    <span class="nav-text">Prestasi Guru</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/akfi/pribadi" class="nav-link <?= isNavActive('/akfi/pribadi', $currentPath) ?>">
                    <i class="fa-solid fa-award"></i>
                    <span class="nav-text">Rapor AKFI Saya</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/survey/guru-kepsek" class="nav-link <?= isNavActive('/survey/guru-kepsek', $currentPath) ?>">
                    <i class="fa-solid fa-square-poll-vertical"></i>
                    <span class="nav-text">Survei Kepala Sekolah</span>
                </a>
            </div>

        <?php elseif (in_array($role, ['guru_mapel', 'guru_diniyah', 'guru_mapel_kbtk', 'guru_tk'], true)): ?>
            <div class="nav-section-title">GURU MATA PELAJARAN</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/nilai/bulanan" class="nav-link <?= isNavActive('/nilai/bulanan', $currentPath) ?>">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span class="nav-text">Nilai & KKM Bulanan</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/administrasi/mapel" class="nav-link <?= isNavActive('/administrasi/mapel', $currentPath) ?>">
                    <i class="fa-solid fa-book-bookmark"></i>
                    <span class="nav-text">Perangkat Mengajar (8 Item)</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/guru/best-practice" class="nav-link <?= isNavActive('/guru/best-practice', $currentPath) ?>">
                    <i class="fa-solid fa-lightbulb"></i>
                    <span class="nav-text">Upload Best Practice</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/supervisi/riwayat" class="nav-link <?= isNavActive('/supervisi/riwayat', $currentPath) ?>">
                    <i class="fa-solid fa-file-lines"></i>
                    <span class="nav-text">Hasil Supervisi Saya</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/guru/prestasi" class="nav-link <?= isNavActive('/guru/prestasi', $currentPath) ?>">
                    <i class="fa-solid fa-medal"></i>
                    <span class="nav-text">Prestasi Guru</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/akfi/pribadi" class="nav-link <?= isNavActive('/akfi/pribadi', $currentPath) ?>">
                    <i class="fa-solid fa-award"></i>
                    <span class="nav-text">Rapor AKFI Saya</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/survey/guru-kepsek" class="nav-link <?= isNavActive('/survey/guru-kepsek', $currentPath) ?>">
                    <i class="fa-solid fa-square-poll-vertical"></i>
                    <span class="nav-text">Survei Kepala Sekolah</span>
                </a>
            </div>

        <?php elseif ($role === 'guru_tahfidz'): ?>
            <div class="nav-section-title">GURU TAHFIDZ</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/jadwal/tahfidz" class="nav-link <?= isNavActive('/jadwal/tahfidz', $currentPath) ?>">
                    <i class="fa-solid fa-quran"></i>
                    <span class="nav-text">Jadwal Halaqah Tahfidz</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/attendance/students" class="nav-link <?= isNavActive('/attendance/students', $currentPath) ?>">
                    <i class="fa-solid fa-clipboard-user"></i>
                    <span class="nav-text">Absensi Bulanan Santri</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/tahfidz/setoran-siswa" class="nav-link <?= isNavActive('/tahfidz/setoran-siswa', $currentPath) ?>">
                    <i class="fa-solid fa-quran"></i>
                    <span class="nav-text">Setoran Hafalan Siswa</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/tahfidz/ajukan-ujian" class="nav-link <?= isNavActive('/tahfidz/ajukan-ujian', $currentPath) ?>">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span class="nav-text">Ajukan Ujian ke Tutor</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/administrasi/mapel" class="nav-link <?= isNavActive('/administrasi/mapel', $currentPath) ?>">
                    <i class="fa-solid fa-book-bookmark"></i>
                    <span class="nav-text">Perangkat Mengajar</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/guru/best-practice" class="nav-link <?= isNavActive('/guru/best-practice', $currentPath) ?>">
                    <i class="fa-solid fa-lightbulb"></i>
                    <span class="nav-text">Upload Best Practice</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/guru/prestasi" class="nav-link <?= isNavActive('/guru/prestasi', $currentPath) ?>">
                    <i class="fa-solid fa-medal"></i>
                    <span class="nav-text">Prestasi Guru</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/akfi/pribadi" class="nav-link <?= isNavActive('/akfi/pribadi', $currentPath) ?>">
                    <i class="fa-solid fa-award"></i>
                    <span class="nav-text">Rapor AKFI Saya</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/survey/guru-kepsek" class="nav-link <?= isNavActive('/survey/guru-kepsek', $currentPath) ?>">
                    <i class="fa-solid fa-square-poll-vertical"></i>
                    <span class="nav-text">Survei Kepala Sekolah</span>
                </a>
            </div>

        <?php elseif ($role === 'kepala_sekolah'): ?>
            <div class="nav-section-title">KEPALA SEKOLAH</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/jadwal/kelas" class="nav-link <?= isNavActive('/jadwal/kelas', $currentPath) ?>">
                    <i class="fa-solid fa-calendar-week"></i>
                    <span class="nav-text">Jadwal Pelajaran Kelas</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/administrasi/review-guru" class="nav-link <?= isNavActive('/administrasi/review-guru', $currentPath) ?>">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span class="nav-text">Approval Admin Guru & TU</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/administrasi/ks" class="nav-link <?= isNavActive('/administrasi/ks', $currentPath) ?>">
                    <i class="fa-solid fa-folder-closed"></i>
                    <span class="nav-text">Dokumen Admin KS (18 Item)</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/po" class="nav-link <?= isNavActive('/sarpras/po', $currentPath) ?>">
                    <i class="fa-solid fa-file-signature"></i>
                    <span class="nav-text">Approval Permintaan Sarpras (PO)</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/assets" class="nav-link <?= isNavActive('/sarpras/assets', $currentPath) ?>">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span class="nav-text">Inventaris Aset Sekolah</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/tagihan/approval" class="nav-link <?= isNavActive('/tagihan/approval', $currentPath) ?>">
                    <i class="fa-solid fa-envelope-open-text"></i>
                    <span class="nav-text">Penagihan & Panggilan (FR 8001C/D)</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/kegiatan/luar-kbm" class="nav-link <?= isNavActive('/kegiatan/luar-kbm', $currentPath) ?>">
                    <i class="fa-solid fa-calendar-plus"></i>
                    <span class="nav-text">Kegiatan di Luar KBM</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/panitia/penugasan" class="nav-link <?= isNavActive('/panitia/penugasan', $currentPath) ?>">
                    <i class="fa-solid fa-user-tag"></i>
                    <span class="nav-text">Penugasan Panitia Lomba</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/guru/prestasi-tetapkan" class="nav-link <?= isNavActive('/guru/prestasi-tetapkan', $currentPath) ?>">
                    <i class="fa-solid fa-certificate"></i>
                    <span class="nav-text">Verifikasi Prestasi Guru</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/akfi/penilaian-bawahan" class="nav-link <?= isNavActive('/akfi/penilaian-bawahan', $currentPath) ?>">
                    <i class="fa-solid fa-user-pen"></i>
                    <span class="nav-text">Nilai Sikap & AKFI Guru/Staf</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/akfi/pribadi" class="nav-link <?= isNavActive('/akfi/pribadi', $currentPath) ?>">
                    <i class="fa-solid fa-award"></i>
                    <span class="nav-text">Rapor AKFI Saya</span>
                </a>
            </div>

        <?php elseif ($role === 'orang_tua'): ?>
            <div class="nav-section-title">PORTAL ORANG TUA</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/parent/rapor" class="nav-link <?= isNavActive('/parent/rapor', $currentPath) ?>">
                    <i class="fa-solid fa-file-lines"></i>
                    <span class="nav-text">Unduh Rapor Anak</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/parent/bills" class="nav-link <?= isNavActive('/parent/bills', $currentPath) ?>">
                    <i class="fa-solid fa-receipt"></i>
                    <span class="nav-text">Tagihan & Rekening SPP</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/survey/ortu" class="nav-link <?= isNavActive('/survey/ortu', $currentPath) ?>">
                    <i class="fa-solid fa-comments"></i>
                    <span class="nav-text">Survei Kepuasan (53 Butir)</span>
                </a>
            </div>

        <?php elseif ($role === 'panitia_lomba'): ?>
            <div class="nav-section-title">PANITIA LOMBA KOTA</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/prestasi/panitia" class="nav-link <?= isNavActive('/prestasi/panitia', $currentPath) ?>">
                    <i class="fa-solid fa-trophy"></i>
                    <span class="nav-text">Input Juara OSN/O2SN/FLS2N</span>
                </a>
            </div>

        <?php elseif ($role === 'siswa'): ?>
            <div class="nav-section-title">PORTAL SISWA</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/survey/siswa" class="nav-link <?= isNavActive('/survey/siswa', $currentPath) ?>">
                    <i class="fa-solid fa-square-poll-vertical"></i>
                    <span class="nav-text">Survei Pembelajaran</span>
                </a>
            </div>

        <?php elseif ($role === 'sarpras'): ?>
            <div class="nav-section-title">SARPRAS & INVENTARIS</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/assets" class="nav-link <?= isNavActive('/sarpras/assets', $currentPath) ?>">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span class="nav-text">Data Aset Sekolah</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/categories" class="nav-link <?= isNavActive('/sarpras/categories', $currentPath) ?>">
                    <i class="fa-solid fa-tags"></i>
                    <span class="nav-text">Kategori Aset</span>
                </a>
            </div>
            <div class="nav-section-title">PENGADAAN & PO</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/po" class="nav-link <?= isNavActive('/sarpras/po', $currentPath) ?>">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span class="nav-text">Formulir Permintaan (PO)</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/po/create" class="nav-link <?= isNavActive('/sarpras/po/create', $currentPath) ?>">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span class="nav-text">Buat PO Baru</span>
                </a>
            </div>
            <div class="nav-section-title">PERNYATAAN SCRAP</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/scrap" class="nav-link <?= isNavActive('/sarpras/scrap', $currentPath) ?>">
                    <i class="fa-solid fa-file-excel"></i>
                    <span class="nav-text">Berita Acara Scrap (F30)</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/scrap/create" class="nav-link <?= isNavActive('/sarpras/scrap/create', $currentPath) ?>">
                    <i class="fa-solid fa-trash-can"></i>
                    <span class="nav-text">Buat Berita Acara Baru</span>
                </a>
            </div>

        <?php elseif ($role === 'direktur_pengadaan'): ?>
            <div class="nav-section-title">DIREKTUR PENGADAAN</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/po" class="nav-link <?= isNavActive('/sarpras/po', $currentPath) ?>">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span class="nav-text">Verifikasi & Approval PO</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/assets" class="nav-link <?= isNavActive('/sarpras/assets', $currentPath) ?>">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span class="nav-text">Rekap Aset Yayasan</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/categories" class="nav-link <?= isNavActive('/sarpras/categories', $currentPath) ?>">
                    <i class="fa-solid fa-tags"></i>
                    <span class="nav-text">Master Kategori Aset</span>
                </a>
            </div>

        <?php elseif ($role === 'direktur_sarpras'): ?>
            <div class="nav-section-title">DIREKTUR SARPRAS</div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/po?filter=contractor" class="nav-link <?= ($currentPath === '/sarpras/po' && isset($_GET['filter']) && $_GET['filter'] === 'contractor') ? 'active' : '' ?>">
                    <i class="fa-solid fa-helmet-safety"></i>
                    <span class="nav-text">Approval PO Kontraktor</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/po" class="nav-link <?= (isNavActive('/sarpras/po', $currentPath) && !isset($_GET['filter'])) ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span class="nav-text">Monitoring Semua PO</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/assets" class="nav-link <?= isNavActive('/sarpras/assets', $currentPath) ?>">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span class="nav-text">Monitoring Seluruh Aset Unit</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/scrap" class="nav-link <?= isNavActive('/sarpras/scrap', $currentPath) ?>">
                    <i class="fa-solid fa-file-excel"></i>
                    <span class="nav-text">Berita Acara Scrap (F30)</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="<?= $baseUrl ?>/sarpras/categories" class="nav-link <?= isNavActive('/sarpras/categories', $currentPath) ?>">
                    <i class="fa-solid fa-tags"></i>
                    <span class="nav-text">Master Kategori Aset</span>
                </a>
            </div>
        <?php endif; ?>

        <div class="nav-section-title">AKUN</div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/profile" class="nav-link <?= isNavActive('/profile', $currentPath) ?>">
                <i class="fa-solid fa-id-card"></i>
                <span class="nav-text">Profil Saya</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/change-password" class="nav-link <?= isNavActive('/change-password', $currentPath) ?>">
                <i class="fa-solid fa-key"></i>
                <span class="nav-text">Ganti Password</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="<?= $baseUrl ?>/logout" class="nav-link">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span class="nav-text">Keluar (Logout)</span>
            </a>
        </div>
    </div>
</aside>
