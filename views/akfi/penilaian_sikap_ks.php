<?php
// views/akfi/penilaian_sikap_ks.php
use Core\SecurityHelper;
use Core\CSRF;
?>
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Penilaian Sikap & Kepemimpinan Kepala Sekolah</h1>
        <div class="page-subtitle">Instrumen rubrik evaluasi keteladanan akhlaq, kepemimpinan Islami, dan loyalitas Kepala Satuan Pendidikan oleh Deputi Direktur Pendidikan.</div>
    </div>
    <a href="<?= $baseUrl ?>/akfi/kanit-preview" class="btn btn-outline">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Pratinjau
    </a>
</div>

<!-- Selector Kepala Sekolah -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px 20px;">
        <label style="font-weight: 600; font-size: 13px; margin-bottom: 8px; display: block;">Pilih Kepala Satuan Pendidikan yang Dinilai:</label>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <?php foreach ($allKs as $ks): 
                $isActive = $selectedAssessment && $selectedAssessment['employee_id'] == $ks['employee_id'];
            ?>
                <a href="<?= $baseUrl ?>/akfi/penilaian-sikap-ks?emp_id=<?= $ks['employee_id'] ?>" 
                   class="btn <?= $isActive ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 13px;">
                    <i class="fa-solid fa-user-tie"></i> 
                    <?= SecurityHelper::escape($ks['unit_code']) ?> - <?= SecurityHelper::escape($ks['nama']) ?>
                    <?php if (!empty($ks['sikap_score']) && (float)$ks['sikap_score'] > 0): ?>
                        <span class="badge badge-success" style="font-size: 10px; margin-left: 4px;">✓ <?= number_format((float)$ks['sikap_score'], 1) ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if (!$selectedAssessment): ?>
    <div class="card">
        <div class="card-body text-center" style="padding: 40px 20px;">
            <i class="fa-solid fa-user-xmark" style="font-size: 40px; color: #94a3b8; margin-bottom: 12px;"></i>
            <h4>Data Penilaian Kepala Sekolah Tidak Ditemukan</h4>
            <p class="text-muted">Silakan pilih Kepala Satuan Pendidikan dari daftar di atas atau buka menu Pratinjau AKFI KS.</p>
            <a href="<?= $baseUrl ?>/akfi/kanit-preview" class="btn btn-primary" style="margin-top: 10px;">
                Buka Pratinjau AKFI KS
            </a>
        </div>
    </div>
