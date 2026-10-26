<?php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$totalKegiatan = count($kegiatan ?? []);
$totalPartisipasi = 0;
foreach ($kegiatan as $k) {
    $totalPartisipasi += (int)($k['participant_count'] ?? 0);
}
$avgPartisipasi = $totalKegiatan > 0 ? round($totalPartisipasi / $totalKegiatan, 1) : 0;
?>

<style>
.kegiatan-row:hover {
    background-color: #f0fdfa !important;
}
.kegiatan-row:hover div:first-child span {
    text-decoration: underline;
}
</style>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span class="badge badge-primary">Dokumen No. 9</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Administrasi Kepala Sekolah</span>
            </div>
            <h1 class="page-title" style="margin: 0 0 6px 0; font-size: 1.5rem; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-calendar-check" style="color: #0d9488;"></i> Kegiatan di Luar KBM &amp; Presensi Pegawai
            </h1>
            <p class="page-subtitle" style="margin: 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Pencatatan agenda kegiatan di luar KBM (rihlah, manasik haji, PHBI, parenting) beserta absensi kehadiran pegawai.
            </p>
        </div>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0d9488;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Total Kegiatan</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-main, #0f172a); margin: 6px 0 2px 0;">
            <?= $totalKegiatan ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Agenda</span>
        </div>
        <div style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Tahun ajaran aktif</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Akumulasi Presensi</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981; margin: 6px 0 2px 0;">
            <?= $totalPartisipasi ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Kehadiran</span>
        </div>
        <div style="font-size: 0.8125rem; color: #10b981; font-weight: 500;">Guru &amp; staf hadir</div>
    </div>

    <div class="card" style="padding: 16px 20px; border-left: 4px solid #0284c7;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted, #64748b); font-weight: 600; letter-spacing: 0.5px;">Rata-rata Partisipasi</div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0284c7; margin: 6px 0 2px 0;">
            <?= $avgPartisipasi ?> <span style="font-size: 0.875rem; font-weight: 400; color: #64748b;">Pegawai</span>
        </div>
        <div style="font-size: 0.8125rem; color: #0284c7; font-weight: 500;">Per kegiatan luar KBM</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start;">
    <!-- Form Card -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-calendar-plus" style="color: #0d9488;"></i> Tambah Kegiatan Luar KBM
            </h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <form action="<?= Auth::baseUrl() ?>/kegiatan/luar-kbm/save" method="POST">
                <?= CSRF::field() ?>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nama Kegiatan / Acara <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: Manasik Haji Cilik, Rihlah, PHBI..." required>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Tanggal Pelaksanaan <span style="color: #ef4444;">*</span></label>
                    <input type="date" name="event_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Lokasi Kegiatan</label>
                    <input type="text" name="location" class="form-control" value="Masjid / Luar Lingkungan Sekolah">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem; margin: 0;">Presensi Guru &amp; Pegawai</label>
                        <div style="display: flex; gap: 4px; font-size: 0.75rem;">
                            <span style="cursor: pointer; color: #0d9488; font-weight: 600;" id="kegSelectAll">Pilih Semua</span>
                            <span style="color: #cbd5e1;">|</span>
                            <span style="cursor: pointer; color: #ef4444; font-weight: 600;" id="kegDeselectAll">Batal</span>
                        </div>
                    </div>
                    <div style="border: 1px solid var(--border-color, #e2e8f0); border-radius: 6px; padding: 10px 12px; max-height: 200px; overflow-y: auto; background: #fafafa;">
                        <?php foreach ($employees as $e): ?>
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; margin-bottom: 6px; cursor: pointer;">
                                <input class="keg-emp-checkbox" type="checkbox" name="emp_ids[]" value="<?= $e['id'] ?>" style="accent-color: #0d9488;" checked>
                                <span>
                                    <strong><?= SecurityHelper::escape($e['nama']) ?></strong>
                                    <small style="color: var(--text-muted, #64748b);">(<?= SecurityHelper::escape($e['position']) ?>)</small>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 16px; font-weight: 600;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Kegiatan &amp; Presensi
                </button>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color, #e2e8f0); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-list-check" style="color: #0d9488;"></i>
                <h3 class="card-title" style="margin: 0; font-size: 1rem;">Daftar Kegiatan di Luar KBM</h3>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #94a3b8;"></i>
                    <input type="text" id="searchKegiatan" class="form-control" placeholder="Cari kegiatan / lokasi..." style="padding-left: 28px; font-size: 0.8125rem; height: 34px; width: 200px;">
                </div>
                <span class="badge badge-secondary"><?= $totalKegiatan ?> Kegiatan</span>
            </div>
        </div>

        <div class="card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">No</th>
                            <th>Nama Kegiatan / Acara</th>
                            <th style="width: 130px;">Tanggal</th>
                            <th>Lokasi Kegiatan</th>
                            <th style="width: 140px; text-align: center;">Presensi Hadir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($kegiatan)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 36px; color: var(--text-muted, #64748b);">
                                    <i class="fa-solid fa-calendar-xmark" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                                    Belum ada data kegiatan luar KBM untuk tahun ajaran ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($kegiatan as $idx => $k): 
                                $partList = $participantsByKegiatan[$k['id']] ?? [];
                            ?>
                                <tr class="kegiatan-row" 
                                    data-id="<?= $k['id'] ?>"
                                    data-title="<?= htmlspecialchars($k['title'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-date="<?= htmlspecialchars(SecurityHelper::formatTanggalIndo($k['event_date']), ENT_QUOTES, 'UTF-8') ?>"
                                    data-search="<?= strtolower(SecurityHelper::escape($k['title'] . ' ' . $k['location'] . ' ' . $k['event_date'])) ?>" 
                                    onclick="handleKegiatanRowClick(this)"
                                    style="cursor: pointer; transition: background 0.15s ease;" 
                                    title="Klik untuk melihat daftar peserta kegiatan ini">
                                    <td style="text-align: center; color: var(--text-muted, #64748b);"><?= $idx + 1 ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: #0d9488; font-size: 0.9375rem; display: flex; align-items: center; gap: 6px;">
                                            <span><?= SecurityHelper::escape($k['title']) ?></span>
                                            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.7rem; opacity: 0.7;"></i>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted, #64748b); margin-top: 2px;">
                                            <span style="color: #0d9488; font-weight: 500;"><i class="fa-solid fa-hand-pointer"></i> Klik baris</span> untuk lihat <?= count($partList) ?> partisipan
                                        </div>
                                        <script type="application/json" class="row-part-json">
                                            <?= json_encode($partList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
                                        </script>
                                    </td>
                                    <td style="color: var(--text-muted, #64748b); font-size: 0.85rem;">
                                        <?= SecurityHelper::formatTanggalIndo($k['event_date']) ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 6px; font-size: 0.875rem;">
                                            <i class="fa-solid fa-location-dot" style="color: #ef4444; font-size: 0.8rem;"></i>
                                            <?= SecurityHelper::escape($k['location']) ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn btn-outline btn-sm btn-view-part" 
                                              onclick="event.stopPropagation(); handleKegiatanRowClick(this.closest('tr'))"
                                              style="font-weight: 600; display: inline-flex; align-items: center; gap: 6px; color: #0d9488; border-color: rgba(13, 148, 136, 0.4); padding: 4px 12px; cursor: pointer;">
                                            <i class="fa-solid fa-users"></i> <?= (int)$k['participant_count'] ?> Pegawai
                                            <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem; opacity: 0.7;"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- JSON Data Partisipan Global Map -->
<script id="participantsJson" type="application/json">
<?= json_encode($participantsByKegiatan, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
</script>

<style>
.kegiatan-row {
    cursor: pointer !important;
}
.kegiatan-row:hover {
    background-color: #f0fdfa !important;
}
.kegiatan-row:hover td {
    background-color: #f0fdfa !important;
}
#partModal.active,
#partModal[style*="display: flex"],
#partModal[style*="display:flex"] {
    display: flex !important;
    opacity: 1 !important;
    visibility: visible !important;
    z-index: 99999 !important;
}
</style>

<!-- Modal Daftar Partisipan Kegiatan -->
<div class="modal-overlay" id="partModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 99999; align-items: center; justify-content: center; padding: 20px;">
    <div class="modal-container" style="background: white; border-radius: 12px; width: 100%; max-width: 600px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header" style="padding: 16px 20px; border-bottom: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div>
                <h3 class="card-title" id="partModalTitle" style="margin: 0; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-users text-primary"></i> Daftar Hadir Partisipan
                </h3>
                <div id="partModalSub" style="font-size: 0.8rem; color: var(--text-muted, #64748b); margin-top: 2px;"></div>
            </div>
            <button type="button" class="btn btn-outline btn-sm" style="border: none; font-size: 1.25rem; line-height: 1; padding: 4px 8px; cursor: pointer;" onclick="closeParticipantModal()">&times;</button>
        </div>

        <div class="modal-body" style="padding: 0; overflow-y: auto; flex: 1;">
            <div id="partListContainer">
                <!-- Diisi via JS -->
            </div>
        </div>

        <div class="modal-footer" style="padding: 12px 20px; border-top: 1px solid var(--border-color, #e2e8f0); background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <div id="partTotalBadge" style="font-size: 0.85rem; font-weight: 600; color: #0d9488;"></div>
            <button type="button" class="btn btn-primary btn-sm" onclick="closeParticipantModal()" style="padding: 6px 18px; cursor: pointer;">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
let participantsMap = {};
try {
    const raw = document.getElementById('participantsJson');
    if (raw) participantsMap = JSON.parse(raw.textContent);
} catch (e) {
    console.error('Failed to parse participants JSON:', e);
}

function handleKegiatanRowClick(rowEl) {
    if (!rowEl) return;
    const title = rowEl.getAttribute('data-title') || '';
    const dateStr = rowEl.getAttribute('data-date') || '';
    const id = rowEl.getAttribute('data-id') || '';
    
    let list = [];
    const scriptTag = rowEl.querySelector('.row-part-json');
    if (scriptTag) {
        try {
            list = JSON.parse(scriptTag.textContent);
        } catch(e) {
            console.error('Error parsing row JSON:', e);
        }
    }
    if ((!list || list.length === 0) && id && participantsMap[id]) {
        list = participantsMap[id];
    }
    openParticipantModal(title, dateStr, list);
}

function openParticipantModal(title, dateStr, participants) {
    document.getElementById('partModalTitle').innerHTML = '<i class="fa-solid fa-users" style="color: #0d9488;"></i> ' + escapeHtml(title);
    document.getElementById('partModalSub').textContent = 'Tanggal Pelaksanaan: ' + dateStr;
    document.getElementById('partTotalBadge').textContent = 'Total: ' + (participants ? participants.length : 0) + ' Pegawai Hadir';

    const container = document.getElementById('partListContainer');
    if (!participants || participants.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px 20px; color: #64748b;"><i class="fa-solid fa-user-slash" style="font-size: 2rem; margin-bottom: 8px; display: block; opacity: 0.5;"></i>Belum ada daftar partisipan tercatat untuk kegiatan ini.</div>';
    } else {
        let html = '<table class="table" style="margin: 0; width: 100%;"><thead><tr style="background: #f8fafc;"><th style="width: 40px; text-align: center;">No</th><th>Nama Pegawai & NIPY</th><th>Jabatan / Posisi</th><th style="width: 100px; text-align: center;">Status</th></tr></thead><tbody>';
        participants.forEach(function(p, idx) {
            html += '<tr>' +
                '<td style="text-align: center; color: #64748b; font-size: 0.85rem;">' + (idx + 1) + '</td>' +
                '<td>' +
                    '<div style="font-weight: 600; color: #0f172a;">' + escapeHtml(p.nama) + '</div>' +
                    '<div style="font-size: 0.75rem; color: #64748b; font-family: monospace;">NIPY: ' + escapeHtml(p.nipy || '-') + '</div>' +
                '</td>' +
                '<td style="font-size: 0.85rem;">' + escapeHtml(p.position || 'Pegawai') + '</td>' +
                '<td style="text-align: center;">' +
                    '<span class="badge badge-success" style="font-size: 0.75rem;"><i class="fa-solid fa-check"></i> ' + escapeHtml(p.status || 'Hadir') + '</span>' +
                '</td>' +
            '</tr>';
        });
        html += '</tbody></table>';
        container.innerHTML = html;
    }

    const modal = document.getElementById('partModal');
    if (modal) {
        modal.classList.add('active');
        modal.style.display = 'flex';
        modal.style.opacity = '1';
        modal.style.visibility = 'visible';
    }
}

function closeParticipantModal() {
    const modal = document.getElementById('partModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
        modal.style.opacity = '0';
        modal.style.visibility = 'hidden';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

window.handleKegiatanRowClick = handleKegiatanRowClick;
window.openParticipantModal = openParticipantModal;
window.closeParticipantModal = closeParticipantModal;

document.addEventListener('DOMContentLoaded', function() {
    const search = document.getElementById('searchKegiatan');
    const rows = document.querySelectorAll('.kegiatan-row');
    if (search) {
        search.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            rows.forEach(function(row) {
                const text = row.getAttribute('data-search') || '';
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }

    const btnAll = document.getElementById('kegSelectAll');
    const btnNone = document.getElementById('kegDeselectAll');
    const checkboxes = document.querySelectorAll('.keg-emp-checkbox');

    if (btnAll) {
        btnAll.addEventListener('click', function() {
            checkboxes.forEach(function(cb) { cb.checked = true; });
        });
    }
    if (btnNone) {
        btnNone.addEventListener('click', function() {
            checkboxes.forEach(function(cb) { cb.checked = false; });
        });
    }

    const modal = document.getElementById('partModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeParticipantModal();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeParticipantModal();
        }
    });
});
</script>
