<?php
// database/migrate_bills_email_tracking.php
// Migrasi idempoten: Menambahkan pelacakan khusus pengiriman email FR 8001C (Kepala Sekolah & Deputi)
// agar wali murid yang sudah dikirimkan email memiliki penanda jelas dan mencegah penagihan bertubi-tubi.

require_once __DIR__ . '/../core/Database.php';

use Core\Database;

Database::connect();

$columnsToAdd = [
    'kepsek_email_8001c_sent_at' => "DATETIME NULL DEFAULT NULL",
    'kepsek_email_8001c_sent_to' => "VARCHAR(191) NULL DEFAULT NULL",
    'kepsek_email_8001c_count'   => "INT NOT NULL DEFAULT 0",
    'deputi_email_8001c_sent_at' => "DATETIME NULL DEFAULT NULL",
    'deputi_email_8001c_sent_to' => "VARCHAR(191) NULL DEFAULT NULL",
    'deputi_email_8001c_count'   => "INT NOT NULL DEFAULT 0",
];

foreach ($columnsToAdd as $col => $def) {
    $exists = Database::fetch(
        "SELECT COUNT(*) as c FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bills' AND COLUMN_NAME = ?",
        [$col]
    );
    if ((int)($exists['c'] ?? 0) === 0) {
        Database::execute("ALTER TABLE `bills` ADD COLUMN `{$col}` {$def}");
        echo "[OK] Kolom bills.{$col} ditambahkan.\n";
    } else {
        echo "[SKIP] Kolom bills.{$col} sudah ada.\n";
    }
}

// Backfill aman riwayat pengiriman email FR 8001C dari audit_logs bila sebelumnya sudah pernah terkirim
try {
    $logs = Database::fetchAll(
        "SELECT action, record_id, new_values, created_at
         FROM audit_logs
         WHERE table_name = 'bills'
           AND action IN ('SEND_EMAIL_KEPSEK_FR8001C', 'SEND_EMAIL_DEPUTI_FR8001C')
           AND new_values LIKE '%\"email_sent\":true%'
         ORDER BY id ASC"
    );
    $kepsekStats = [];
    $deputiStats = [];
    foreach ($logs as $lg) {
        $billId = (int)($lg['record_id'] ?? 0);
        if ($billId <= 0) {
            continue;
        }
        $payload = json_decode((string)($lg['new_values'] ?? ''), true);
        if (!is_array($payload) || empty($payload['email_sent'])) {
            continue;
        }
        $emailTo = trim((string)($payload['email'] ?? ''));
        $sentAt = (string)($lg['created_at'] ?? '');
        if ($lg['action'] === 'SEND_EMAIL_KEPSEK_FR8001C') {
            if (!isset($kepsekStats[$billId])) {
                $kepsekStats[$billId] = ['count' => 0, 'last_at' => $sentAt, 'last_to' => $emailTo];
            }
            $kepsekStats[$billId]['count']++;
            $kepsekStats[$billId]['last_at'] = $sentAt;
            $kepsekStats[$billId]['last_to'] = $emailTo;
        } elseif ($lg['action'] === 'SEND_EMAIL_DEPUTI_FR8001C') {
            if (!isset($deputiStats[$billId])) {
                $deputiStats[$billId] = ['count' => 0, 'last_at' => $sentAt, 'last_to' => $emailTo];
            }
            $deputiStats[$billId]['count']++;
            $deputiStats[$billId]['last_at'] = $sentAt;
            $deputiStats[$billId]['last_to'] = $emailTo;
        }
    }

    $ksUpdated = 0;
    foreach ($kepsekStats as $bId => $st) {
        Database::execute(
            "UPDATE bills
             SET kepsek_email_8001c_sent_at = ?,
                 kepsek_email_8001c_sent_to = ?,
                 kepsek_email_8001c_count = ?
             WHERE id = ? AND kepsek_email_8001c_sent_at IS NULL",
            [$st['last_at'], $st['last_to'], $st['count'], $bId]
        );
        $ksUpdated++;
    }

    $dpUpdated = 0;
    foreach ($deputiStats as $bId => $st) {
        Database::execute(
            "UPDATE bills
             SET deputi_email_8001c_sent_at = ?,
                 deputi_email_8001c_sent_to = ?,
                 deputi_email_8001c_count = ?
             WHERE id = ? AND deputi_email_8001c_sent_at IS NULL",
            [$st['last_at'], $st['last_to'], $st['count'], $bId]
        );
        $dpUpdated++;
    }

    echo "[OK] Backfill riwayat email FR 8001C dari audit_logs selesai (KS: {$ksUpdated}, Deputi: {$dpUpdated}).\n";
} catch (\Throwable $e) {
    echo "[SKIP] Backfill audit_logs FR 8001C: " . $e->getMessage() . "\n";
}
