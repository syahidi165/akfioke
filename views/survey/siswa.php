<div class="card mb-4 bg-teal-50 border-teal-200">
    <div class="card-body">
        <h5 class="font-bold text-teal-900 mb-2">
            <i class="fa fa-smile me-2"></i> Kuesioner Pengalaman Belajar Santri
        </h5>
        <p class="text-teal-800 small mb-0">
            Adik-adik santri yang sholih dan sholihah, yuk bantu sekolah memberikan kesan dan perasaan belajarmu selama bersekolah di Al Kahfi Batam!
        </p>
    </div>
</div>

<?php
$savedAns = !empty($existing['answers_json']) ? (json_decode($existing['answers_json'], true) ?: []) : [];
$ans1 = (int)($savedAns[1] ?? 4);
$ans2 = (int)($savedAns[2] ?? 4);
$favText = $existing['suggestions'] ?? '';
?>
<?php if (!empty($existing)): ?>
<div class="alert alert-success mb-4">
    <i class="fa fa-check-circle me-1"></i> Kamu sudah mengirimkan jawaban kuesioner pada tahun ajaran ini (Skor Kepuasan: <strong><?= number_format((float)$existing['score_pct'], 1) ?>%</strong>). Kamu tetap dapat memperbarui jawabanmu di bawah ini.
</div>
<?php endif; ?>

<form action="<?= \Core\Auth::baseUrl() ?>/survey/siswa/submit" method="POST">
    <?= \Core\CSRF::field() ?>

    <div class="card mb-4">
        <div class="card-header bg-slate-100 font-semibold">
            Pertanyaan Pengalaman Belajar Santri
        </div>
        <div class="card-body">
            <div class="mb-4 pb-3 border-bottom">
                <label class="form-label font-bold">1. Bagaimana perasaanmu saat belajar di kelas bersama Bapak/Ibu guru?</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="ans[1]" id="s1_4" value="4" <?= $ans1 === 4 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="s1_4">😊 Sangat Senang & Semangat</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="ans[1]" id="s1_3" value="3" <?= $ans1 === 3 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="s1_3">🙂 Cukup Senang</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="ans[1]" id="s1_2" value="2" <?= $ans1 === 2 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="s1_2">😐 Kadang Bosan</label>
                    </div>
                </div>
            </div>

            <div class="mb-4 pb-3 border-bottom">
                <label class="form-label font-bold">2. Apakah Bapak/Ibu guru selalu ramah dan sabar membimbing hafalan tahfidz?</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="ans[2]" id="s2_4" value="4" <?= $ans2 === 4 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="s2_4">🌟 Sangat Ramah & Sabar</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="ans[2]" id="s2_3" value="3" <?= $ans2 === 3 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="s2_3">👍 Cukup Sabar</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="ans[2]" id="s2_2" value="2" <?= $ans2 === 2 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="s2_2">👎 Kadang Galak</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label font-bold">3. Apa hal yang paling kamu sukai di sekolah Al Kahfi?</label>
                <input type="text" name="favorite_thing" class="form-control" value="<?= \Core\SecurityHelper::escape($favText) ?>" placeholder="Contoh: Belajar tahfidz, bermain bersama teman, fasilitas olahraga...">
            </div>
        </div>
    </div>

    <div class="text-end mb-4">
        <button type="submit" class="btn btn-emerald px-4">
            <i class="fa fa-paper-plane me-1"></i> Kirim Jawaban Santri
        </button>
    </div>
</form>
