<?php
// views/profile/index.php
use Core\SecurityHelper;

$nipy = $employee['nipy'] ?? '-';
$nama = $employee['nama'] ?? ($user['full_name'] ?? '-');
$isYayasanUser = empty($unit)
    || in_array($activeRole['name'] ?? '', ['operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan', 'ketua_yayasan', 'pembina_yayasan'], true)
    || (!empty($employee['level_posisi']) && (stripos($employee['level_posisi'], 'Direktur') !== false || stripos($employee['level_posisi'], 'Yayasan') !== false || stripos($employee['level_posisi'], 'Deputi') !== false))
    || (!empty($employee['nipy']) && str_starts_with($employee['nipy'], 'YAY-'));

$unitName = $isYayasanUser ? 'Yayasan Islam Al Kahfi Batam' : ($unit['name'] ?? 'Yayasan Islam Al Kahfi Batam');
$unitCode = $isYayasanUser ? 'YAYASAN' : ($unit['code'] ?? 'YAYASAN');
$email = $employee['email'] ?? ($user['email'] ?? '-');
$phone = $employee['contact_phone'] ?? ($user['phone'] ?? '-');
$tmt = !empty($employee['tmt']) ? SecurityHelper::formatTanggalIndo($employee['tmt']) : '-';
$tingkatPendidikan = $employee['tingkat_pendidikan'] ?? '-';
$genderCode = $employee['gender'] ?? 'L';
$genderText = ($genderCode === 'P') ? 'Perempuan' : 'Laki-Laki';
$posisi = $employee['level_posisi'] ?? ($activeRole['display_name'] ?? ($activeRole['name'] ?? 'Pegawai'));

// Clean WhatsApp number
$waNumber = preg_replace('/[^0-9]/', '', (string)$phone);
if (strpos($waNumber, '0') === 0) {
    $waNumber = '62' . substr($waNumber, 1);
}
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 800; color: #1e293b;">
                <i class="fa-solid fa-id-card" style="color: #059669; margin-right: 8px;"></i> Profil Akun & Biodata
            </h1>
            <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 13px; font-weight: 700; padding: 4px 10px;">
                <?= SecurityHelper::escape($activeRole['display_name']) ?>
            </span>
        </div>
        <div class="page-subtitle" style="color: #64748b; font-size: 13px; margin-top: 4px;">
            Data profil terintegrasi secara otomatis dari data kepegawaian, penugasan KBM operator, dan inventaris Sarpras.
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn btn-outline" onclick="window.print()" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-print"></i> Cetak Biodata
        </button>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('editKontakModal').style.display='flex'" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-pen-to-square"></i> Perbarui Kontak
        </button>
    </div>
</div>

