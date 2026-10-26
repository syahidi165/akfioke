<?php
// views/supervisi/form.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;

$sessionDateIndo = SecurityHelper::formatTanggalIndo($session['session_date'] ?? date('Y-m-d'));
?>

<div class="supervisi-form-wrapper">
    <!-- Header Informasi Sesi Supervisi -->
    <div class="card" style="margin-bottom: 20px; border-left: 5px solid var(--primary);">
        <div class="card-body" style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div style="flex: 1; min-width: 280px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <span class="badge badge-info" style="font-size: 11px;">Instrumen Supervisi Kelas</span>
                        <span class="badge badge-secondary" style="font-size: 11px;"><?= SecurityHelper::escape($session['unit_name'] ?? 'Unit') ?></span>
                    </div>
                    <h2 style="font-size: 20px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                        <i class="fa-solid fa-chalkboard-user" style="color: var(--primary); margin-right: 6px;"></i>
                        <?= SecurityHelper::escape($session['teacher_name']) ?>
                    </h2>
                    <div style="font-size: 13px; color: var(--text-muted); display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                        <span><i class="fa-solid fa-id-badge" style="margin-right: 4px;"></i> NIPY: <strong><?= SecurityHelper::escape($session['teacher_nipy']) ?></strong></span>
                        <span><i class="fa-solid fa-book" style="margin-right: 4px;"></i> Mapel: <strong><?= SecurityHelper::escape($session['subject_name']) ?></strong></span>
                        <span><i class="fa-solid fa-users-rectangle" style="margin-right: 4px;"></i> Kelas: <strong><?= SecurityHelper::escape($session['class_name']) ?></strong></span>
                        <span><i class="fa-solid fa-calendar-days" style="margin-right: 4px;"></i> Tanggal: <strong><?= $sessionDateIndo ?></strong></span>
                    </div>
                </div>

                <!-- Live Score Box -->
                <div class="live-score-card">
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); letter-spacing: 0.5px;">Live Estimasi Skor</div>
                    <div style="display: flex; align-items: baseline; gap: 6px; margin: 4px 0;">
                        <span id="liveTotalBadge" style="font-size: 32px; font-weight: 800; color: var(--primary); line-height: 1;">0.0</span>
                        <span style="font-size: 13px; color: var(--text-muted); font-weight: 600;">/ 100</span>
                    </div>
                    <span id="livePredBadge" class="badge badge-secondary" style="font-size: 11px; font-weight: 700; padding: 4px 10px;">
                        PERLU PENINGKATAN
                    </span>
                </div>
            </div>
        </div>
    </div>

    <form action="<?= Auth::baseUrl() ?>/supervisi/save-score" method="POST" id="supervisiForm">
        <?= CSRF::field() ?>
        <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">

        <!-- ========================================== -->
        <!-- SEKSI A: PERENCANAAN PEMBELAJARAN (BOBOT 10) -->
        <!-- ========================================== -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header" style="background: #f0fdf4; border-bottom: 1px solid #bbf7d0; display: flex; justify-content: space-between; align-items: center; padding: 14px 20px;">
                <h3 class="card-title" style="color: #166534; font-size: 15px; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-folder-open"></i> A. ADMINISTRASI PERENCANAAN PEMBELAJARAN
                </h3>
                <span class="badge" style="background: #166534; color: #ffffff; font-weight: 700; padding: 4px 10px;">Maksimal Bobot: 10%</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table-supervisi">
                        <thead>
                            <tr>
                                <th style="width: 45px; text-align: center;">No</th>
                                <th>Aspek Indikator Administrasi</th>
                                <th style="width: 90px; text-align: center;">Bobot</th>
                                <th style="width: 320px; text-align: center;">Pilihan Skor Penilaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $itemsA = [
                                1 => ['Alur Tujuan Pembelajaran (ATP) / Prota', 1],
                                2 => ['Modul Ajar / Rencana Pelaksanaan Pembelajaran (RPP)', 1],
                                3 => ['Media Pembelajaran yang Relevan & Interaktif', 4],
                                4 => ['Daftar Nilai / Format Asesmen Formatif & Sumatif', 1],
                                5 => ['Agenda Harian / Jurnal Mengajar Guru', 2],
                                6 => ['Buku Presensi Peserta Didik', 1]
                            ];
                            foreach ($itemsA as $num => $row): 
                                $curVal = $savedScores['A'][$num] ?? 2;
                            ?>
                                <tr>
                                    <td style="text-align: center; font-weight: 700; color: var(--text-muted);"><?= $num ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main); font-size: 13.5px;"><?= $row[0] ?></div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-secondary" style="font-weight: 600;"><?= $row[1] ?>%</span>
                                    </td>
                                    <td>
                                        <div class="rating-pills-group">
                                            <label class="rating-pill <?= $curVal === 2 ? 'active' : '' ?>">
                                                <input type="radio" class="score-calc-a" name="sec_a[<?= $num ?>]" value="2" data-weight="<?= $row[1] ?>" <?= $curVal === 2 ? 'checked' : '' ?> required>
                                                <span class="pill-label pill-green">Lengkap (2)</span>
                                            </label>
                                            <label class="rating-pill <?= $curVal === 1 ? 'active' : '' ?>">
                                                <input type="radio" class="score-calc-a" name="sec_a[<?= $num ?>]" value="1" data-weight="<?= $row[1] ?>" <?= $curVal === 1 ? 'checked' : '' ?>>
                                                <span class="pill-label pill-amber">Kurang (1)</span>
                                            </label>
                                            <label class="rating-pill <?= $curVal === 0 ? 'active' : '' ?>">
                                                <input type="radio" class="score-calc-a" name="sec_a[<?= $num ?>]" value="0" data-weight="<?= $row[1] ?>" <?= $curVal === 0 ? 'checked' : '' ?>>
                                                <span class="pill-label pill-red">Tidak Ada (0)</span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- SEKSI B: KEGIATAN PENDAHULUAN (BOBOT 20)    -->
        <!-- ========================================== -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header" style="background: #eff6ff; border-bottom: 1px solid #bfdbfe; display: flex; justify-content: space-between; align-items: center; padding: 14px 20px;">
                <h3 class="card-title" style="color: #1e40af; font-size: 15px; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-door-open"></i> B. KEGIATAN PENDAHULUAN
                </h3>
                <span class="badge" style="background: #1e40af; color: #ffffff; font-weight: 700; padding: 4px 10px;">Maksimal Bobot: 20%</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table-supervisi">
                        <thead>
                            <tr>
                                <th style="width: 45px; text-align: center;">No</th>
                                <th>Aspek Indikator Pendahuluan</th>
                                <th style="width: 90px; text-align: center;">Bobot</th>
                                <th style="width: 380px; text-align: center;">Pilihan Skor Penilaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $itemsB = [
                                1 => ['Pelajaran diawali dengan ucapan salam dan do\'a bersama', 3],
                                2 => ['Menyampaikan tujuan pembelajaran dan manfaatnya dalam kehidupan sehari-hari', 7],
                                3 => ['Mengajukan pertanyaan yang menantang untuk memotivasi (imaniah) peserta didik', 10]
                            ];
                            foreach ($itemsB as $num => $row): 
                                $curVal = $savedScores['B'][$num] ?? 3;
                            ?>
                                <tr>
                                    <td style="text-align: center; font-weight: 700; color: var(--text-muted);"><?= $num ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main); font-size: 13.5px;"><?= $row[0] ?></div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-secondary" style="font-weight: 600;"><?= $row[1] ?>%</span>
                                    </td>
                                    <td>
                                        <div class="rating-pills-group">
                                            <label class="rating-pill <?= $curVal === 3 ? 'active' : '' ?>">
                                                <input type="radio" class="score-calc-b" name="sec_b[<?= $num ?>]" value="3" data-weight="<?= $row[1] ?>" <?= $curVal === 3 ? 'checked' : '' ?> required>
                                                <span class="pill-label pill-green">Sgt Baik (3)</span>
                                            </label>
                                            <label class="rating-pill <?= $curVal === 2 ? 'active' : '' ?>">
                                                <input type="radio" class="score-calc-b" name="sec_b[<?= $num ?>]" value="2" data-weight="<?= $row[1] ?>" <?= $curVal === 2 ? 'checked' : '' ?>>
                                                <span class="pill-label pill-blue">Baik (2)</span>
                                            </label>
                                            <label class="rating-pill <?= $curVal === 1 ? 'active' : '' ?>">
                                                <input type="radio" class="score-calc-b" name="sec_b[<?= $num ?>]" value="1" data-weight="<?= $row[1] ?>" <?= $curVal === 1 ? 'checked' : '' ?>>
                                                <span class="pill-label pill-amber">Cukup (1)</span>
                                            </label>
                                            <label class="rating-pill <?= $curVal === 0 ? 'active' : '' ?>">
                                                <input type="radio" class="score-calc-b" name="sec_b[<?= $num ?>]" value="0" data-weight="<?= $row[1] ?>" <?= $curVal === 0 ? 'checked' : '' ?>>
                                                <span class="pill-label pill-red">Kurang (0)</span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- SEKSI C: KEGIATAN INTI PEMBELAJARAN (70)   -->
        <!-- ========================================== -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header" style="background: #fdf4ff; border-bottom: 1px solid #f5d0fe; display: flex; justify-content: space-between; align-items: center; padding: 14px 20px;">
                <h3 class="card-title" style="color: #86198f; font-size: 15px; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-chalkboard"></i> C. KEGIATAN INTI PEMBELAJARAN
                </h3>
                <span class="badge" style="background: #86198f; color: #ffffff; font-weight: 700; padding: 4px 10px;">Maksimal Bobot: 70%</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div id="rule12Warning" class="alert alert-warning" style="display: none; margin: 16px 20px 0 20px;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 16px;"></i>
                    <div>
                        <strong>Aturan Resmi Indikator 12:</strong> Ada indikator nomor 1 s/d 11 bernilai kurang dari 2 (Cukup/Kurang), sehingga indikator 12 otomatis tidak dapat dipilih "Sangat Baik (3)".
                    </div>
                </div>

                <div class="table-responsive" style="margin-top: 12px;">
                    <table class="table-supervisi">
                        <thead>
                            <tr>
                                <th style="width: 45px; text-align: center;">No</th>
                                <th>Aspek Indikator Kegiatan Inti</th>
                                <th style="width: 90px; text-align: center;">Bobot</th>
                                <th style="width: 380px; text-align: center;">Pilihan Skor Penilaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $itemsC = [
                                1 => ['Guru menunjukkan sikap yang baik (antusias, ramah, percaya diri) dan sikap tubuh yang sesuai', 6],
                                2 => ['Guru menguasai materi dan menggunakan buku pegangan dengan baik (sesuai, tepat guna)', 6],
                                3 => ['Aktivitas di kelas bervariasi dan dinamis (interaksi murid-murid, murid-guru)', 6],
                                4 => ['Guru menanamkan nilai-nilai ruh keislaman, adab dan nilai positif lainnya', 6],
                                5 => ['Guru berbicara dengan suara yang jelas, intonasi bervariasi dan porsi seimbang', 6],
                                6 => ['Guru mengecek pemahaman siswa akan konsep yang diajarkan selama pelajaran', 6],
                                7 => ['Instruksi yang diberikan jelas dan mudah dipahami siswa', 7],
                                8 => ['Manajemen waktu pada pembelajaran ini sudah efektif', 6],
                                9 => ['Sarana dan prasarana pendukung pembelajaran digunakan dengan tepat', 6],
                                10 => ['Masukan/koreksi dari guru ke siswa diberikan dengan tepat, sesuai kebutuhan', 7],
                                11 => ['Guru melakukan asesmen hasil pembelajaran di akhir pembelajaran', 7],
                                12 => ['Pelajaran ini berlangsung lancar dan sukses (Aturan khusus: jika ada 1-11 bernilai < 2, tidak boleh Sangat Baik)', 1]
                            ];
                            foreach ($itemsC as $num => $row): 
                                $curVal = $savedScores['C'][$num] ?? 3;
                            ?>
                                <tr>
                                    <td style="text-align: center; font-weight: 700; color: var(--text-muted);"><?= $num ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-main); font-size: 13.5px;"><?= $row[0] ?></div>
                                        <?php if ($num === 12): ?>
                                            <div style="font-size: 11px; color: #dc2626; font-style: italic; margin-top: 3px;">
                                                <i class="fa-solid fa-lock"></i> Terkunci otomatis jika ada indikator 1-11 bernilai &lt; 2.
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-secondary" style="font-weight: 600;"><?= $row[1] ?>%</span>
                                    </td>
                                    <td>
                                        <div class="rating-pills-group">
                                            <label class="rating-pill <?= $curVal === 3 ? 'active' : '' ?>" id="label_c_<?= $num ?>_3">
                                                <input type="radio" class="score-calc-c c-item-<?= $num ?>" name="sec_c[<?= $num ?>]" id="c_<?= $num ?>_3" value="3" data-item="<?= $num ?>" data-weight="<?= $row[1] ?>" <?= $curVal === 3 ? 'checked' : '' ?> required>
                                                <span class="pill-label pill-green">Sgt Baik (3)</span>
                                            </label>
                                            <label class="rating-pill <?= $curVal === 2 ? 'active' : '' ?>">
                                                <input type="radio" class="score-calc-c c-item-<?= $num ?>" name="sec_c[<?= $num ?>]" id="c_<?= $num ?>_2" value="2" data-item="<?= $num ?>" data-weight="<?= $row[1] ?>" <?= $curVal === 2 ? 'checked' : '' ?>>
                                                <span class="pill-label pill-blue">Baik (2)</span>
                                            </label>
                                            <label class="rating-pill <?= $curVal === 1 ? 'active' : '' ?>">
                                                <input type="radio" class="score-calc-c c-item-<?= $num ?>" name="sec_c[<?= $num ?>]" id="c_<?= $num ?>_1" value="1" data-item="<?= $num ?>" data-weight="<?= $row[1] ?>" <?= $curVal === 1 ? 'checked' : '' ?>>
                                                <span class="pill-label pill-amber">Cukup (1)</span>
                                            </label>
                                            <label class="rating-pill <?= $curVal === 0 ? 'active' : '' ?>">
                                                <input type="radio" class="score-calc-c c-item-<?= $num ?>" name="sec_c[<?= $num ?>]" id="c_<?= $num ?>_0" value="0" data-item="<?= $num ?>" data-weight="<?= $row[1] ?>" <?= $curVal === 0 ? 'checked' : '' ?>>
                                                <span class="pill-label pill-red">Kurang (0)</span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- CATATAN & REKOMENDASI SUPERVISOR           -->
        <!-- ========================================== -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h3 class="card-title" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-comment-dots" style="color: var(--primary);"></i> Catatan Evaluasi & Rekomendasi Supervisi
                </h3>
            </div>
            <div class="card-body">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-weight: 700; color: #166534; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-circle-check"></i> 1. Hal-hal yang sudah baik (Apresiasi):
                    </label>
                    <textarea name="notes_good" rows="3" class="form-control" placeholder="Contoh: Interaksi murid sangat dinamis, penggunaan media pembelajaran visual sangat efektif dan kontekstual..."><?= SecurityHelper::escape($session['notes_good'] ?? '') ?></textarea>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-weight: 700; color: #b91c1c; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> 2. Hal-hal yang perlu ditingkatkan / dibina lebih lanjut:
                    </label>
                    <textarea name="notes_improve" rows="3" class="form-control" placeholder="Contoh: Manajemen alokasi waktu pada penutup pelajaran dan asesmen formatif perlu ditingkatkan..."><?= SecurityHelper::escape($session['notes_improve'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Action Footer -->
        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; margin-bottom: 40px;">
            <a href="<?= Auth::baseUrl() ?>/supervisi/penilaian" class="btn btn-outline" style="padding: 10px 20px;">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-size: 14px;">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Hasil Supervisi Mengajar
            </button>
        </div>
    </form>
</div>

<style>
/* Tabel Supervisi */
.table-supervisi {
    width: 100%;
    border-collapse: collapse;
}
.table-supervisi th {
    background: #f8fafc;
    border-bottom: 2px solid var(--border-color);
    padding: 12px 16px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-muted);
}
.table-supervisi td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}
.table-supervisi tr:hover td {
    background: #fdfefe;
}

