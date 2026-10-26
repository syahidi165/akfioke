<?php
// views/survey/ortu.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;
?>

<div class="survey-page-wrapper">
    <?php if ($existing): ?>
        <div class="card" style="text-align: center; padding: 40px 20px; border-top: 4px solid var(--primary); margin-bottom: 24px;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: #dcfce7; color: #15803d; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 16px;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h2 style="font-size: 22px; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">
                Terima Kasih Atas Partisipasi Anda!
            </h2>
            <p style="color: var(--text-muted); font-size: 14px; max-width: 540px; margin: 0 auto 24px auto; line-height: 1.6;">
                Anda telah mengisi Kuesioner Kepuasan Layanan Sekolah untuk Tahun Ajaran ini. Masukan Anda sangat berharga untuk kemajuan pendidikan anak-anak kita.
            </p>
            
            <div style="display: inline-block; padding: 16px 28px; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 24px;">
                <div style="font-size: 12px; text-transform: uppercase; font-weight: 600; color: var(--text-muted); letter-spacing: 0.5px;">
                    Indeks Kepuasan yang Anda Berikan
                </div>
                <div style="font-size: 36px; font-weight: 800; color: var(--primary); margin: 4px 0;">
                    <?= (float)$existing['score_pct'] ?>%
                </div>
                <div style="font-size: 12px; color: var(--text-muted);">
                    Total Skor: <strong><?= (int)$existing['total_score'] ?></strong> / <?= (int)$existing['max_score'] ?>
                </div>
            </div>

            <?php if (!empty($existing['suggestions'])): ?>
                <div style="max-width: 560px; margin: 0 auto; text-align: left; background: #ffffff; border: 1px solid var(--border-color); border-radius: 10px; padding: 16px;">
                    <div style="font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px;">
                        <i class="fa-solid fa-comment-dots" style="color: var(--primary); margin-right: 4px;"></i> Saran / Masukan yang Anda kirimkan:
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted); font-style: italic; line-height: 1.5;">
                        "<?= nl2br(SecurityHelper::escape($existing['suggestions'])) ?>"
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card" style="margin-bottom: 24px; border-left: 5px solid var(--primary); background: #f0fdf4;">
            <div class="card-body" style="padding: 18px 20px;">
                <h2 style="font-size: 18px; font-weight: 700; color: #166534; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-clipboard-list"></i> Kuesioner Evaluasi & Kepuasan Layanan Sekolah
                </h2>
                <p style="font-size: 13px; color: #15803d; margin: 0 0 10px 0; line-height: 1.5;">
                    Bapak/Ibu Orang Tua Santri yang kami muliakan, mohon kesediaannya meluangkan waktu sejenak untuk memberikan penilaian objektif demi peningkatan mutu pelayanan pendidikan Yayasan Islam Al Kahfi Batam.
                </p>
                <div style="display: flex; gap: 8px; flex-wrap: wrap; font-size: 11.5px;">
                    <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;"><strong>1:</strong> Sangat Tidak Puas</span>
                    <span class="badge" style="background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa;"><strong>2:</strong> Tidak Puas</span>
                    <span class="badge" style="background: #fef9c3; color: #854d0e; border: 1px solid #fef08a;"><strong>3:</strong> Cukup Puas</span>
                    <span class="badge" style="background: #e0f2fe; color: #075985; border: 1px solid #bae6fd;"><strong>4:</strong> Puas</span>
                    <span class="badge" style="background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;"><strong>5:</strong> Sangat Puas</span>
                </div>
            </div>
        </div>

        <form action="<?= Auth::baseUrl() ?>/survey/ortu/submit" method="POST">
            <?= CSRF::field() ?>

            <?php foreach ($questions as $sectionTitle => $items): ?>
                <div class="card" style="margin-bottom: 24px;">
                    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color); padding: 14px 20px;">
                        <h3 class="card-title" style="font-size: 14.5px; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-folder-open" style="color: var(--primary);"></i>
                            <?= SecurityHelper::escape($sectionTitle) ?>
                        </h3>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <div class="table-responsive">
                            <table class="table-survey">
                                <thead>
                                    <tr>
                                        <th style="width: 45px; text-align: center;">No</th>
                                        <th>Pernyataan Aspek Layanan</th>
                                        <th style="width: 280px; text-align: center;">Skala Penilaian (1 - 5)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $qNum => $qText): ?>
                                        <tr>
                                            <td style="text-align: center; font-weight: 700; color: var(--text-muted);"><?= $qNum ?></td>
                                            <td style="line-height: 1.45; font-size: 13.5px; color: var(--text-main);">
                                                <?= SecurityHelper::escape($qText) ?>
                                            </td>
                                            <td>
                                                <div class="likert-group">
                                                    <?php 
                                                    $labels = [1 => 'STP', 2 => 'TP', 3 => 'CP', 4 => 'P', 5 => 'SP'];
                                                    $tooltips = [1 => '1 - Sangat Tidak Puas', 2 => '2 - Tidak Puas', 3 => '3 - Cukup Puas', 4 => '4 - Puas', 5 => '5 - Sangat Puas'];
                                                    for ($val = 1; $val <= 5; $val++): 
                                                    ?>
                                                        <label class="likert-pill" title="<?= $tooltips[$val] ?>">
                                                            <input type="radio" name="ans[<?= $qNum ?>]" value="<?= $val ?>" <?= $val === 4 ? 'checked' : '' ?> required>
                                                            <span class="likert-num likert-val-<?= $val ?>"><?= $val ?></span>
                                                        </label>
                                                    <?php endfor; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Saran & Masukan Bebas -->
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header">
                    <h3 class="card-title" style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-comment-dots" style="color: var(--primary);"></i> Saran, Masukan, dan Harapan untuk Sekolah
                    </h3>
                </div>
                <div class="card-body">
                    <textarea name="suggestions" rows="4" class="form-control" placeholder="Tuliskan saran, apresiasi, atau kritik konstruktif untuk kemajuan pendidikan ananda dan sekolah kami..."></textarea>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-bottom: 40px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 15px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 10px rgba(5, 150, 105, 0.25);">
                    <i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Kirimkan Hasil Kuesioner
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<style>
/* Table Survey */
.table-survey {
    width: 100%;
    border-collapse: collapse;
}
.table-survey th {
    background: #f8fafc;
    border-bottom: 2px solid var(--border-color);
    padding: 12px 16px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-muted);
}
.table-survey td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}
.table-survey tr:hover td {
    background: #fcfdfe;
}

