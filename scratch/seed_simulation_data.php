<?php
require 'core/Database.php';
$db = Core\Database::connect();

echo "=== SEEDING SIMULATION DATA ===\n";

// 1. Update employees tingkat_pendidikan & gender
$eduDist = ['S1', 'S1', 'S1', 'S2', 'S1', 'S1', 'D3', 'S2', 'S1', 'SMA/SMK', 'S1', 'S2'];
$genderDist = ['L', 'P', 'P', 'L', 'P', 'L', 'P', 'P', 'L', 'P'];
$employees = $db->query("SELECT id, level_posisi, unit_id FROM employees WHERE is_deleted = 0")->fetchAll(PDO::FETCH_ASSOC);

foreach ($employees as $idx => $emp) {
    $pos = strtolower($emp['level_posisi']);
    if (strpos($pos, 'kepala') !== false || strpos($pos, 'direktur') !== false || strpos($pos, 'deputi') !== false) {
        $edu = 'S2';
    } elseif (strpos($pos, 'security') !== false || strpos($pos, 'cleaning') !== false || strpos($pos, 'cs') !== false) {
        $edu = 'SMA/SMK';
    } elseif (strpos($pos, 'tata usaha') !== false || strpos($pos, 'tu') !== false) {
        $edu = ($idx % 2 === 0) ? 'D3' : 'S1';
    } else {
        $edu = $eduDist[$idx % count($eduDist)];
    }
    $gen = $genderDist[$idx % count($genderDist)];
    $db->prepare("UPDATE employees SET tingkat_pendidikan = ?, gender = ? WHERE id = ?")->execute([$edu, $gen, $emp['id']]);
}
echo "Updated employees tingkat_pendidikan and gender.\n";

// Make sure we have enough employees per unit if needed
// Let's check employee counts per unit
$unitIds = [1, 2, 3, 4, 5, 16];