/* Rating Pills Group */
.rating-pills-group {
    display: flex;
    gap: 6px;
    justify-content: center;
    align-items: center;
}
.rating-pill {
    position: relative;
    cursor: pointer;
    margin: 0;
    display: inline-flex;
}
.rating-pill input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.pill-label {
    display: inline-block;
    padding: 6px 10px;
    font-size: 11.5px;
    font-weight: 600;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: var(--text-muted);
    transition: all 0.15s ease-in-out;
    user-select: none;
    white-space: nowrap;
}
.rating-pill:hover .pill-label {
    border-color: #94a3b8;
    background: #f8fafc;
}

/* Checked States */
.rating-pill input[type="radio"]:checked + .pill-label.pill-green {
    background: #15803d;
    color: #ffffff;
    border-color: #15803d;
    box-shadow: 0 2px 4px rgba(21, 128, 61, 0.25);
}
.rating-pill input[type="radio"]:checked + .pill-label.pill-blue {
    background: #2563eb;
    color: #ffffff;
    border-color: #2563eb;
    box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
}
.rating-pill input[type="radio"]:checked + .pill-label.pill-amber {
    background: #d97706;
    color: #ffffff;
    border-color: #d97706;
    box-shadow: 0 2px 4px rgba(217, 119, 6, 0.25);
}
.rating-pill input[type="radio"]:checked + .pill-label.pill-red {
    background: #dc2626;
    color: #ffffff;
    border-color: #dc2626;
    box-shadow: 0 2px 4px rgba(220, 38, 38, 0.25);
}

