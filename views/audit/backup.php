<?php
// views/audit/backup.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
?>

<!-- Header Halaman -->
<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Operator Yayasan</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">
                    <i class="fa-solid fa-shield-halved" style="color: #059669;"></i> Pusat Keamanan &amp; Integritas Data
                </span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-database" style="color: #059669;"></i> Cadangan &amp; Pemulihan Database (Backup &amp; Restore)
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Manajemen pencadangan basis data, proteksi enkripsi AES-256 (kepatuhan UU PDP), sinkronisasi Cold Storage NAS, dan prosedur pemulihan sistem.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="<?= $baseUrl ?>/master/academic-years" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px; border-color: #0284c7; color: #0284c7; background: #f0f9ff;">
                <i class="fa-solid fa-snowflake"></i> Kelola Arsip TA (Cold NAS)
            </a>
            <a href="<?= $baseUrl ?>/audit/logs" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-list-check"></i> Log Audit Keamanan
            </a>
        </div>
    </div>
</div>

<!-- 4 Kartu Metrik Ringkasan Penyimpanan & Keamanan -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- Metrik 1: Total Cadangan Tersimpan -->
    <div class="card" style="padding: 16px; border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; display: flex; align-items: center; gap: 14px; background: #ffffff;">
        <div style="width: 48px; height: 48px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0;">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Arsip Cadangan</div>
            <div style="font-size: 1.4rem; font-weight: 700; color: #0f172a; line-height: 1.2; margin: 2px 0;">
                <?= (int)$totalBackups ?> <span style="font-size: 0.8125rem; font-weight: 500; color: #64748b;">Berkas</span>
            </div>
            <div style="font-size: 0.75rem; color: #0284c7; font-weight: 500;">
                <?= round($totalBytes / (1024 * 1024), 2) ?> MB Total Ukuran
            </div>
        </div>
    </div>

    <!-- Metrik 2: Hot Storage Hosting -->
    <div class="card" style="padding: 16px; border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; display: flex; align-items: center; gap: 14px; background: #ffffff;">
        <div style="width: 48px; height: 48px; border-radius: 10px; background: #fffbeb; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0;">
            <i class="fa-solid fa-fire"></i>
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Hot Storage (Hosting)</div>
            <div style="font-size: 1.4rem; font-weight: 700; color: #0f172a; line-height: 1.2; margin: 2px 0;">
                <?= !empty($hotFree) ? round($hotFree / (1024 * 1024 * 1024), 1) : '-' ?> <span style="font-size: 0.8125rem; font-weight: 500; color: #64748b;">GB Tersedia</span>
            </div>
            <div style="font-size: 0.75rem; color: #a16207; font-weight: 500;">
                Media: Hosting Server NVMe/SSD
            </div>
        </div>
    </div>

    <!-- Metrik 3: Cold Storage (NAS Lokal) -->
    <div class="card" style="padding: 16px; border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; display: flex; align-items: center; gap: 14px; background: #ffffff;">
        <div style="width: 48px; height: 48px; border-radius: 10px; background: <?= $nasStatus['success'] ? '#dcfce7' : '#fee2e2' ?>; color: <?= $nasStatus['success'] ? '#15803d' : '#b91c1c' ?>; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0;">
            <i class="fa-solid fa-snowflake"></i>
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Cold Storage (NAS Lokal)</div>
            <div style="font-size: 1.15rem; font-weight: 700; color: <?= $nasStatus['success'] ? '#15803d' : '#b91c1c' ?>; line-height: 1.2; margin: 2px 0;">
                <?= $nasStatus['success'] ? 'NAS Terhubung' : 'Terputus / Offline' ?>
            </div>
            <div style="font-size: 0.75rem; color: #64748b;">
                Driver: <strong><?= strtoupper(SecurityHelper::escape($coldConfig['driver'] ?? 'LOCAL')) ?></strong>
            </div>
        </div>
    </div>

    <!-- Metrik 4: Kriptografi & Kepatuhan -->
    <div class="card" style="padding: 16px; border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; display: flex; align-items: center; gap: 14px; background: #ffffff;">
        <div style="width: 48px; height: 48px; border-radius: 10px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; flex-shrink: 0;">
            <i class="fa-solid fa-lock"></i>
        </div>
        <div>
            <div style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Proteksi Kriptografi</div>
            <div style="font-size: 1.4rem; font-weight: 700; color: #0f172a; line-height: 1.2; margin: 2px 0;">
                <?= (int)$encryptedCount ?> <span style="font-size: 0.8125rem; font-weight: 500; color: #64748b;">Terenkripsi</span>
            </div>
            <div style="font-size: 0.75rem; color: #059669; font-weight: 500;">
                AES-256-CBC + HMAC-SHA256
            </div>
        </div>
    </div>
