<?php
// views/survey/guru_kepsek.php
use Core\SecurityHelper;
use Core\CSRF;
use Core\Auth;
?>

<div class="survey-page-wrapper">
    <div class="card" style="margin-bottom: 24px; border-left: 5px solid var(--primary); background: #f0fdf4;">
        <div class="card-body" style="padding: 18px 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h2 style="font-size: 18px; font-weight: 700; color: #166534; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-user-tie"></i> Survei Kepemimpinan Kepala Sekolah (Oleh Dewan Guru)
                    </h2>
                    <p style="font-size: 13px; color: #15803d; margin: 0; line-height: 1.5;">
                        Penilaian objektif dan rahasia dewan guru mengenai kinerja kepemimpinan Kepala Satuan Pendidikan untuk evaluasi berkala dan AKFI Kepala Sekolah.
                    </p>
                </div>
                <div>
                    <span class="badge badge-info" style="font-size: 12px; padding: 6px 12px;">
                        <i class="fa-solid fa-user-secret" style="margin-right: 4px;"></i> Penilaian Anonim / Rahasia
                    </span>
                </div>
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap; font-size: 11.5px; margin-top: 12px; padding-top: 10px; border-top: 1px dashed #bbf7d0;">
                <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;"><strong>1:</strong> Sangat Kurang</span>
                <span class="badge" style="background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa;"><strong>2:</strong> Kurang</span>
                <span class="badge" style="background: #fef9c3; color: #854d0e; border: 1px solid #fef08a;"><strong>3:</strong> Cukup</span>
                <span class="badge" style="background: #e0f2fe; color: #075985; border: 1px solid #bae6fd;"><strong>4:</strong> Baik</span>
                <span class="badge" style="background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;"><strong>5:</strong> Sangat Baik</span>
            </div>
        </div>
    </div>

    <form action="<?= Auth::baseUrl() ?>/survey/guru-kepsek/submit" method="POST">
        <?= CSRF::field() ?>

        <?php foreach ($questions as $secTitle => $items): ?>
            <div class="card" style="margin-bottom: 24px;">
                <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color); padding: 14px 20px;">
                    <h3 class="card-title" style="font-size: 14.5px; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-folder-open" style="color: var(--primary);"></i>
                        <?= SecurityHelper::escape($secTitle) ?>
                    </h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="table-survey">
                            <thead>
                                <tr>
                                    <th style="width: 45px; text-align: center;">No</th>
                                    <th>Pernyataan Indikator Kepemimpinan</th>
                                    <th style="width: 280px; text-align: center;">Skala Skor (1 - 5)</th>
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
                                                $tooltips = [1 => '1 - Sangat Kurang', 2 => '2 - Kurang', 3 => '3 - Cukup', 4 => '4 - Baik', 5 => '5 - Sangat Baik'];
                                                for ($val = 1; $val <= 5; $val++): 
                                                ?>
                                                    <label class="likert-pill" title="<?= $tooltips[$val] ?>">
                                                        <input type="radio" name="ans[<?= $qNum ?>]" id="gq_<?= $qNum ?>_<?= $val ?>" value="<?= $val ?>" <?= $val === 4 ? 'checked' : '' ?> required>
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

        <!-- Catatan / Masukan Konstruktif -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h3 class="card-title" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-comment-dots" style="color: var(--primary);"></i> Catatan, Masukan, atau Harapan untuk Kepala Sekolah
                </h3>
            </div>
            <div class="card-body">
                <textarea name="suggestions" rows="4" class="form-control" placeholder="Tuliskan apresiasi kepemimpinan, saran perbaikan koordinasi, atau kritik konstruktif untuk peningkatan mutu kepemimpinan sekolah..."></textarea>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-bottom: 40px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 15px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 10px rgba(5, 150, 105, 0.25);">
                <i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Kirimkan Penilaian Guru
            </button>
        </div>
    </form>
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