/* Likert 1-5 Group */
.likert-group {
    display: flex;
    justify-content: center;
    gap: 8px;
    align-items: center;
}
.likert-pill {
    position: relative;
    cursor: pointer;
    margin: 0;
    display: inline-flex;
}
.likert-pill input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.likert-num {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    font-weight: 700;
    font-size: 13px;
    color: var(--text-muted);
    transition: all 0.15s ease-in-out;
    user-select: none;
}
.likert-pill:hover .likert-num {
    border-color: #94a3b8;
    background: #f8fafc;
    transform: scale(1.05);
}

/* Checked Colors */
.likert-pill input[type="radio"]:checked + .likert-val-1 {
    background: #dc2626;
    color: #ffffff;
    border-color: #dc2626;
    box-shadow: 0 2px 6px rgba(220, 38, 38, 0.35);
}
.likert-pill input[type="radio"]:checked + .likert-val-2 {
    background: #ea580c;
    color: #ffffff;
    border-color: #ea580c;
    box-shadow: 0 2px 6px rgba(234, 88, 12, 0.35);
}
.likert-pill input[type="radio"]:checked + .likert-val-3 {
    background: #ca8a04;
    color: #ffffff;
    border-color: #ca8a04;
    box-shadow: 0 2px 6px rgba(202, 138, 4, 0.35);
}
.likert-pill input[type="radio"]:checked + .likert-val-4 {
    background: #0284c7;
    color: #ffffff;
    border-color: #0284c7;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.35);
}
.likert-pill input[type="radio"]:checked + .likert-val-5 {
    background: #15803d;
    color: #ffffff;
    border-color: #15803d;
    box-shadow: 0 2px 6px rgba(21, 128, 61, 0.35);
}
</style>
