<?php
// views/master/mushaf.php
use Core\SecurityHelper;
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Master Mushaf Al-Qur'an Madinah</h1>
        <div class="page-subtitle">Standar Mushaf Madinah (King Fahd Complex) 604 halaman, 15 baris per halaman untuk kalkulasi otomatis hafalan santri & guru.</div>
    </div>
</div>

<div class="grid-cols-3" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-book-quran"></i></div>
        <div>
            <div class="stat-value">114 Surah</div>
            <div class="stat-label">Total Surah Al-Qur'an</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon emerald"><i class="fa-solid fa-file-lines"></i></div>
        <div>
            <div class="stat-value">604 Halaman</div>
            <div class="stat-label">Mushaf Madinah Standar</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="fa-solid fa-calculator"></i></div>
        <div>
            <div class="stat-value">15 Baris / Hal</div>
            <div class="stat-label">1 Juz ≈ 300 Baris (20 Hal) | ½ Juz ≈ 150 Baris</div>
        </div>
    </div>
</div>

<!-- Kalkulator Interaktif Madinah Lines -->
<div class="card" style="margin-bottom: 24px; border: 2px solid #0d9488;">
    <div class="card-header" style="background: #f0fdfa;">
        <h3 class="card-title" style="color: #0f766e;"><i class="fa-solid fa-calculator"></i> Uji Coba Kalkulator Baris Al-Qur'an Madinah</h3>
    </div>
    <div class="card-body">
        <div class="grid-cols-4">
            <div class="form-group">
                <label class="form-label">Halaman Awal</label>
                <input type="number" id="calc_page_start" class="form-control" min="1" max="604" value="582" onchange="runCalc()">
            </div>
            <div class="form-group">
                <label class="form-label">Halaman Akhir</label>
                <input type="number" id="calc_page_end" class="form-control" min="1" max="604" value="604" onchange="runCalc()">
            </div>
            <div class="form-group">
                <label class="form-label">Hasil Perhitungan Baris</label>
                <div id="calc_result_lines" style="font-size: 24px; font-weight: 700; color: #0f766e; padding: 4px 0;">345 Baris</div>
            </div>
            <div class="form-group">
                <label class="form-label">Kategori Capaian</label>
                <div id="calc_result_category" style="padding: 6px 0;">
                    <span class="badge badge-success" style="font-size: 13px;">&ge; 1 Juz (Tuntas)</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-table-list"></i> Daftar 114 Surah & Rentang Halaman</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Surah (Arab)</th>
                        <th>Nama Surah (Latin)</th>
                        <th>Jumlah Ayat</th>
                        <th>Halaman Awal</th>
                        <th>Halaman Akhir</th>
                        <th>Estimasi Baris</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($surahs as $s): ?>
                        <?php $lines = ($s['end_page'] - $s['start_page'] + 1) * 15; ?>
                        <tr>
                            <td><?= $s['surah_number'] ?></td>
                            <td style="font-size: 18px; font-family: 'Amiri', serif;"><?= SecurityHelper::escape($s['name_ar']) ?></td>
                            <td><strong><?= SecurityHelper::escape($s['name_id']) ?></strong></td>
                            <td><?= $s['verses_count'] ?> ayat</td>
                            <td><span class="badge badge-secondary">Hal. <?= $s['start_page'] ?></span></td>
                            <td><span class="badge badge-secondary">Hal. <?= $s['end_page'] ?></span></td>
                            <td><strong><?= $lines ?> baris</strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function runCalc() {
    const p1 = parseInt(document.getElementById('calc_page_start').value) || 1;
    const p2 = parseInt(document.getElementById('calc_page_end').value) || 1;
    const pages = Math.max(1, (p2 - p1 + 1));
    const lines = pages * 15;

    document.getElementById('calc_result_lines').innerText = lines + ' Baris';

    let catHtml = '';
    if (lines >= 300) {
        catHtml = '<span class="badge badge-success" style="font-size: 13px;">&ge; 1 Juz (Tuntas)</span>';
    } else if (lines >= 150) {
        catHtml = '<span class="badge badge-info" style="font-size: 13px;">&ge; ½ Juz (Target TK)</span>';
    } else {
        catHtml = '<span class="badge badge-warning" style="font-size: 13px;">' + lines + ' Baris (Belum 1/2 Juz)</span>';
    }
    document.getElementById('calc_result_category').innerHTML = catHtml;
}
</script>