<!-- TOP PROFILE HERO CARD -->
<div class="card" style="background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); color: #ffffff; border-radius: 14px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(15, 118, 110, 0.25); border: none; overflow: hidden; position: relative;">
    <div style="position: absolute; right: -20px; bottom: -20px; font-size: 160px; color: rgba(255, 255, 255, 0.05); pointer-events: none;">
        <i class="fa-solid fa-graduation-cap"></i>
    </div>
    <div class="card-body" style="padding: 28px; position: relative; z-index: 1;">
        <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
            <div style="width: 86px; height: 86px; border-radius: 50%; background: #ffffff; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 800; box-shadow: 0 4px 14px rgba(0,0,0,0.15); border: 3px solid rgba(255,255,255,0.4); flex-shrink: 0;">
                <?php if ($genderCode === 'P'): ?>
                    <i class="fa-solid fa-user-nurse"></i>
                <?php else: ?>
                    <i class="fa-solid fa-user-tie"></i>
                <?php endif; ?>
            </div>
            <div style="flex: 1; min-width: 240px;">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h2 style="font-size: 22px; font-weight: 800; margin: 0; color: #ffffff; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                        <?= SecurityHelper::escape($nama) ?>
                    </h2>
                    <span style="background: rgba(255,255,255,0.2); backdrop-filter: blur(4px); padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        NIPY: <?= SecurityHelper::escape($nipy) ?>
                    </span>
                </div>
                <div style="margin-top: 6px; font-size: 14px; opacity: 0.95; display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                    <span><i class="fa-solid fa-briefcase" style="margin-right: 6px;"></i><?= SecurityHelper::escape($posisi) ?></span>
                    <span><i class="fa-solid <?= $unitCode === 'YAYASAN' ? 'fa-building-columns' : 'fa-school' ?>" style="margin-right: 6px;"></i><?= SecurityHelper::escape($unitName) ?></span>
                    <span><i class="fa-solid fa-circle-check" style="margin-right: 6px; color: #34d399;"></i>Status: Aktif</span>
                </div>
            </div>
            <div style="text-align: right; background: rgba(0,0,0,0.15); padding: 12px 18px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.15);">
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85;">Username Akun</div>
                <div style="font-size: 15px; font-weight: 700; color: #f0fdf4;">@<?= SecurityHelper::escape($user['username'] ?? '') ?></div>
                <div style="font-size: 11px; margin-top: 4px; opacity: 0.85;">Peran Utama: <?= SecurityHelper::escape($activeRole['display_name']) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="grid-cols-3" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <!-- INFORMASI BIODATA UTAMA -->
    <div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 14px 18px;">
            <h3 class="card-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-user-check" style="color: #059669;"></i> Biodata & Identitas Pegawai
            </h3>
        </div>
        <div class="card-body" style="padding: 18px;">
            <table class="table" style="width: 100%; border-collapse: separate; border-spacing: 0 8px; margin: 0;">
                <tbody>
                    <tr>
                        <td style="color: #64748b; font-size: 13px; width: 40%; padding: 4px 0;"><i class="fa-solid fa-barcode text-muted me-1"></i> NIPY</td>
                        <td style="font-weight: 700; color: #0f172a; font-size: 13px; padding: 4px 0;"><?= SecurityHelper::escape($nipy) ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 13px; padding: 4px 0;"><i class="fa-solid fa-user text-muted me-1"></i> Nama Lengkap</td>
                        <td style="font-weight: 700; color: #0f172a; font-size: 13px; padding: 4px 0;"><?= SecurityHelper::escape($nama) ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 13px; padding: 4px 0;"><i class="fa-solid fa-venus-mars text-muted me-1"></i> Jenis Kelamin</td>
                        <td style="font-weight: 600; color: #0f172a; font-size: 13px; padding: 4px 0;">
                            <?php if ($genderCode === 'P'): ?>
                                <span class="badge" style="background: #fdf2f8; color: #db2777; border: 1px solid #fbcfe8;">Perempuan</span>
                            <?php else: ?>
                                <span class="badge" style="background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;">Laki-Laki</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 13px; padding: 4px 0;"><i class="fa-solid <?= $unitCode === 'YAYASAN' ? 'fa-building-columns' : 'fa-school' ?> text-muted me-1"></i> Satuan Unit</td>
                        <td style="font-weight: 600; color: #0f172a; font-size: 13px; padding: 4px 0;">
                            <?= SecurityHelper::escape($unitName) ?>
                            <?php if ($unitCode === 'YAYASAN'): ?>
                                <span class="badge" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 11px; margin-left: 6px; padding: 2px 8px;">Yayasan</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 13px; padding: 4px 0;"><i class="fa-solid fa-calendar-check text-muted me-1"></i> TMT (Mulai Tugas)</td>
                        <td style="font-weight: 600; color: #0f172a; font-size: 13px; padding: 4px 0;"><?= SecurityHelper::escape($tmt) ?></td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 13px; padding: 4px 0;"><i class="fa-solid fa-user-graduate text-muted me-1"></i> Tingkat Pendidikan</td>
                        <td style="font-weight: 600; color: #0f172a; font-size: 13px; padding: 4px 0;">
                            <span class="badge badge-info" style="font-size: 12px;"><?= SecurityHelper::escape($tingkatPendidikan) ?></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KONTAK & KOMUNIKASI -->
    <div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 14px 18px;">
            <h3 class="card-title" style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-address-book" style="color: #0284c7;"></i> Kontak & Informasi Akun
            </h3>
        </div>
        <div class="card-body" style="padding: 18px;">
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 4px; display: block;">
                    <i class="fa-regular fa-envelope" style="margin-right: 4px;"></i> Alamat Email
                </label>
                <div style="font-size: 14px; font-weight: 600; color: #0f172a;">
                    <a href="mailto:<?= SecurityHelper::escape($email) ?>" style="color: #0284c7; text-decoration: none;">
                        <?= SecurityHelper::escape($email) ?>
                    </a>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 4px; display: block;">
                    <i class="fa-brands fa-whatsapp" style="margin-right: 4px; color: #16a34a;"></i> No. WhatsApp
                </label>
                <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px;">
                    <span style="font-size: 15px; font-weight: 700; color: #0f172a;"><?= SecurityHelper::escape($phone) ?></span>
                    <?php if (!empty($waNumber)): ?>
                        <a href="https://wa.me/<?= $waNumber ?>" target="_blank" class="btn btn-sm" style="background: #25d366; color: white; border: none; padding: 4px 10px; font-size: 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="fa-brands fa-whatsapp"></i> Chat WA
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 4px; display: block;">
                    <i class="fa-solid fa-shield-halved" style="margin-right: 4px;"></i> Hak Akses Sistem (Peran)
                </label>
                <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px;">
                    <?php foreach ($roles as $r): ?>
                        <span class="badge" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-size: 12px; padding: 4px 8px;">
                            <?= SecurityHelper::escape($r['display_name']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="background: #f8fafc; border-radius: 8px; padding: 10px 12px; border: 1px solid #e2e8f0; font-size: 12px; color: #64748b;">
                <i class="fa-solid fa-circle-info text-info me-1"></i> Butuh mengganti password akun? 
                <a href="<?= $baseUrl ?>/change-password" style="color: #0284c7; font-weight: 600; text-decoration: none;">Klik di sini</a>.
            </div>
        </div>
    </div>
</div>

<!-- SECTION 1: MENGAJAR DI KELAS APA DAN MATA PELAJARAN APA -->
<div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
        <div>
            <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-chalkboard-user" style="color: #7c3aed;"></i> Penugasan Mengajar & Pembelajaran
            </h3>
            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                Diambil otomatis dari penugasan yang diberikan oleh Operator Sekolah masing-masing (Mata Pelajaran & Kelas).
            </div>
        </div>
        <span class="badge badge-primary" style="font-size: 12px; padding: 4px 10px;">
            <?= count($teachingAssignments) ?> Penugasan Mapel
        </span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($teachingAssignments) || !empty($waliKelasList)): ?>
            <?php if (!empty($waliKelasList)): ?>
                <div style="padding: 14px 20px; background: #faf5ff; border-bottom: 1px solid #ede9fe; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <span style="font-weight: 700; color: #6b21a8; font-size: 13px;">
                        <i class="fa-solid fa-star" style="color: #eab308; margin-right: 6px;"></i> Penugasan Wali Kelas:
                    </span>
                    <?php foreach ($waliKelasList as $wk): ?>
                        <span class="badge" style="background: #7c3aed; color: #ffffff; padding: 5px 12px; font-size: 12px; border-radius: 6px;">
                            Kelas <?= SecurityHelper::escape($wk['name']) ?> (<?= SecurityHelper::escape($wk['academic_year_name'] . ' ' . $wk['semester']) ?>)
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr style="background: #f1f5f9; color: #475569; font-size: 12px; text-transform: uppercase;">
                            <th style="padding: 12px 18px;">No</th>
                            <th style="padding: 12px 18px;">Mata Pelajaran</th>
                            <th style="padding: 12px 18px;">Kode Mapel</th>
                            <th style="padding: 12px 18px;">Kelas / Rombel</th>
                            <th style="padding: 12px 18px;">Tingkat</th>
                            <th style="padding: 12px 18px;">Tahun Ajaran</th>
                            <th style="padding: 12px 18px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($teachingAssignments)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px; color: #64748b;">
                                    Belum ada penugasan mata pelajaran spesifik yang diinputkan operator sekolah.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($teachingAssignments as $ta): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 18px; font-weight: 600; color: #64748b;"><?= $no++ ?></td>
                                    <td style="padding: 12px 18px; font-weight: 700; color: #0f172a;">
                                        <i class="fa-solid fa-book-open" style="color: #7c3aed; margin-right: 6px;"></i>
                                        <?= SecurityHelper::escape($ta['subject_name']) ?>
                                    </td>
                                    <td style="padding: 12px 18px; color: #64748b;">
                                        <span class="badge" style="background: #f3e8ff; color: #7e22ce; font-family: monospace;">
                                            <?= SecurityHelper::escape($ta['subject_code']) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px 18px; font-weight: 700; color: #0f172a;">
                                        Kelas <?= SecurityHelper::escape($ta['class_name']) ?>
                                    </td>
                                    <td style="padding: 12px 18px; color: #64748b;">
                                        Tingkat <?= SecurityHelper::escape($ta['class_level']) ?>
                                    </td>
                                    <td style="padding: 12px 18px; color: #64748b;">
                                        <?= SecurityHelper::escape($ta['academic_year_name'] . ' (' . $ta['semester'] . ')') ?>
                                    </td>
                                    <td style="padding: 12px 18px;">
                                        <span class="badge badge-success" style="font-size: 11px;">Aktif Mengajar</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="padding: 32px 20px; text-align: center; color: #64748b;">
                <div style="width: 54px; height: 54px; border-radius: 50%; background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; color: #94a3b8; margin-bottom: 12px;">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <div style="font-weight: 700; color: #334155; font-size: 15px;">Tidak Ada Penugasan Mengajar KBM</div>
                <div style="font-size: 13px; max-width: 450px; margin: 4px auto 0;">
                    Akun ini bertugas pada posisi struktural/operasional (<strong><?= SecurityHelper::escape($posisi) ?></strong>) dan tidak memiliki penugasan guru mapel aktif di kelas.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- SECTION 2: ASET SEKOLAH YANG SEDANG DIGUNAKAN -->