<?php else: ?>
    <?php
        $rubricDefs = [
            1 => [
                'title' => 'Keteladanan Ibadah, Akhlaq & Busana Syar\'i',
                'desc'  => 'Konsistensi sholat berjamaah tepat waktu, keteladanan akhlaqul karimah bagi seluruh warga sekolah, adab berbicara, serta kedisiplinan berbusana rapi syar\'i sesuai standar Yayasan Islam Al Kahfi.',
                'bobot' => '20%'
            ],
            2 => [
                'title' => 'Kepemimpinan & Komunikasi Islami',
                'desc'  => 'Kemampuan memimpin musyawarah dengan bijak (hikmah), membina kekompakan tim pendidik & tenaga kependidikan, serta komunikasi santun dan menyejukkan kepada guru, karyawan, dan orang tua santri.',
                'bobot' => '20%'
            ],
            3 => [
                'title' => 'Loyalitas & Komitmen terhadap Visi Misi Yayasan',
                'desc'  => 'Ketaatan penuh terhadap arahan dan kebijakan strategis Yayasan Islam Al Kahfi Batam, keaktifan dalam kajian pembinaan yayasan, serta komitmen tinggi menjaga marwah lembaga di tengah masyarakat.',
                'bobot' => '20%'
            ],
            4 => [
                'title' => 'Tanggung Jawab & Integritas Manajerial',
                'desc'  => 'Kejujuran dan transparansi dalam pengelolaan program satuan pendidikan, ketuntasan 18 berkas administrasi kepala sekolah, serta akuntabilitas pertanggungjawaban operasional unit secara tuntas.',
                'bobot' => '20%'
            ],
            5 => [
                'title' => 'Responsivitas & Keterbukaan terhadap Pembinaan Yayasan',
                'desc'  => 'Kecepatan dan kesungguhan dalam menindaklanjuti rekomendasi supervisi/evaluasi Deputi & Direktur Pendidikan, keterbukaan menerima kritik evaluasi, serta inisiatif perbaikan mutu berkelanjutan.',
                'bobot' => '20%'
            ],
        ];

        $bobotSikap = (float)($selectedAssessment['bobot_sikap'] ?? 30.00);
    ?>

    <div class="row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        <!-- Form Penilaian -->
        <form method="POST" action="<?= $baseUrl ?>/akfi/save-sikap-ks" id="formSikapKs">
            <?= CSRF::inputField() ?>
            <input type="hidden" name="assessment_id" value="<?= $selectedAssessment['id'] ?>">

            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="background: #fff; border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 600; margin: 0;">5 Butir Rubrik Sikap & Kepemimpinan Islami</h3>
                            <div style="font-size: 12px; color: var(--text-muted);">Skala 1 (Sangat Kurang) s/d 5 (Amat Baik / Mumtaz) — Bobot Total: <?= $bobotSikap ?>%</div>
                        </div>
                        <span class="badge badge-primary" style="font-size: 12px;">Bobot <?= $bobotSikap ?>%</span>
                    </div>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <?php foreach ($rubricDefs as $idx => $r): 
                        $curVal = isset($savedRubrics[$idx]) ? (int)$savedRubrics[$idx] : 5;
                    ?>
                        <div style="padding: 16px; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 6px;">
                                <div style="font-weight: 600; font-size: 14px; color: var(--primary);">
                                    <?= $idx ?>. <?= SecurityHelper::escape($r['title']) ?>
                                </div>
                                <span class="badge badge-info" style="font-size: 10px;">Bobot <?= $r['bobot'] ?></span>
                            </div>
                            <p style="font-size: 12px; color: #475569; margin-bottom: 12px; line-height: 1.5;">
                                <?= SecurityHelper::escape($r['desc']) ?>
                            </p>
                            
                            <!-- Radio Scale 1 - 5 -->
                            <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px;">
                                <?php 
                                    $scaleLabels = [
                                        1 => '1 - Kurang Sekali',
                                        2 => '2 - Kurang',
                                        3 => '3 - Cukup',
                                        4 => '4 - Baik',
                                        5 => '5 - Amat Baik'
                                    ];
                                    foreach ($scaleLabels as $val => $lbl):
                                ?>
                                    <label style="display: block; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 6px; text-align: center; cursor: pointer; background: <?= $curVal == $val ? '#e0f2fe' : '#fff' ?>; transition: all 0.2s;" class="scale-option">
                                        <input type="radio" name="sikap[<?= $idx ?>]" value="<?= $val ?>" <?= $curVal == $val ? 'checked' : '' ?> style="margin-bottom: 4px;" class="sikap-radio" onchange="calcSikapTotal()">
                                        <div style="font-size: 11px; font-weight: 600;"><?= $lbl ?></div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Catatan Pembinaan -->
                    <div style="margin-top: 20px;">
                        <label style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Catatan / Rekomendasi Pembinaan Atasan (Deputi Direktur):</label>
                        <textarea name="superior_notes" class="form-control" rows="4" placeholder="Tuliskan apresiasi, catatan pembinaan, atau rekomendasi perbaikan kepemimpinan untuk Kepala Sekolah ini..."><?= htmlspecialchars($selectedAssessment['superior_notes'] ?? '') ?></textarea>
                    </div>

                    <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 8px;">
                        <a href="<?= $baseUrl ?>/akfi/kanit-preview" class="btn btn-outline">Batal</a>
                        <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Penilaian Sikap KS
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Sidebar Ringkasan & Profil KS -->
        <div>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header" style="background: #fff; border-bottom: 1px solid var(--border-color); padding: 14px 16px;">
                    <h4 style="font-size: 14px; font-weight: 600; margin: 0;">Profil Kepala Satuan Pendidikan</h4>
                </div>
                <div class="card-body" style="padding: 16px;">
                    <div style="text-align: center; margin-bottom: 14px;">
                        <div style="width: 56px; height: 56px; background: rgba(15, 118, 110, 0.1); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 8px auto;">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <h3 style="font-size: 15px; font-weight: 700; margin: 0;"><?= SecurityHelper::escape($selectedAssessment['nama']) ?></h3>
                        <div style="font-size: 12px; color: var(--text-muted);">NIPY: <?= SecurityHelper::escape($selectedAssessment['nipy']) ?></div>
                        <span class="badge badge-info" style="margin-top: 4px;"><?= SecurityHelper::escape($selectedAssessment['unit_name']) ?></span>
                    </div>

                    <table class="table" style="font-size: 12px; margin: 0;">
                        <tr>
                            <td class="text-muted" width="50%">Posisi AKFI</td>
                            <td><strong><?= SecurityHelper::escape($selectedAssessment['position_name']) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Skor Kinerja Saat Ini</td>
                            <td><strong><?= number_format((float)$selectedAssessment['kinerja_score'], 1) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Skor Absensi Saat Ini</td>
                            <td><strong><?= number_format((float)$selectedAssessment['absensi_score'], 1) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Skor AKFI</td>
                            <td><strong style="color: var(--primary); font-size: 14px;"><?= number_format((float)$selectedAssessment['total_score'], 2) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Predikat Saat Ini</td>
                            <td>
                                <?php if (!empty($selectedAssessment['letter_grade'])): ?>
                                    <span class="badge badge-success"><?= $selectedAssessment['letter_grade'] ?></span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Kalkulasi Realtime -->
            <div class="card" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                <div class="card-body" style="padding: 16px;">
                    <div style="font-weight: 700; color: #166534; font-size: 14px; margin-bottom: 8px;">
                        <i class="fa-solid fa-calculator"></i> Simulasi Skor Sikap
                    </div>
                    <div style="font-size: 12px; color: #15803d; line-height: 1.6;">
                        Total Poin Rubrik: <strong id="sumPoints">25</strong> / 25<br>
                        Persentase Capaian: <strong id="pctCapaian">100%</strong><br>
                        <div style="font-size: 14px; font-weight: 800; color: #166534; margin-top: 6px; border-top: 1px dashed #86efac; padding-top: 6px;">
                            Skor Sikap Masuk AKFI: <span id="finalSikapScore"><?= number_format($bobotSikap, 2) ?></span> / <?= $bobotSikap ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function calcSikapTotal() {
    let radios = document.querySelectorAll('.sikap-radio:checked');
    let sum = 0;
    radios.forEach(function(r) {
        sum += parseInt(r.value, 10);
    });
    let max = 25; // 5 items * 5
    let bobot = <?= $bobotSikap ?>;
    let score = ((sum / max) * bobot).toFixed(2);
    let pct = ((sum / max) * 100).toFixed(1);

    document.getElementById('sumPoints').textContent = sum;
    document.getElementById('pctCapaian').textContent = pct + '%';
    document.getElementById('finalSikapScore').textContent = score;

    // highlight selected labels
    document.querySelectorAll('.scale-option').forEach(function(el) {
        let inp = el.querySelector('input');
        if (inp && inp.checked) {
            el.style.background = '#e0f2fe';
            el.style.borderColor = '#0284c7';
        } else {
            el.style.background = '#fff';
            el.style.borderColor = '#cbd5e1';
        }
    });
}
document.addEventListener('DOMContentLoaded', calcSikapTotal);
</script>