/* Disabled State */
.rating-pill.disabled {
    cursor: not-allowed;
    opacity: 0.4;
}

/* Live Score Card */
.live-score-card {
    background: #ffffff;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 12px 20px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updatePillsState() {
        document.querySelectorAll('.rating-pill').forEach(pill => {
            const radio = pill.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                pill.classList.add('active');
            } else {
                pill.classList.remove('active');
            }
        });
    }

    function recalculate() {
        let totA = 0;
        let totB = 0;
        let totC = 0;

        // Check Seksi A
        document.querySelectorAll('.score-calc-a:checked').forEach(el => {
            const sc = parseFloat(el.value);
            const w = parseFloat(el.getAttribute('data-weight'));
            totA += (sc / 2) * w;
        });

        // Check Seksi B
        document.querySelectorAll('.score-calc-b:checked').forEach(el => {
            const sc = parseFloat(el.value);
            const w = parseFloat(el.getAttribute('data-weight'));
            totB += (sc / 3) * w;
        });

        // Check Seksi C & Rule 12 enforcement
        let hasLessThan2 = false;
        for (let i = 1; i <= 11; i++) {
            const checked = document.querySelector('.c-item-' + i + ':checked');
            if (checked && parseInt(checked.value, 10) < 2) {
                hasLessThan2 = true;
            }
        }

        const warnAlert = document.getElementById('rule12Warning');
        const c12_3 = document.getElementById('c_12_3');
        const labelC12_3 = document.getElementById('label_c_12_3');

        if (hasLessThan2) {
            if (warnAlert) warnAlert.style.display = 'flex';
            if (c12_3 && c12_3.checked) {
                const c12_2 = document.getElementById('c_12_2');
                if (c12_2) c12_2.checked = true;
            }
            if (c12_3) c12_3.disabled = true;
            if (labelC12_3) labelC12_3.classList.add('disabled');
        } else {
            if (warnAlert) warnAlert.style.display = 'none';
            if (c12_3) c12_3.disabled = false;
            if (labelC12_3) labelC12_3.classList.remove('disabled');
        }

        document.querySelectorAll('.score-calc-c:checked').forEach(el => {
            const sc = parseFloat(el.value);
            const w = parseFloat(el.getAttribute('data-weight'));
            totC += (sc / 3) * w;
        });

        const grandTotal = Math.round((totA + totB + totC) * 10) / 10;
        const totalBadge = document.getElementById('liveTotalBadge');
        if (totalBadge) totalBadge.textContent = grandTotal.toFixed(1);

        const predBadge = document.getElementById('livePredBadge');
        if (predBadge) {
            if (grandTotal >= 96.0) {
                predBadge.className = 'badge badge-success';
                predBadge.textContent = 'SANGAT BAIK';
            } else if (grandTotal >= 86.0) {
                predBadge.className = 'badge badge-info';
                predBadge.textContent = 'BAIK';
            } else if (grandTotal >= 71.0) {
                predBadge.className = 'badge badge-warning';
                predBadge.textContent = 'CUKUP';
            } else {
                predBadge.className = 'badge badge-danger';
                predBadge.textContent = 'PERLU PENINGKATAN';
            }
        }

        updatePillsState();
    }

    document.querySelectorAll('input[type=radio]').forEach(r => {
        r.addEventListener('change', recalculate);
    });

    recalculate();
});
</script>