// Add extra students for SD (unit 3) if fewer than 30
$sdStudentCount = (int)$db->query("SELECT COUNT(*) FROM students WHERE unit_id = 3 AND status = 'Aktif'")->fetchColumn();
if ($sdStudentCount < 25) {
    $names = [
        ['Muhammad Farhan', 'L', '0081234567'],
        ['Aisyah Humaira', 'P', '0081234568'],
        ['Zaid bin Tsabit', 'L', '0081234569'],
        ['Fathimah Az-Zahra', 'P', '0081234570'],
        ['Umar Al-Faruq', 'L', '0081234571'],
        ['Maryam Khansa', 'P', '0081234572'],
        ['Bilal Ibnu Rabah', 'L', '0081234573'],
        ['Khadijah Al-Kubra', 'P', '0081234574'],
        ['Ali Zainal Abidin', 'L', '0081234575'],
        ['Salma Nabila', 'P', '0081234576'],
        ['Ibrahim Khalilullah', 'L', '0081234577'],
        ['Hafshah binti Umar', 'P', '0081234578'],
        ['Hamzah Asadullah', 'L', '0081234579'],
        ['Safiyyah binti Abdul', 'P', '0081234580'],
        ['Usamah bin Zaid', 'L', '0081234581'],
        ['Ruqayyah binti Rasul', 'P', '0081234582'],
        ['Thalhah bin Ubaidillah', 'L', '0081234583'],
        ['Asma binti Abu Bakar', 'P', '0081234584'],
        ['Sa\'ad bin Abi Waqqas', 'L', '0081234585'],
        ['Zainab binti Jahsy', 'P', '0081234586']
    ];
    $cls = $db->query("SELECT id FROM classes WHERE unit_id = 3 LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
    $clsId = !empty($cls) ? $cls[0] : 3;

    foreach ($names as $idx => $n) {
        $nis = 'SD' . str_pad($sdStudentCount + $idx + 1, 4, '0', STR_PAD_LEFT);
        $db->prepare("INSERT INTO students (nis, nisn, no_kk, nama, gender, birthplace, birthdate, unit_id, status)
                      VALUES (?, ?, '2171010101010099', ?, ?, 'Batam', '2016-05-10', 3, 'Aktif')")->execute([$nis, $n[2], $n[0], $n[1]]);
        $stdId = $db->lastInsertId();
        $targetCls = $cls[$idx % count($cls)] ?? $clsId;
        // Enroll in TA 1 and TA 2
        $db->prepare("INSERT IGNORE INTO class_students (class_id, student_id, academic_year_id, status) VALUES (?, ?, 1, 'Aktif')")->execute([$targetCls, $stdId]);
        $db->prepare("INSERT IGNORE INTO class_students (class_id, student_id, academic_year_id, status) VALUES (?, ?, 2, 'Aktif')")->execute([$targetCls, $stdId]);
    }
    echo "Added extra students to SD (unit 3).\n";
}

// 2. Employee Attendance Seeding (for TA 1 - Genap, months 1..6, and TA 2 - Ganjil, months 7..12)
$tas = [
    1 => [1, 2, 3, 4, 5, 6],    // TA 1: Genap
    2 => [7, 8, 9, 10, 11, 12]  // TA 2: Ganjil (Archived)
];

$allEmployees = $db->query("SELECT id, unit_id, position, level_posisi FROM employees WHERE is_deleted = 0 AND unit_id IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);

foreach ($tas as $taId => $months) {
    foreach ($months as $m) {
        foreach ($allEmployees as $emp) {
            // High attendance with small realistic variance
            $seed = ($emp['id'] * 17 + $taId * 31 + $m * 7) % 100;
            $hadir = 20;
            $sakit = 0;
            $izin = 0;
            $alpha = 0;
            $terlambat = 0;

            if ($seed < 70) {
                // Perfect attendance
                $hadir = 20;
            } elseif ($seed < 85) {
                $hadir = 19;
                $izin = 1;
                $terlambat = 5;
            } elseif ($seed < 95) {
                $hadir = 18;
                $sakit = 2;
                $terlambat = 10;
            } else {
                $hadir = 18;
                $izin = 1;
                $alpha = 1;
                $terlambat = 15;
            }

            $db->prepare("INSERT INTO employee_attendance 
                (employee_id, unit_id, academic_year_id, month_num, hadir, sakit, izin, alpha, terlambat_menit, kajian_hadir, kajian_total, recorded_by_user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 4, 4, 1)
                ON DUPLICATE KEY UPDATE hadir = VALUES(hadir), sakit = VALUES(sakit), izin = VALUES(izin), alpha = VALUES(alpha), terlambat_menit = VALUES(terlambat_menit)")
                ->execute([$emp['id'], $emp['unit_id'], $taId, $m, $hadir, $sakit, $izin, $alpha, $terlambat]);
        }
    }
}
echo "Seeded employee attendance for TA 1 & TA 2.\n";

// 3. Student Attendance Seeding
$allStudents = $db->query("SELECT s.id, s.unit_id, cs.class_id, cs.academic_year_id 
                           FROM students s 
                           JOIN class_students cs ON s.id = cs.student_id 
                           WHERE s.status = 'Aktif'")->fetchAll(PDO::FETCH_ASSOC);

foreach ($tas as $taId => $months) {
    foreach ($months as $m) {
        foreach ($allStudents as $std) {
            if ((int)$std['academic_year_id'] !== $taId) continue;

            $seed = ($std['id'] * 23 + $taId * 19 + $m * 11) % 100;
            $hadir = 20;
            $sakit = 0;
            $izin = 0;
            $alpha = 0;

            if ($seed < 75) {
                $hadir = 20;
            } elseif ($seed < 88) {
                $hadir = 19;
                $sakit = 1;
            } elseif ($seed < 96) {
                $hadir = 18;
                $izin = 2;
            } else {
                $hadir = 17;
                $alpha = 1;
                $sakit = 2;
            }

            $effectiveDays = 20;
            $pct = round(($hadir / $effectiveDays) * 100, 2);

            $db->prepare("INSERT INTO student_attendance 
                (student_id, class_id, academic_year_id, month_num, hadir, effective_days, sakit, izin, alpha, alfa, attendance_pct, created_by_user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE hadir = VALUES(hadir), sakit = VALUES(sakit), izin = VALUES(izin), alpha = VALUES(alpha), attendance_pct = VALUES(attendance_pct)")
                ->execute([$std['id'], $std['class_id'], $taId, $m, $hadir, $effectiveDays, $sakit, $izin, $alpha, $alpha, $pct]);
        }
    }
}
echo "Seeded student attendance for TA 1 & TA 2.\n";

// 4. Student Movement Logs (Rekap Pergerakan Siswa per Bulan & Live Count)
$monthNames = [
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni'
];

foreach ($unitIds as $uId) {
    // Current live count
    $baseCount = (int)$db->query("SELECT COUNT(*) FROM students WHERE unit_id = $uId AND status = 'Aktif'")->fetchColumn();
    if ($baseCount === 0) $baseCount = 15;

    // Movement pattern for TA 2 (Ganjil - Jul..Des)
    $currGanjil = max(5, $baseCount - 8);
    $ganjilMovements = [
        7  => ['masuk' => 12, 'keluar' => 0],
        8  => ['masuk' => 2,  'keluar' => 1],
        9  => ['masuk' => 1,  'keluar' => 0],
        10 => ['masuk' => 0,  'keluar' => 1],
        11 => ['masuk' => 1,  'keluar' => 0],
        12 => ['masuk' => 0,  'keluar' => 2]
    ];

    foreach ($ganjilMovements as $mNum => $mov) {
        $currGanjil = $currGanjil + $mov['masuk'] - $mov['keluar'];
        $db->prepare("INSERT INTO student_movement_logs (unit_id, academic_year_id, month_num, month_name, masuk, keluar, current_count, notes)
                      VALUES (?, 2, ?, ?, ?, ?, ?, 'Rekap semester ganjil')
                      ON DUPLICATE KEY UPDATE masuk = VALUES(masuk), keluar = VALUES(keluar), current_count = VALUES(current_count)")
            ->execute([$uId, $mNum, $monthNames[$mNum], $mov['masuk'], $mov['keluar'], $currGanjil]);
    }

    // Movement pattern for TA 1 (Genap - Jan..Jun)
    $currGenap = $currGanjil;
    $genapMovements = [
        1 => ['masuk' => 3, 'keluar' => 1],
        2 => ['masuk' => 2, 'keluar' => 0],
        3 => ['masuk' => 1, 'keluar' => 1],
        4 => ['masuk' => 0, 'keluar' => 0],
        5 => ['masuk' => 1, 'keluar' => 0],
        6 => ['masuk' => 0, 'keluar' => 1]
    ];

    foreach ($genapMovements as $mNum => $mov) {
        $currGenap = $currGenap + $mov['masuk'] - $mov['keluar'];
        $db->prepare("INSERT INTO student_movement_logs (unit_id, academic_year_id, month_num, month_name, masuk, keluar, current_count, notes)
                      VALUES (?, 1, ?, ?, ?, ?, ?, 'Rekap semester genap')
                      ON DUPLICATE KEY UPDATE masuk = VALUES(masuk), keluar = VALUES(keluar), current_count = VALUES(current_count)")
            ->execute([$uId, $mNum, $monthNames[$mNum], $mov['masuk'], $mov['keluar'], $currGenap]);
    }
}
echo "Seeded student_movement_logs for all units across months.\n";

// 5. Student Grades Seeding (All mapel, periodic per month)
$sdSubjects = $db->query("SELECT id, name FROM subjects WHERE unit_id = 3")->fetchAll(PDO::FETCH_ASSOC);
$sdClasses = $db->query("SELECT id FROM classes WHERE unit_id = 3")->fetchAll(PDO::FETCH_COLUMN);
$sdStudents = $db->query("SELECT id FROM students WHERE unit_id = 3 AND status = 'Aktif'")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tas as $taId => $months) {
    foreach ($months as $m) {
        foreach ($sdSubjects as $sb) {
            foreach ($sdStudents as $sId) {
                $clsId = $sdClasses[$sId % count($sdClasses)];
                // Score between 72 and 96
                $base = 78 + (($sId * 13 + $sb['id'] * 17 + $m * 5) % 18);
                if ($taId === 1) $base += 2; // slight improvement in Genap
                $score = min(98, max(68, $base));
                $kkm = 75.00;

                $db->prepare("INSERT INTO student_grades 
                    (student_id, class_id, subject_id, academic_year_id, month_num, score, kkm, is_locked, created_by_user_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1)
                    ON DUPLICATE KEY UPDATE score = VALUES(score), kkm = VALUES(kkm)")
                    ->execute([$sId, $clsId, $sb['id'], $taId, $m, $score, $kkm]);
            }
        }
    }
}
echo "Seeded student_grades for SD across all subjects and months.\n";

// 6. Bills (SPP Tagihan & Tunggakan)
$sppAmount = 650000.00;
foreach ($tas as $taId => $months) {
    $year = ($taId === 1) ? 2025 : 2024;
    foreach ($months as $m) {
        foreach ($sdStudents as $idx => $sId) {
            // 85% lunas, 15% tertunggak
            $isPaid = (($sId * 7 + $m * 13) % 100) < 85;
            $status = $isPaid ? 'Lunas' : 'Tertunggak';
            $paidAmt = $isPaid ? $sppAmount : 0.00;
            $paidDate = $isPaid ? date("$year-".str_pad($m, 2, '0', STR_PAD_LEFT)."-10") : null;

            // Check if bill exists
            $existing = $db->prepare("SELECT id FROM bills WHERE unit_id = 3 AND academic_year_id = ? AND student_id = ? AND bill_month = ? AND bill_year = ?");
            $existing->execute([$taId, $sId, $m, $year]);
            $bId = $existing->fetchColumn();

            if ($bId) {
                $db->prepare("UPDATE bills SET amount = ?, status = ?, paid_date = ?, paid_amount = ? WHERE id = ?")
                   ->execute([$sppAmount, $status, $paidDate, $paidAmt, $bId]);
            } else {
                $db->prepare("INSERT INTO bills (unit_id, academic_year_id, student_id, bill_month, bill_year, title, amount, due_date, status, paid_date, paid_amount, payment_method, created_by_user_id)
                              VALUES (3, ?, ?, ?, ?, 'SPP Bulanan', ?, ?, ?, ?, ?, 'Transfer Bank', 1)")
                   ->execute([$taId, $sId, $m, $year, $sppAmount, "$year-".str_pad($m, 2, '0', STR_PAD_LEFT)."-15", $status, $paidDate, $paidAmt]);
            }
        }
    }
}
echo "Seeded bills & tunggakan for SD across months.\n";

// 7. Supervisi Sessions
$sdTeachers = $db->query("SELECT id, nama FROM employees WHERE unit_id = 3 AND is_deleted = 0 AND (level_posisi LIKE '%Guru%' OR position LIKE '%Guru%')")->fetchAll(PDO::FETCH_ASSOC);
$kepsekSd = $db->query("SELECT id FROM employees WHERE unit_id = 3 AND (level_posisi LIKE '%Kepala%' OR position LIKE '%Kepala%') LIMIT 1")->fetchColumn() ?: 1;

foreach ($tas as $taId => $months) {
    foreach ($sdTeachers as $idx => $tch) {
        $sbId = $sdSubjects[$idx % count($sdSubjects)]['id'];
        $clsId = $sdClasses[$idx % count($sdClasses)];
        $m = $months[$idx % count($months)];
        $year = ($taId === 1) ? 2025 : 2024;
        $sessionDate = "$year-" . str_pad($m, 2, '0', STR_PAD_LEFT) . "-18";

        $scoreA = 9.5;
        $scoreB = 18.0 + ($idx % 3);
        $scoreC = 62.0 + ($idx % 8);
        $finalScore = $scoreA + $scoreB + $scoreC;

        if ($finalScore >= 90) {
            $pred = 'SANGAT BAIK';
        } elseif ($finalScore >= 80) {
            $pred = 'BAIK';
        } else {
            $pred = 'CUKUP';
        }

        $existing = $db->prepare("SELECT id FROM supervisi_sessions WHERE unit_id = 3 AND academic_year_id = ? AND teacher_id = ?");
        $existing->execute([$taId, $tch['id']]);
        $supId = $existing->fetchColumn();

        if ($supId) {
            $db->prepare("UPDATE supervisi_sessions SET session_date = ?, score_a = ?, score_b = ?, score_c = ?, total_score = ?, final_score = ?, achievement_predicate = ? WHERE id = ?")
               ->execute([$sessionDate, $scoreA, $scoreB, $scoreC, $finalScore, $finalScore, $pred, $supId]);
        } else {
            $db->prepare("INSERT INTO supervisi_sessions (unit_id, academic_year_id, supervisor_id, teacher_id, subject_id, class_id, session_date, score_a, score_b, score_c, total_score, max_score, final_score, achievement_predicate, notes_good, notes_improve)
                          VALUES (3, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 100.00, ?, ?, 'Metode pembelajaran aktif dan interaktif', 'Pemanfaatan media ajar digital perlu ditingkatkan')")
               ->execute([$taId, $kepsekSd, $tch['id'], $sbId, $clsId, $sessionDate, $scoreA, $scoreB, $scoreC, $finalScore, $finalScore, $pred]);
        }
    }
}
echo "Seeded supervisi sessions for SD teachers.\n";
echo "=== SEEDING COMPLETED SUCCESSFULLY ===\n";