<div class="card" style="box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
        <div>
            <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-boxes-stacked" style="color: #0d9488;"></i> Aset Sekolah yang Sedang Digunakan / Dipegang
            </h3>
            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                Daftar inventaris sarana dan prasarana yang dipinjamkan/dipegang oleh akun Anda, bersumber dari pendataan resmi Sarpras unit.
            </div>
        </div>
        <span class="badge" style="background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; font-size: 12px; padding: 4px 10px; font-weight: 700;">
            <?= count($assignedAssets) ?> Aset Terdaftar
        </span>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (!empty($assignedAssets)): ?>
            <div class="table-responsive">
                <table class="table" style="margin: 0; width: 100%;">
                    <thead>
                        <tr style="background: #f1f5f9; color: #475569; font-size: 12px; text-transform: uppercase;">
                            <th style="padding: 12px 18px;">No</th>
                            <th style="padding: 12px 18px;">No Aset</th>
                            <th style="padding: 12px 18px;">Nama / Merk / Brand</th>
                            <th style="padding: 12px 18px;">Kategori</th>
                            <th style="padding: 12px 18px;">Tgl Beli</th>
                            <th style="padding: 12px 18px;">Kondisi</th>
                            <th style="padding: 12px 18px;">Lokasi / Penempatan</th>
                            <th style="padding: 12px 18px;">Foto Barang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($assignedAssets as $asset): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 18px; font-weight: 600; color: #64748b;"><?= $no++ ?></td>
                                <td style="padding: 12px 18px;">
                                    <span class="badge" style="background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1; font-family: monospace; font-size: 12px;">
                                        <?= SecurityHelper::escape($asset['asset_code']) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 18px; font-weight: 700; color: #0f172a;">
                                    <?= SecurityHelper::escape($asset['name']) ?>
                                </td>
                                <td style="padding: 12px 18px; color: #64748b;">
                                    <span class="badge badge-secondary"><?= SecurityHelper::escape($asset['category_name'] ?? 'Umum') ?></span>
                                </td>
                                <td style="padding: 12px 18px; color: #64748b;">
                                    <?= !empty($asset['purchase_date']) ? date('d/m/Y', strtotime($asset['purchase_date'])) : '-' ?>
                                </td>
                                <td style="padding: 12px 18px;">
                                    <?php if ($asset['condition_status'] === 'Baik'): ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> Baik</span>
                                    <?php elseif ($asset['condition_status'] === 'Rusak Ringan'): ?>
                                        <span class="badge badge-warning"><i class="fa-solid fa-triangle-exclamation"></i> Rusak Ringan</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?= SecurityHelper::escape($asset['condition_status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 18px; color: #475569;">
                                    <i class="fa-solid fa-location-dot text-muted me-1"></i>
                                    <?= SecurityHelper::escape($asset['location'] ?? 'Ruang Kerja Pegawai') ?>
                                </td>
                                <td style="padding: 12px 18px;">
                                    <?php if (!empty($asset['photo'])): ?>
                                        <a href="<?= $baseUrl ?>/sarpras/file/view?file=<?= urlencode($asset['photo']) ?>" target="_blank" class="btn btn-sm btn-outline" style="padding: 2px 8px; font-size: 11px;">
                                            <i class="fa-regular fa-image"></i> Lihat Foto
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 12px;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="padding: 36px 20px; text-align: center; color: #64748b;">
                <div style="width: 54px; height: 54px; border-radius: 50%; background: #f0fdfa; color: #0d9488; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px;">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <div style="font-weight: 700; color: #334155; font-size: 15px;">Belum Ada Aset Sekolah yang Ditetapkan</div>
                <div style="font-size: 13px; max-width: 450px; margin: 4px auto 0;">
                    Saat ini belum ada sarana atau prasarana sekolah yang tercatat atas nama Anda di database Sarpras unit. Jika Anda memegang inventaris sekolah, silakan lapor ke petugas Sarpras.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL EDIT KONTAK -->
<div id="editKontakModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; margin: 16px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">
                <i class="fa-solid fa-pen-to-square" style="color: #059669; margin-right: 6px;"></i> Perbarui Kontak Profil
            </h3>
            <button type="button" onclick="document.getElementById('editKontakModal').style.display='none'" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>
        <form method="POST" action="<?= $baseUrl ?>/profile/update">
            <?= $csrfField ?>
            <div style="padding: 20px;">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px; display: block;">
                        No. WhatsApp / Telepon <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="phone" class="form-control" value="<?= SecurityHelper::escape($phone) ?>" placeholder="Contoh: 081234567890" required>
                    <small style="color: #64748b; font-size: 11px;">Gunakan nomor aktif untuk kemudahan koordinasi yayasan.</small>
                </div>
                <div class="form-group" style="margin-bottom: 8px;">
                    <label class="form-label" style="font-weight: 600; font-size: 13px; color: #334155; margin-bottom: 6px; display: block;">
                        Alamat Email <span class="text-danger">*</span>
                    </label>
                    <input type="email" name="email" class="form-control" value="<?= SecurityHelper::escape($email) ?>" placeholder="nama@alkahfi.sch.id" required>
                </div>
            </div>
            <div style="padding: 12px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('editKontakModal').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
