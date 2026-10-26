<?php
// views/master/smtp_settings.php
use Core\SecurityHelper;
use Core\CSRF;

$defaultTemplate = "Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n"
    . "Yth. Bpk/Ibu {nama_ortu},\n"
    . "Orang tua / wali dari ananda {nama_siswa} ({kelas}).\n\n"
    . "Dengan hormat, kami menyampaikan pemberitahuan terkait kewajiban administrasi SPP sekolah ananda sebagai berikut:\n\n"
    . "{rincian_tunggakan}\n"
    . "Total Kewajiban: {total}\n"
    . "Tenggat Waktu: {jatuh_tempo}\n\n"
    . "Pembayaran dapat ditransfer melalui:\n"
    . "{rekening}\n\n"
    . "Demikian pemberitahuan ini kami sampaikan. Atas perhatian dan kerjasamanya kami ucapkan jazakumullahu khairan katsiran.\n\n"
    . "Wassalamu'alaikum Warahmatullahi Wabarakatuh,\n"
    . "Batam, {tanggal}\n"
    . "Kepala Sekolah";
?>
<div class="page-header">
    <div>
        <h1 class="page-title">Pengaturan SMTP & Template Surat Penagihan SPP</h1>
        <div class="page-subtitle">Unit: <strong><?= SecurityHelper::escape($unit['name']) ?></strong> | Digunakan untuk pengiriman tagihan otomatis ke email orang tua.</div>
    </div>
</div>

<div class="grid-cols-2">
    <!-- Form Konfigurasi SMTP -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa-solid fa-server" style="color: var(--primary);"></i> Konfigurasi Server SMTP</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= $baseUrl ?>/settings/smtp/save">
                <?= CSRF::inputField() ?>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" value="<?= SecurityHelper::escape($unit['smtp_host'] ?? 'smtp.gmail.com') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">SMTP Port</label>
                        <input type="number" name="smtp_port" class="form-control" value="<?= $unit['smtp_port'] ?? 587 ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Username / Email Pengirim</label>
                    <input type="email" name="smtp_user" class="form-control" value="<?= SecurityHelper::escape($unit['smtp_user'] ?? '') ?>" required placeholder="unit.sekolah@gmail.com">
                </div>

                <div class="grid-cols-2">
                    <div class="form-group">
                        <label class="form-label">Kata Sandi SMTP (App Password)</label>
                        <input type="password" name="smtp_pass" class="form-control" placeholder="•••••••• (Aman terenkripsi)">
                        <small style="font-size: 11px; color: var(--text-muted);">Biarkan kosong jika tidak ingin mengubah kata sandi.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Enkripsi</label>
                        <select name="smtp_enc" class="form-control">
                            <option value="tls" <?= ($unit['smtp_enc'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Port 587)</option>
                            <option value="ssl" <?= ($unit['smtp_enc'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 16px;">
                    <label class="form-label">Template Pesan Surat Penagihan SPP</label>
                    <textarea name="letter_template" class="form-control" rows="10" style="font-family: monospace; font-size: 12px;"><?= SecurityHelper::escape($unit['letter_template'] ?: $defaultTemplate) ?></textarea>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                        Gunakan tag variabel: <code>{nama_siswa}</code>, <code>{kelas}</code>, <code>{nama_ortu}</code>, <code>{rincian_tunggakan}</code>, <code>{total}</code>, <code>{rekening}</code>, <code>{jatuh_tempo}</code>, <code>{tanggal}</code>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
                    <i class="fa-solid fa-save"></i> Simpan Pengaturan
                </button>
            </form>
        </div>
    </div>

    <!-- Uji Coba Pengiriman Email -->
    <div>
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h3 class="card-title"><i class="fa-solid fa-paper-plane" style="color: var(--accent);"></i> Uji Coba Pengiriman Email SMTP</h3>
            </div>
            <div class="card-body">
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
                    Pastikan pengaturan di sebelah kiri telah disimpan sebelum melakukan uji coba kirim email.
                </p>
                <form method="POST" action="<?= $baseUrl ?>/settings/smtp/test">
                    <?= CSRF::inputField() ?>
                    <div class="form-group">
                        <label class="form-label">Kirim Email Uji Coba Ke:</label>
                        <input type="email" name="test_email" class="form-control" required placeholder="email.anda@gmail.com">
                    </div>
                    <button type="submit" class="btn btn-accent">
                        <i class="fa-solid fa-vial"></i> Kirim Email Uji Coba
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fa-solid fa-file-invoice"></i> Rekening Bank Penampung Unit</h3>
            </div>
            <div class="card-body" style="font-size: 13px;">
                <table style="width: 100%;">
                    <tr><td style="width: 120px; color: var(--text-muted);">Nama Bank:</td><td><strong><?= SecurityHelper::escape($unit['bank_name']) ?></strong></td></tr>
                    <tr><td style="color: var(--text-muted);">No. Rekening:</td><td><strong style="color: #0284c7;"><?= SecurityHelper::escape($unit['bank_account']) ?></strong></td></tr>
                    <tr><td style="color: var(--text-muted);">Atas Nama:</td><td><strong><?= SecurityHelper::escape($unit['bank_holder']) ?></strong></td></tr>
                </table>
                <small style="color: var(--text-muted); display: block; margin-top: 10px;">
                    Rekening ini akan otomatis disisipkan pada tag <code>{rekening}</code> saat surat tagihan dicetak dan dikirimkan.
                </small>
            </div>
        </div>
    </div>
</div>