</div>

<!-- Grid Layout Utama: 2 Kolom Responsif -->
<div style="display: grid; grid-template-columns: minmax(360px, 1fr) minmax(480px, 1.8fr); gap: 24px; align-items: start;">
    
    <!-- KOLOM KIRI: PEMBUATAN CADANGAN & FORM RESTORE -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <!-- CARD 1: CADANGAN DATABASE SAJA (.SQL / .ENC) -->
        <div class="card" style="border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; overflow: hidden; background: #ffffff;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
                <h3 class="card-title" style="margin: 0; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; color: #0f172a;">
                    <i class="fa-solid fa-database" style="color: #059669;"></i> Cadangan Database Saja
                </h3>
                <span class="badge badge-success" style="font-size: 0.7rem;">Cepat (DML + DDL)</span>
            </div>
            <div class="card-body" style="padding: 18px;">
                <p style="font-size: 0.8125rem; color: var(--text-muted, #64748b); line-height: 1.5; margin-bottom: 14px;">
                    Mengekspor seluruh tabel, relasi, dan baris data aplikasi ke dalam format SQL. Sangat direkomendasikan dibuat sebelum update data atau penyesuaian sistem.
                </p>
                <form action="<?= $baseUrl ?>/backup/create" method="POST">
                    <?= CSRF::inputField() ?>
                    
                    <!-- Opsi Enkripsi AES-256 -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; margin-bottom: 12px;">
                        <label style="display: flex; align-items: center; gap: 10px; font-size: 0.8125rem; font-weight: 600; color: #0f172a; cursor: pointer; margin: 0;">
                            <input type="checkbox" name="encrypt" value="1" id="encryptBackup" checked style="width: 16px; height: 16px;">
                            <span><i class="fa-solid fa-lock" style="color: #059669;"></i> Enkripsi Berkas dengan AES-256</span>
                        </label>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px; margin-left: 26px;">
                            Hanya server resmi dengan kunci <code>APP_KEY</code> yang dapat mendekripsi. Sesuai standar UU PDP No. 27/2022.
                        </div>
                    </div>

                    <!-- Opsi Salin ke Cold Storage NAS -->
                    <?php if ($coldEnabled): ?>
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px;">
                            <label style="display: flex; align-items: center; gap: 10px; font-size: 0.8125rem; font-weight: 600; color: #166534; cursor: pointer; margin: 0;">
                                <input type="checkbox" name="copy_to_nas" value="1" <?= $nasStatus['success'] ? 'checked' : '' ?> style="width: 16px; height: 16px;">
                                <span><i class="fa-solid fa-snowflake" style="color: #0284c7;"></i> Salin otomatis ke Cold Storage NAS</span>
                            </label>
                            <div style="font-size: 0.75rem; color: #15803d; margin-top: 4px; margin-left: 26px;">
                                Mengamankan salinan langsung ke NAS lokal sekolah tanpa perlu transfer manual.
                            </div>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px;" onclick="return confirm('Mulai proses pencadangan database sekarang?')">
                        <i class="fa-solid fa-download"></i> Ekspor Cadangan Database (.SQL / .ENC)
                    </button>
                </form>
            </div>
        </div>

        <!-- CARD 2: CADANGAN PENUH (FULL BACKUP: DATABASE + STORAGE UPLOADS .ZIP) -->
        <div class="card" style="border: 1px solid #bfdbfe; border-radius: 10px; overflow: hidden; background: #ffffff;">
            <div class="card-header" style="background: #eff6ff; border-bottom: 1px solid #bfdbfe; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
                <h3 class="card-title" style="margin: 0; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; color: #1e40af;">
                    <i class="fa-solid fa-file-zipper" style="color: #2563eb;"></i> Cadangan Penuh (DB + Storage)
                </h3>
                <span class="badge" style="background: #dbeafe; color: #1d4ed8; font-size: 0.7rem; font-weight: 600;">Rekomendasi Mingguan</span>
            </div>
            <div class="card-body" style="padding: 18px;">
                <p style="font-size: 0.8125rem; color: var(--text-muted, #64748b); line-height: 1.5; margin-bottom: 14px;">
                    Mengekspor <strong>seluruh database MariaDB/MySQL</strong> serta mengarsipkan <strong>seluruh dokumen/berkas di folder <code>storage/uploads/</code></strong> ke dalam arsip <code>.ZIP</code> terpadu lengkap dengan manifest metadata.
                </p>
                <form action="<?= $baseUrl ?>/backup/create-full" method="POST">
                    <?= CSRF::inputField() ?>
                    
                    <?php if ($coldEnabled): ?>
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px;">
                            <label style="display: flex; align-items: center; gap: 10px; font-size: 0.8125rem; font-weight: 600; color: #166534; cursor: pointer; margin: 0;">
                                <input type="checkbox" name="copy_to_nas" value="1" <?= $nasStatus['success'] ? 'checked' : '' ?> style="width: 16px; height: 16px;">
                                <span><i class="fa-solid fa-snowflake" style="color: #0284c7;"></i> Sinkronkan arsip .ZIP langsung ke NAS Lokal</span>
                            </label>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: #2563eb; border-color: #2563eb;" onclick="return confirm('Mulai proses pencadangan penuh (Database + Dokumen Storage)? Proses ini mungkin memakan waktu beberapa saat tergantung ukuran berkas.')">
                        <i class="fa-solid fa-cloud-arrow-down"></i> Ekspor Cadangan Penuh (.ZIP)
                    </button>
                </form>
            </div>
        </div>

        <!-- CARD 3: PEMULIHAN DATABASE (UNGGAH BERKAS) -->
        <div class="card" style="border: 1px solid #fecaca; border-radius: 10px; overflow: hidden; background: #ffffff;">
            <div class="card-header" style="background: #fef2f2; border-bottom: 1px solid #fecaca; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between;">
                <h3 class="card-title" style="margin: 0; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; color: #991b1b;">
                    <i class="fa-solid fa-clock-rotate-left" style="color: #dc2626;"></i> Pemulihan Database (Restore)
                </h3>
                <span class="badge badge-danger" style="font-size: 0.7rem;">Operasi Kritis</span>
            </div>
            <div class="card-body" style="padding: 18px;">
                <div class="alert alert-warning" style="font-size: 0.8125rem; padding: 12px; margin-bottom: 14px; border-radius: 6px; line-height: 1.4;">
                    <i class="fa-solid fa-triangle-exclamation" style="color: #d97706; font-size: 1rem;"></i>
                    <div>
                        <strong>Peringatan Penting:</strong> Pemulihan akan <strong>menimpa seluruh rekaman tabel</strong> saat ini sesuai isi berkas SQL atau berkas terenkripsi (.ENC) yang diunggah.
                    </div>
                </div>
                <form action="<?= $baseUrl ?>/backup/restore" method="POST" enctype="multipart/form-data">
                    <?= CSRF::inputField() ?>
                    <div class="form-group" style="margin-bottom: 14px;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Pilih Berkas Cadangan (.SQL atau .ENC) <span style="color: #ef4444;">*</span></label>
                        <input type="file" name="backup_file" class="form-control" accept=".sql,.enc" required style="font-size: 0.8125rem; padding: 6px 10px;">
                    </div>
                    <button type="submit" class="btn btn-outline" style="width: 100%; padding: 9px; font-weight: 600; border-color: #dc2626; color: #dc2626; display: inline-flex; align-items: center; justify-content: center; gap: 8px;" onclick="return confirm('PERINGATAN AKHIR: Apakah Anda yakin 100% ingin memulihkan database dari berkas ini? Seluruh data tabel saat ini akan ditimpa!')">
                        <i class="fa-solid fa-arrow-rotate-left"></i> Mulai Pemulihan dari Berkas Unggahan
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- KOLOM KANAN: RIWAYAT ARSIP & PANDUAN OTOMASI -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <!-- CARD 4: TABEL RIWAYAT BERKAS CADANGAN -->
        <div class="card" style="border: 1px solid var(--border-color, #e2e8f0); border-radius: 10px; overflow: hidden; background: #ffffff;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                    <div>
                        <h3 class="card-title" style="margin: 0; font-size: 1rem; color: #0f172a;">Arsip Cadangan Tersimpan</h3>
                        <p style="margin: 0; font-size: 0.75rem; color: #64748b;">Daftar berkas cadangan database &amp; penyimpanan di server</p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="badge badge-secondary"><?= count($backups) ?> Arsip</span>
                </div>
            </div>
            
            <div class="card-body" style="padding: 0;">
                <div style="overflow-x: auto;">
                    <table class="table" style="width: 100%; margin: 0;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid var(--border-color, #e2e8f0); text-align: left;">
                                <th style="width: 45px; text-align: center; padding: 12px 10px; font-size: 0.8125rem;">No</th>
                                <th style="padding: 12px 14px; font-size: 0.8125rem;">Berkas &amp; Jenis Cadangan</th>
                                <th style="width: 100px; padding: 12px 10px; font-size: 0.8125rem;">Ukuran</th>
                                <th style="width: 140px; padding: 12px 10px; font-size: 0.8125rem;">Lokasi Storage</th>
                                <th style="width: 130px; padding: 12px 10px; font-size: 0.8125rem;">Dibuat Pada</th>
                                <th style="width: 140px; text-align: center; padding: 12px 10px; font-size: 0.8125rem;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($backups)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 36px 20px; color: #64748b;">
                                        <i class="fa-solid fa-folder-open" style="font-size: 2rem; color: #cbd5e1; display: block; margin-bottom: 8px;"></i>
                                        Belum ada riwayat berkas cadangan. Silakan buat cadangan baru di panel sebelah kiri.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($backups as $idx => $b): 
                                    $ext = strtolower(pathinfo($b['filename'], PATHINFO_EXTENSION));
                                    $isZip = ($ext === 'zip');
                                    $isEnc = ($ext === 'enc' || stripos($b['backup_type'] ?? '', 'Terenkripsi') !== false);
                                    $type = $b['backup_type'] ?? 'Manual';
                                    $isSyncedToNas = !empty($b['synced_to_nas']);
                                    $sizeFormatted = ($b['file_size'] >= 1048576) 
                                        ? round($b['file_size'] / 1048576, 2) . ' MB' 
                                        : round($b['file_size'] / 1024, 1) . ' KB';
                                ?>
                                    <tr style="border-bottom: 1px solid var(--border-color, #e2e8f0);">
                                        <td style="text-align: center; padding: 12px 8px; color: #64748b; font-size: 0.8125rem;">
                                            <?= $idx + 1 ?>
                                        </td>
                                        <td style="padding: 12px 14px;">
                                            <div style="font-family: monospace; font-size: 0.8125rem; font-weight: 700; color: #0f172a; word-break: break-all;">
                                                <?php if ($isZip): ?>
                                                    <i class="fa-solid fa-file-zipper" style="color: #2563eb; margin-right: 4px;"></i>
                                                <?php elseif ($isEnc): ?>
                                                    <i class="fa-solid fa-lock" style="color: #059669; margin-right: 4px;"></i>
                                                <?php else: ?>
                                                    <i class="fa-solid fa-database" style="color: #0d9488; margin-right: 4px;"></i>
                                                <?php endif; ?>
                                                <?= SecurityHelper::escape($b['filename']) ?>
                                            </div>
                                            <div style="display: flex; gap: 6px; align-items: center; margin-top: 4px; flex-wrap: wrap;">
                                                <?php if (stripos($type, 'Full') !== false): ?>
                                                    <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 0.6875rem;">
                                                        <i class="fa-solid fa-layer-group"></i> Full (DB + Berkas)
                                                    </span>
                                                <?php elseif (stripos($type, 'Cron') !== false): ?>
                                                    <span class="badge" style="background: #f0fdfa; color: #0f766e; border: 1px solid #99f6e4; font-size: 0.6875rem;">
                                                        <i class="fa-solid fa-clock"></i> Cron Otomatis
                                                    </span>
                                                <?php elseif ($isEnc): ?>
                                                    <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-size: 0.6875rem;">
                                                        <i class="fa-solid fa-key"></i> Terenkripsi AES-256
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge" style="background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; font-size: 0.6875rem;">
                                                        <i class="fa-solid fa-file-code"></i> SQL Plaintext
                                                    </span>
                                                <?php endif; ?>

                                                <?php if (!empty($b['creator_name'])): ?>
                                                    <span style="font-size: 0.6875rem; color: #94a3b8;">
                                                        oleh <?= SecurityHelper::escape($b['creator_name']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td style="padding: 12px 10px; font-family: monospace; font-size: 0.8125rem; font-weight: 600; color: #334155;">
                                            <?= $sizeFormatted ?>
                                        </td>
                                        <td style="padding: 12px 10px;">
                                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                                <span class="badge" style="background: #fffbeb; color: #92400e; border: 1px solid #fde68a; font-size: 0.6875rem; width: fit-content;">
                                                    <i class="fa-solid fa-fire" style="color: #ea580c;"></i> Hot Hosting
                                                </span>
                                                <?php if ($isSyncedToNas): ?>
                                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 0.6875rem; width: fit-content;" title="Berkas telah tersinkronkan ke Cold Storage NAS">
                                                        <i class="fa-solid fa-snowflake" style="color: #0284c7;"></i> Cold NAS
                                                    </span>
                                                <?php elseif ($coldEnabled): ?>
                                                    <form method="POST" action="<?= $baseUrl ?>/backup/copy-to-nas" style="margin: 0;">
                                                        <?= CSRF::inputField() ?>
                                                        <input type="hidden" name="backup_id" value="<?= $b['id'] ?>">
                                                        <button type="submit" class="btn btn-outline btn-sm" style="padding: 1px 6px; font-size: 0.65rem; border-color: #0284c7; color: #0284c7;" title="Salin berkas ini ke Cold Storage NAS">
                                                            <i class="fa-solid fa-cloud-arrow-up"></i> Kirim ke NAS
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td style="padding: 12px 10px; font-size: 0.75rem; color: #64748b;">
                                            <?= date('d/m/Y', strtotime($b['created_at'])) ?><br>
                                            <span style="color: #94a3b8; font-size: 0.7rem;"><?= date('H:i:s', strtotime($b['created_at'])) ?> WIB</span>
                                        </td>
                                        <td style="text-align: center; padding: 12px 10px;">
                                            <div style="display: flex; gap: 4px; justify-content: center; align-items: center; flex-wrap: wrap;">
                                                <!-- Unduh File -->
                                                <a href="<?= $baseUrl ?>/backup/download/<?= $b['id'] ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px;" title="Unduh Berkas ke Komputer">
                                                    <i class="fa-solid fa-download"></i>
                                                </a>

                                                <!-- Pulihkan Langsung dari Berkas Ini (Hanya untuk SQL / ENC) -->
                                                <?php if (!$isZip): ?>
                                                    <button type="button" class="btn btn-outline btn-sm" onclick="openDirectRestoreModal(<?= (int)$b['id'] ?>, '<?= SecurityHelper::escape($b['filename']) ?>', '<?= $sizeFormatted ?>')" style="padding: 4px 8px; font-size: 0.75rem; color: #d97706; border-color: #f59e0b;" title="Pulihkan Database Langsung dari Berkas Ini">
                                                        <i class="fa-solid fa-arrow-rotate-left"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <!-- Hapus Berkas -->
                                                <form method="POST" action="<?= $baseUrl ?>/backup/delete" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas cadangan <?= SecurityHelper::escape($b['filename']) ?>?')" style="margin: 0;">
                                                    <?= CSRF::inputField() ?>
                                                    <input type="hidden" name="backup_id" value="<?= $b['id'] ?>">
                                                    <button type="submit" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 0.75rem; color: #ef4444; border-color: #fca5a5;" title="Hapus Berkas Cadangan">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer" style="background: #f8fafc; border-top: 1px solid var(--border-color, #e2e8f0); padding: 12px 20px; font-size: 0.75rem; color: #64748b; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-shield-halved" style="color: #059669;"></i>
                <span>Unduhan berkas diproses menggunakan chunk-streaming hemat RAM. Retensi arsip mingguan lama otomatis dibersihkan untuk menjaga kapasitas disk hosting.</span>
            </div>
        </div>

        <!-- CARD 5: PANDUAN OTOMASI CRON JOB / TASK SCHEDULER -->
        <div class="card" style="border: 1px solid #bfdbfe; border-radius: 10px; overflow: hidden; background: #ffffff;">
            <div class="card-header" style="background: #eff6ff; border-bottom: 1px solid #bfdbfe; padding: 14px 20px;">
                <h3 class="card-title" style="margin: 0; font-size: 0.95rem; display: flex; align-items: center; gap: 8px; color: #1e40af;">
                    <i class="fa-solid fa-terminal" style="color: #2563eb;"></i> Panduan Otomasi Pencadangan Mingguan (Cron Job &amp; Task Scheduler)
                </h3>
            </div>
            <div class="card-body" style="padding: 18px;">
                <p style="font-size: 0.8125rem; color: var(--text-muted, #64748b); line-height: 1.5; margin-bottom: 12px;">
                    Untuk menjamin data santri dan transaksi yayasan tidak hilang jika terjadi musibah server, jalankan pencadangan otomatis setiap minggu:
                </p>

                <!-- Linux VPS Crontab -->
                <div style="margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #334155;">
                            <i class="fa-brands fa-linux" style="color: #ea580c;"></i> Linux VPS / cPanel (Setiap Hari Minggu Pukul 02:00 WIB):
                        </span>
                        <button type="button" class="btn btn-outline btn-sm" onclick="copyText('cronCmdLinux')" style="padding: 2px 8px; font-size: 0.7rem;">
                            <i class="fa-solid fa-copy"></i> Salin
                        </button>
                    </div>
                    <pre id="cronCmdLinux" style="background: #0f172a; color: #34d399; padding: 10px 14px; border-radius: 6px; font-size: 0.75rem; font-family: monospace; overflow-x: auto; margin: 0;">0 2 * * 0 /usr/bin/php <?= dirname(dirname(__DIR__)) ?>/bin/cron_backup_weekly.php &gt;&gt; <?= dirname(dirname(__DIR__)) ?>/storage/logs/cron_backup.log 2&gt;&amp;1</pre>
                </div>

                <!-- Windows Server / XAMPP -->
                <div>
                    <span style="font-size: 0.8125rem; font-weight: 700; color: #334155; display: block; margin-bottom: 4px;">
                        <i class="fa-brands fa-windows" style="color: #0284c7;"></i> Windows Server / XAMPP (Windows Task Scheduler):
                    </span>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 6px; font-size: 0.75rem; font-family: monospace; color: #334155; line-height: 1.6;">
                        <strong>Program/script:</strong> <code>C:\xampp\php\php.exe</code><br>
                        <strong>Add arguments:</strong> <code><?= dirname(dirname(__DIR__)) ?>\bin\cron_backup_weekly.php</code>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Konfirmasi Pemulihan Langsung (Restore Existing Modal) -->
<div class="modal-overlay" id="directRestoreModal">
    <div class="modal-container" style="max-width: 500px;">
        <form method="POST" action="<?= $baseUrl ?>/backup/restore-existing" id="directRestoreForm">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="backup_id" id="restore_backup_id">
            
            <div class="modal-header" style="background: #fef2f2; border-bottom: 1px solid #fecaca; padding: 16px 20px;">
                <h3 class="card-title" style="margin: 0; font-size: 1.05rem; display: flex; align-items: center; gap: 8px; color: #991b1b;">
                    <i class="fa-solid fa-triangle-exclamation" style="color: #dc2626;"></i> Konfirmasi Pemulihan Database
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeDirectRestoreModal()">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <p style="font-size: 0.875rem; color: #0f172a; margin-bottom: 14px; line-height: 1.5;">
                    Anda akan memulihkan database seluruh sistem dari berkas cadangan:
                </p>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px;">
                    <div style="font-weight: 700; font-family: monospace; color: #0f172a; word-break: break-all;" id="restoreFileNameLabel">
                        backup_file.sql
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;" id="restoreFileSizeLabel">
                        Ukuran: -
                    </div>
                </div>

                <div class="alert alert-danger" style="font-size: 0.8125rem; line-height: 1.4; padding: 12px; margin-bottom: 16px; border-radius: 6px;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>
                        <strong>PENTING:</strong> Tindakan ini akan menimpa seluruh rekaman database operasional saat ini. Pastikan tidak ada guru atau operator yang sedang menginput nilai atau transaksi saat proses berlangsung.
                    </div>
                </div>

                <div style="margin-bottom: 8px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.8125rem; color: #0f172a; cursor: pointer;">
                        <input type="checkbox" id="confirmRestoreCheck" onchange="toggleRestoreSubmitBtn(this.checked)" style="margin-top: 2px; width: 16px; height: 16px;">
                        <span>Saya memahami risiko dan mengonfirmasi untuk melakukan penimpaan (restore) database dari berkas ini.</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeDirectRestoreModal()">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnExecuteRestore" disabled style="background: #dc2626; border-color: #dc2626; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-arrow-rotate-left"></i> Eksekusi Pemulihan Database
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDirectRestoreModal(id, filename, size) {
    document.getElementById('restore_backup_id').value = id;
    document.getElementById('restoreFileNameLabel').innerText = filename;
    document.getElementById('restoreFileSizeLabel').innerText = 'Ukuran Berkas: ' + size;
    document.getElementById('confirmRestoreCheck').checked = false;
    document.getElementById('btnExecuteRestore').disabled = true;
    const modal = document.getElementById('directRestoreModal');
    if (modal) modal.classList.add('active');
}

function closeDirectRestoreModal() {
    const modal = document.getElementById('directRestoreModal');
    if (modal) modal.classList.remove('active');
}

function toggleRestoreSubmitBtn(checked) {
    const btn = document.getElementById('btnExecuteRestore');
    if (btn) btn.disabled = !checked;
}

function copyText(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = el.innerText || el.textContent;
    navigator.clipboard.writeText(text).then(() => {
        alert('Perintah berhasil disalin ke clipboard!');
    }).catch(err => {
        alert('Gagal menyalin: ' + err);
    });
}
</script>
