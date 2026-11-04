<?php
// views/deputi/calendar.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$baseUrl = Auth::baseUrl();
?>

<div class="page-header" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span class="badge badge-primary">Deputi Pendidikan</span>
                <span style="font-size: 0.8125rem; color: var(--text-muted, #64748b);">Ritme Kerja SOP: WM, MM, MR &amp; Kajian</span>
            </div>
            <h1 class="page-title" style="font-size: 1.5rem; font-weight: 700; color: var(--text-heading, #1e293b); margin: 0;">
                <i class="fa-solid fa-calendar-days" style="color: #2563eb; margin-right: 8px;"></i>
                Kalender Kerja Tahunan &amp; Meeting Manager (MoM)
            </h1>
            <p style="margin: 4px 0 0 0; color: var(--text-muted, #64748b); font-size: 0.875rem;">
                Deputi: <strong><?= SecurityHelper::escape($deputiUser['full_name'] ?? 'Deputi') ?></strong> &bull; Notula Rapat (MoM) terintegrasi dengan persentase realisasi komitmen tindak lanjut.
            </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center;">
            <?php if (count($deputiList) > 1 && Auth::hasAnyRole(['direktur_pendidikan', 'operator_yayasan'])): ?>
                <form method="GET" action="<?= $baseUrl ?>/deputi/calendar" style="display: flex; align-items: center; gap: 6px;">
                    <select name="deputi_id" class="form-control" style="font-size: 0.875rem;" onchange="this.form.submit()">
                        <?php foreach ($deputiList as $dl): ?>
                            <option value="<?= $dl['id'] ?>" <?= $dl['id'] == $deputiUserId ? 'selected' : '' ?>>
                                <?= SecurityHelper::escape($dl['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>
            <button class="btn btn-primary" onclick="openAddEventModal()" style="font-weight: 600;">
                <i class="fa-solid fa-plus"></i> Tambah Agenda Rapat/Kegiatan
            </button>
        </div>
    </div>
</div>

<!-- Category Stat Pills -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; margin-bottom: 24px;">
    <?php
    $catCounts = [
        'Weekly Meeting'   => 0,
        'Monthly Review'   => 0,
        'Management Review'=> 0,
        'Kajian Pekanan'   => 0,
        'Pelatihan SDM'    => 0,
    ];
    foreach ($events as $ev) {
        if (isset($catCounts[$ev['category']])) {
            $catCounts[$ev['category']]++;
        }
    }
    ?>
    <div class="card" style="padding: 12px 16px; border-radius: 10px; border-left: 4px solid #3b82f6;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Weekly Meeting (WM)</div>
        <div style="font-size: 1.25rem; font-weight: 700; color: #1e293b;"><?= $catCounts['Weekly Meeting'] ?> Agenda</div>
    </div>
    <div class="card" style="padding: 12px 16px; border-radius: 10px; border-left: 4px solid #8b5cf6;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Monthly Review (MM)</div>
        <div style="font-size: 1.25rem; font-weight: 700; color: #1e293b;"><?= $catCounts['Monthly Review'] ?> Agenda</div>
    </div>
    <div class="card" style="padding: 12px 16px; border-radius: 10px; border-left: 4px solid #ec4899;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Management Review (MR)</div>
        <div style="font-size: 1.25rem; font-weight: 700; color: #1e293b;"><?= $catCounts['Management Review'] ?> Agenda</div>
    </div>
    <div class="card" style="padding: 12px 16px; border-radius: 10px; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Kajian Pekanan Guru</div>
        <div style="font-size: 1.25rem; font-weight: 700; color: #1e293b;"><?= $catCounts['Kajian Pekanan'] ?> Sesi</div>
    </div>
    <div class="card" style="padding: 12px 16px; border-radius: 10px; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">Pelatihan SDM Guru</div>
        <div style="font-size: 1.25rem; font-weight: 700; color: #1e293b;"><?= $catCounts['Pelatihan SDM'] ?> Program</div>
    </div>
</div>

<!-- Event List / Timeline Table -->
<div class="card" style="background: var(--bg-card, #ffffff); border-radius: 12px; border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
    <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: #1e293b;">
            <i class="fa-solid fa-list-check" style="color: #4f46e5; margin-right: 6px;"></i> Daftar Agenda Kerja &amp; Rekap Realisasi MoM
        </h3>
    </div>

    <div class="table-responsive">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: #64748b;">
                    <th style="padding: 12px 16px;">Waktu &amp; Tanggal</th>
                    <th style="padding: 12px 16px;">Kategori</th>
                    <th style="padding: 12px 16px;">Agenda / Judul Kegiatan</th>
                    <th style="padding: 12px 16px;">Unit / Lokasi</th>
                    <th style="padding: 12px 16px; text-align: center;">Undangan</th>
                    <th style="padding: 12px 16px; text-align: center;">Notula &amp; Realisasi</th>
                    <th style="padding: 12px 16px; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="7" style="padding: 30px; text-align: center; color: var(--text-muted);">
                            Belum ada agenda rapat atau kegiatan yang dijadwalkan. Klik "Tambah Agenda" untuk memulai.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $ev): ?>
                        <?php
                            $catColors = [
                                'Weekly Meeting'   => ['bg' => '#eff6ff', 'text' => '#1d4ed8'],
                                'Monthly Review'   => ['bg' => '#f5f3ff', 'text' => '#6d28d9'],
                                'Management Review'=> ['bg' => '#fdf2f8', 'text' => '#be185d'],
                                'Kajian Pekanan'   => ['bg' => '#ecfdf5', 'text' => '#047857'],
                                'Pelatihan SDM'    => ['bg' => '#fffbeb', 'text' => '#b45309'],
                                'Sosialisasi Visi Misi' => ['bg' => '#f0fdfa', 'text' => '#0f766e'],
                                'Timeplan SPMB/PGB/RAPBS' => ['bg' => '#f1f5f9', 'text' => '#334155'],
                                'Lainnya'          => ['bg' => '#f1f5f9', 'text' => '#475569'],
                            ];
                            $cColor = $catColors[$ev['category']] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
                        ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 12px 16px; white-space: nowrap;">
                                <div style="font-weight: 700; color: #1e293b; font-size: 0.875rem;">
                                    <?= date('d M Y', strtotime($ev['start_date'])) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b;">
                                    <?= !empty($ev['start_time']) ? date('H:i', strtotime($ev['start_time'])) . ' WIB' : 'Penuh' ?>
                                </div>
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="badge" style="background: <?= $cColor['bg'] ?>; color: <?= $cColor['text'] ?>; font-size: 11px; padding: 4px 8px;">
                                    <?= SecurityHelper::escape($ev['category']) ?>
                                </span>
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 600; color: #1e293b; font-size: 0.875rem;">
                                    <?= SecurityHelper::escape($ev['title']) ?>
                                </div>
                                <?php if (!empty($ev['description'])): ?>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                        <?= SecurityHelper::escape(mb_strimwidth($ev['description'], 0, 70, '...')) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 16px; font-size: 0.8125rem; color: #475569;">
                                <div><i class="fa-solid fa-school" style="font-size: 10px; color: #94a3b8;"></i> <?= SecurityHelper::escape($ev['unit_name'] ?: 'Semua Unit') ?></div>
                                <?php if (!empty($ev['location'])): ?>
                                    <div style="font-size: 0.75rem; color: #64748b;"><i class="fa-solid fa-location-dot" style="font-size: 10px; color: #94a3b8;"></i> <?= SecurityHelper::escape($ev['location']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <?php if (!empty($ev['invitation_sent_at'])): ?>
                                    <span class="badge" style="background: #dcfce7; color: #15803d; font-size: 10px;">
                                        <i class="fa-solid fa-check"></i> Terkirim (<?= $ev['invitation_h_days'] >= 1 ? "H-{$ev['invitation_h_days']}" : "Hari H" ?>)
                                    </span>
                                <?php else: ?>
                                    <button type="button" class="btn btn-outline" onclick="openInviteModal(<?= $ev['id'] ?>, '<?= SecurityHelper::escape($ev['title']) ?>')" style="padding: 2px 8px; font-size: 11px;">
                                        <i class="fa-solid fa-envelope"></i> Kirim Undangan
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <?php if (!empty($ev['mom_id'])): ?>
                                    <div>
                                        <span class="badge" style="background: #e0f2fe; color: #0284c7; font-size: 11px; font-weight: 700;">
                                            MoM: <?= number_format((float)$ev['realization_pct'], 0) ?>% Tuntas
                                        </span>
                                    </div>
                                    <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                        <?= (int)$ev['completed_actions'] ?>/<?= (int)$ev['total_actions'] ?> komitmen
                                    </div>
                                <?php else: ?>
                                    <span class="badge" style="background: #f1f5f9; color: #94a3b8; font-size: 10px;">Belum Diisi</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 16px; text-align: right; white-space: nowrap;">
                                <button class="btn btn-primary" onclick="openMomModal(<?= $ev['id'] ?>)" style="padding: 4px 10px; font-size: 0.8125rem;">
                                    <i class="fa-solid fa-file-pen"></i> MoM &amp; Komitmen
                                </button>
                                <form action="<?= $baseUrl ?>/deputi/calendar/delete-event" method="POST" style="display: inline-block;" onsubmit="return confirm('Hapus agenda kegiatan ini?')">
                                    <?= CSRF::inputField() ?>
                                    <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                                    <input type="hidden" name="deputi_id" value="<?= $deputiUserId ?>">
                                    <button type="submit" class="btn btn-outline" style="padding: 4px 8px; font-size: 0.8125rem; color: #dc2626; border-color: #fecaca;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: TAMBAH / EDIT AGENDA                                              -->
<!-- ========================================================================= -->
<div class="modal-overlay" id="addEventModal">
    <div class="modal-container" style="max-width: 600px;">
        <form action="<?= $baseUrl ?>/deputi/calendar/save-event" method="POST">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="deputi_user_id" value="<?= $deputiUserId ?>">

            <div class="modal-header">
                <h3 class="card-title" style="margin: 0; font-size: 1.125rem;">
                    <i class="fa-solid fa-calendar-plus" style="color: #2563eb;"></i> Tambah Agenda Rapat &amp; Kegiatan
                </h3>
            </div>
            <div class="modal-body" style="display: flex; flex-direction: column; gap: 14px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Judul Agenda / Rapat *</label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: Weekly Meeting WM 1 - Pembahasan Kesiapan KBM" required>
                </div>
                <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Kategori Agenda *</label>
                        <select name="category" class="form-control" required>
                            <option value="Weekly Meeting">Weekly Meeting (WM)</option>
                            <option value="Monthly Review">Monthly Review (MM)</option>
                            <option value="Management Review">Management Review (MR)</option>
                            <option value="Kajian Pekanan">Kajian Pekanan Guru</option>
                            <option value="Sosialisasi Visi Misi">Sosialisasi Visi Misi &amp; QO</option>
                            <option value="Pelatihan SDM">Pelatihan &amp; Kompetensi SDM</option>
                            <option value="Timeplan SPMB/PGB/RAPBS">Timeplan SPMB/PGB/RAPBS</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Unit Terkait</label>
                        <select name="unit_id" class="form-control">
                            <option value="">-- Semua Unit / Lintas Yayasan --</option>
                            <?php foreach ($units as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= SecurityHelper::escape($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Tanggal Pelaksanaan *</label>
                        <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Waktu Mulai</label>
                        <input type="time" name="start_time" class="form-control" value="08:30">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Lokasi / Tautan Rapat</label>
                    <input type="text" name="location" class="form-control" placeholder="Contoh: Ruang Rapat Yayasan / Google Meet">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Deskripsi &amp; Pokok Bahasan</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Tuliskan pokok agenda yang akan dibahas..."></textarea>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="closeAddEventModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Jadwalkan Agenda</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: POP-UP NOTULA MoM & DAFTAR KOMITMEN TINDAK LANJUT                -->
<!-- ========================================================================= -->
<div class="modal-overlay" id="momModal">
    <div class="modal-container" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
        <form action="<?= $baseUrl ?>/deputi/calendar/save-mom" method="POST" enctype="multipart/form-data">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="event_id" id="mom_event_id" value="0">
            <input type="hidden" name="deputi_id" value="<?= $deputiUserId ?>">

            <div class="modal-header">
                <div>
                    <h3 class="card-title" id="momModalTitle" style="margin: 0; font-size: 1.125rem;">
                        <i class="fa-solid fa-file-signature" style="color: #0d9488;"></i> Notula Rapat (MoM) &amp; Komitmen Tindak Lanjut
                    </h3>
                    <div id="momEventSubtitle" style="font-size: 0.8125rem; color: #64748b; margin-top: 2px;"></div>
                </div>
            </div>

            <div class="modal-body" style="display: flex; flex-direction: column; gap: 16px;">
                <!-- Rekap Agenda Belum Tuntas dari Rapat Lalu -->
                <div id="prevUnfinishedBox" style="display: none; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 14px;">
                    <div style="font-weight: 700; color: #92400e; font-size: 0.8125rem; margin-bottom: 6px;">
                        <i class="fa-solid fa-clock-rotate-left"></i> Catatan Komitmen yang Belum Tuntas dari Rapat Sebelumnya:
                    </div>
                    <ul id="prevUnfinishedList" style="margin: 0; padding-left: 20px; font-size: 0.8125rem; color: #78350f;"></ul>
                </div>

                <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Tanggal Notula / Pelaksanaan *</label>
                        <input type="date" name="meeting_date" id="mom_meeting_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Upload Lampiran Berkas MoM (PDF/Doc/Foto)</label>
                        <input type="file" name="mom_file" class="form-control">
                        <div id="momCurrentFile" style="font-size: 11px; color: #0369a1; margin-top: 3px;"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Isi Notula Rapat &amp; Keputusan Musyawarah *</label>
                    <textarea name="mom_content" id="mom_content" class="form-control" rows="4" placeholder="Tuliskan ringkasan jalannya rapat, hasil kesepakatan, dan keputusan rapat..." required></textarea>
                </div>

                <!-- Action Items Section -->
                <div style="background: #f8fafc; border-radius: 8px; padding: 14px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <div style="font-weight: 700; font-size: 0.875rem; color: #1e293b;">
                            <i class="fa-solid fa-list-check" style="color: #4f46e5;"></i> Butir Komitmen / Action Items (Tindak Lanjut)
                        </div>
                        <button type="button" class="btn btn-outline" onclick="addActionItemRow()" style="padding: 2px 8px; font-size: 11px;">
                            <i class="fa-solid fa-plus"></i> Tambah Butir
                        </button>
                    </div>

                    <!-- Existing Actions Table -->
                    <div id="existingActionsWrapper" style="margin-bottom: 10px;"></div>

                    <!-- New Action Rows Container -->
                    <div id="newActionsContainer" style="display: flex; flex-direction: column; gap: 8px;"></div>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="closeMomModal()">Tutup</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Notula &amp; Komitmen</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: KIRIM UNDANGAN RAPAT                                             -->
<!-- ========================================================================= -->
<div class="modal-overlay" id="inviteModal">
    <div class="modal-container" style="max-width: 500px;">
        <form action="<?= $baseUrl ?>/deputi/calendar/send-invitation" method="POST">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="event_id" id="invite_event_id" value="0">
            <input type="hidden" name="deputi_id" value="<?= $deputiUserId ?>">

            <div class="modal-header">
                <h3 class="card-title" style="margin: 0; font-size: 1.125rem;">
                    <i class="fa-solid fa-paper-plane" style="color: #2563eb;"></i> Broadcast Undangan Rapat
                </h3>
            </div>
            <div class="modal-body">
                <p style="font-size: 0.875rem; color: #334155; margin-bottom: 12px;">
                    Kirim notifikasi &amp; email undangan untuk agenda: <strong id="inviteEventTitle"></strong>
                </p>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Pilih Stakeholder / Peserta Rapat:</label>
                    <div style="display: flex; flex-direction: column; gap: 6px; max-height: 180px; overflow-y: auto; background: #f8fafc; padding: 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                        <?php 
                        $stakeholders = Database::fetchAll("SELECT u.id, u.full_name, r.display_name FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE r.name IN ('kepala_sekolah', 'waka_kurikulum', 'waka_kesiswaan', 'direktur_pendidikan') GROUP BY u.id ORDER BY u.full_name ASC");
                        foreach ($stakeholders as $sh):
                        ?>
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; cursor: pointer;">
                                <input type="checkbox" name="recipients[]" value="<?= $sh['id'] ?>" checked style="accent-color: #2563eb;">
                                <span><?= SecurityHelper::escape($sh['full_name']) ?> <span style="color: #64748b; font-size: 11px;">(<?= SecurityHelper::escape($sh['display_name']) ?>)</span></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="btn btn-outline" onclick="closeInviteModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Kirim Sekarang</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddEventModal() {
    document.getElementById('addEventModal').classList.add('active');
}
function closeAddEventModal() {
    document.getElementById('addEventModal').classList.remove('active');
}

function openInviteModal(eventId, title) {
    document.getElementById('invite_event_id').value = eventId;
    document.getElementById('inviteEventTitle').innerText = title;
    document.getElementById('inviteModal').classList.add('active');
}
function closeInviteModal() {
    document.getElementById('inviteModal').classList.remove('active');
}

function openMomModal(eventId) {
    document.getElementById('mom_event_id').value = eventId;
    document.getElementById('newActionsContainer').innerHTML = '';
    document.getElementById('existingActionsWrapper').innerHTML = '<div style="text-align:center; padding: 10px;"><i class="fa-solid fa-spinner fa-spin"></i> Memuat data notula...</div>';

    fetch('<?= $baseUrl ?>/deputi/calendar/get-event-details?id=' + eventId)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (!data.success) {
                alert('Gagal memuat detail agenda.');
                return;
            }
            document.getElementById('momModalTitle').innerText = 'Notula: ' + data.event.title;
            document.getElementById('momEventSubtitle').innerText = (data.event.unit_name || 'Lintas Yayasan') + ' • ' + data.event.start_date + ' ' + (data.event.start_time || '');
            document.getElementById('mom_meeting_date').value = (data.mom && data.mom.meeting_date) ? data.mom.meeting_date : data.event.start_date;
            document.getElementById('mom_content').value = (data.mom && data.mom.mom_content) ? data.mom.mom_content : '';

            var fileSpan = document.getElementById('momCurrentFile');
            if (data.mom && data.mom.file_path) {
                fileSpan.innerHTML = '<i class="fa-solid fa-paperclip"></i> File tersimpan: <a href="' + data.mom.file_path + '" target="_blank">Lihat Dokumen</a>';
            } else {
                fileSpan.innerHTML = '';
            }

            // Unfinished from previous
            var prevBox = document.getElementById('prevUnfinishedBox');
            var prevList = document.getElementById('prevUnfinishedList');
            if (data.unfinishedPrevious && data.unfinishedPrevious.length > 0) {
                prevList.innerHTML = '';
                data.unfinishedPrevious.forEach(function(item) {
                    var li = document.createElement('li');
                    li.innerHTML = '<strong>' + item.action_topic + '</strong> (PIC: ' + (item.pic_name || '-') + ') &bull; <em>Dari: ' + item.prev_event_title + ' (' + item.prev_date + ')</em>';
                    prevList.appendChild(li);
                });
                prevBox.style.display = 'block';
            } else {
                prevBox.style.display = 'none';
            }

            // Existing actions table
            var exWrapper = document.getElementById('existingActionsWrapper');
            if (data.actions && data.actions.length > 0) {
                var html = '<table style="width: 100%; font-size: 0.8125rem; border-collapse: collapse;">' +
                           '<tr style="background:#e2e8f0; font-weight:700;"><th style="padding:4px 8px;">Topik Komitmen</th><th style="padding:4px 8px;">PIC</th><th style="padding:4px 8px;">Tenggat</th><th style="padding:4px 8px; text-align:center;">Status</th></tr>';
                data.actions.forEach(function(a) {
                    var isDone = a.status === 'Completed';
                    html += '<tr style="border-bottom:1px solid #e2e8f0;">' +
                            '<td style="padding:6px 8px;">' + a.action_topic + '</td>' +
                            '<td style="padding:6px 8px;">' + (a.pic_name || '-') + '</td>' +
                            '<td style="padding:6px 8px;">' + (a.due_date || '-') + '</td>' +
                            '<td style="padding:6px 8px; text-align:center;">' +
                            '  <button type="button" class="btn" style="padding:2px 8px; font-size:10px; background:' + (isDone ? '#dcfce7' : '#fee2e2') + '; color:' + (isDone ? '#15803d' : '#b91c1c') + ';" onclick="toggleAction(' + a.id + ', \'' + (isDone ? 'Pending' : 'Completed') + '\')">' +
                            (isDone ? '<i class="fa-solid fa-check"></i> Selesai' : '<i class="fa-solid fa-hourglass-half"></i> Pending') +
                            '  </button>' +
                            '</td></tr>';
                });
                html += '</table>';
                exWrapper.innerHTML = html;
            } else {
                exWrapper.innerHTML = '<div style="font-size:0.75rem; color:#94a3b8; font-style:italic;">Belum ada komitmen aksi tercatat. Klik Tambah Butir di atas.</div>';
            }

            document.getElementById('momModal').classList.add('active');
        });
}

function closeMomModal() {
    document.getElementById('momModal').classList.remove('active');
}

function addActionItemRow() {
    var c = document.getElementById('newActionsContainer');
    var div = document.createElement('div');
    div.style.display = 'grid';
    div.style.gridTemplateColumns = '2fr 1fr 1fr auto';
    div.style.gap = '6px';
    div.style.alignItems = 'center';
    div.innerHTML = '<input type="text" name="action_topics[]" class="form-control" style="font-size:0.8125rem;" placeholder="Poin komitmen / aksi..." required>' +
                    '<input type="text" name="action_pics[]" class="form-control" style="font-size:0.8125rem;" placeholder="Nama PIC">' +
                    '<input type="date" name="action_due_dates[]" class="form-control" style="font-size:0.8125rem;">' +
                    '<button type="button" class="btn btn-outline" style="padding:4px 8px; color:#dc2626;" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>';
    c.appendChild(div);
}

function toggleAction(actionId, status) {
    var f = document.createElement('form');
    f.method = 'POST';
    f.action = '<?= $baseUrl ?>/deputi/calendar/toggle-action';
    f.innerHTML = '<?= CSRF::inputField() ?>' +
                  '<input type="hidden" name="action_id" value="' + actionId + '">' +
                  '<input type="hidden" name="status" value="' + status + '">' +
                  '<input type="hidden" name="deputi_id" value="<?= $deputiUserId ?>">';
    document.body.appendChild(f);
    f.submit();
}
</script>
