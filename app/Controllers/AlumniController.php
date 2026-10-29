<?php
// app/Controllers/AlumniController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\SecurityHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class AlumniController extends Controller {

    // --- 1. REKOMENDASI STUDI LANJUT OLEH WALAS ---
    public function walasRekomendasi(): void {
        $this->requireRole(['walas', 'kepala_sekolah', 'operator_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        // Cari informasi akun walas dan kelas yang diampu
        $userEmp = Database::fetch("SELECT * FROM employees WHERE user_id = ?", [$userId]);
        $myClass = null;
        if ($userEmp) {
            $myClass = Database::fetch(
                "SELECT * FROM classes WHERE (walas_employee_id = ? OR walas_id = ? OR walas_id = ?) AND academic_year_id = ? AND unit_id = ?",
                [$userEmp['id'], $userEmp['id'], $userId, $taId, $unitId]
            );
        }

        $activeRole = Auth::activeRoleName();
        $isWalas = ($activeRole === 'walas');

        $isFinalGrade = false;
        if ($myClass) {
            $lvl = strtoupper($myClass['level'] ?? '');
            $nm = strtoupper($myClass['name'] ?? '');
            $isFinalGrade = (str_contains($lvl, '6') || str_contains($lvl, '9') || 
                             str_contains($lvl, '12') || str_contains($lvl, 'TK B') || 
                             str_contains($nm, 'KELAS 6') || str_contains($nm, 'KELAS 9') || 
                             str_contains($nm, 'KELAS 12') || str_contains($nm, 'TK B') ||
                             str_contains($nm, ' 6') || str_contains($nm, ' 9') || str_contains($nm, ' 12'));
        }

        $students = [];
        if ($isWalas) {
            if ($isFinalGrade && $myClass) {
                // Walas tingkat akhir: HANYA santri di kelasnya sendiri
                $query = "SELECT s.*, cs.class_id, c.name as class_name, c.level as class_level, u.name as unit_name,
                                 at.id as tracking_id, at.destination_type, at.destination_name, at.is_favorite, at.major, at.notes as alumni_notes
                          FROM students s
                          JOIN class_students cs ON s.id = cs.student_id AND cs.academic_year_id = ?
                          JOIN classes c ON cs.class_id = c.id
                          JOIN units u ON s.unit_id = u.id
                          LEFT JOIN alumni_tracking at ON s.id = at.student_id
                          WHERE s.status = 'Aktif'
                            AND cs.class_id = ?
                          ORDER BY s.nama ASC";
                $students = Database::fetchAll($query, [$taId, $myClass['id']]);
            } else {
                // Bukan walas tingkat akhir: kosongkan daftar santri
                $students = [];
            }
        } else {
            // Kepala sekolah atau Operator Sekolah: seluruh santri tingkat akhir di unit
            $query = "SELECT s.*, cs.class_id, c.name as class_name, c.level as class_level, u.name as unit_name,
                             at.id as tracking_id, at.destination_type, at.destination_name, at.is_favorite, at.major, at.notes as alumni_notes
                      FROM students s
                      JOIN class_students cs ON s.id = cs.student_id AND cs.academic_year_id = ?
                      JOIN classes c ON cs.class_id = c.id
                      JOIN units u ON s.unit_id = u.id
                      LEFT JOIN alumni_tracking at ON s.id = at.student_id
                      WHERE s.status = 'Aktif'
                        AND s.unit_id = ?
                        AND (c.level IN ('SD 6', 'SMP 9', 'SMA 12', 'TK B')
                             OR c.level LIKE '%6%' OR c.level LIKE '%9%' OR c.level LIKE '%12%' OR c.level LIKE '%TK B%'
                             OR c.name LIKE '%Kelas 6%' OR c.name LIKE '%Kelas 9%' OR c.name LIKE '%Kelas 12%' OR c.name LIKE '%TK B%')
                      ORDER BY c.name ASC, s.nama ASC";
            $students = Database::fetchAll($query, [$taId, $unitId]);
        }

        // Rekap riil seluruh rekomendasi yang telah tersimpan
        if ($isWalas && $myClass) {
            $savedRecommendations = Database::fetchAll(
                "SELECT at.*, s.nama as student_name, s.nis, c.name as class_name, c.level as class_level 
                 FROM alumni_tracking at 
                 JOIN students s ON at.student_id = s.id 
                 JOIN class_students cs ON s.id = cs.student_id AND cs.academic_year_id = ?
                 JOIN classes c ON cs.class_id = c.id
                 WHERE cs.class_id = ?
                 ORDER BY at.id DESC",
                [$taId, $myClass['id']]
            );
        } else {
            $savedRecommendations = Database::fetchAll(
                "SELECT at.*, s.nama as student_name, s.nis, c.name as class_name, c.level as class_level 
                 FROM alumni_tracking at 
                 JOIN students s ON at.student_id = s.id 
                 LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.academic_year_id = ?
                 LEFT JOIN classes c ON cs.class_id = c.id
                 WHERE at.unit_id = ?
                 ORDER BY at.id DESC",
                [$taId, $unitId]
            );
        }

        $this->view('alumni/walas_rekomendasi', [
            'pageTitle'            => 'Rekomendasi Studi Lanjut Santri Tingkat Akhir',
            'students'             => $students,
            'savedRecommendations' => $savedRecommendations,
            'myClass'              => $myClass,
            'userEmp'              => $userEmp,
            'isWalas'              => $isWalas,
            'isFinalGrade'         => $isFinalGrade
        ]);
    }

    public function walasRekomendasiSubmit(): void {
        $this->requireRole(['walas', 'kepala_sekolah', 'operator_sekolah']);
        $unitId = Auth::unitId();

        $studentId = (int)($_POST['student_id'] ?? 0);
        $destType = trim($_POST['destination_type'] ?? 'Sekolah');
        $destName = trim($_POST['destination_name'] ?? '');
        $major = trim($_POST['major'] ?? '');
        $isFav = !empty($_POST['is_favorite']) ? 1 : 0;
        $notes = trim($_POST['notes'] ?? '');
        $gradYear = date('Y');

        $st = Database::fetch("SELECT id, nama, unit_id FROM students WHERE id = ? AND unit_id = ?", [$studentId, $unitId]);
        if (!$st || empty($destName)) {
            $this->setFlash('danger', 'Mohon pilih santri dan lengkapi nama lembaga lanjutan yang direkomendasikan.');
            $this->redirect(Auth::baseUrl() . '/walas/rekomendasi');
            return;
        }

        $activeRole = Auth::activeRoleName();
        if ($activeRole === 'walas') {
            $userId = Auth::id();
            $taId = Auth::academicYearId();
            $userEmp = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [$userId]);
            $empId = (int)($userEmp['id'] ?? 0);
            $myClass = Database::fetch(
                "SELECT * FROM classes WHERE (walas_employee_id = ? OR walas_id = ? OR walas_id = ?) AND academic_year_id = ? AND unit_id = ?",
                [$empId, $empId, $userId, $taId, $unitId]
            );
            if (!$myClass) {
                $this->setFlash('danger', 'Akses ditolak: Anda tidak memiliki rombel kelas aktif.');
                $this->redirect(Auth::baseUrl() . '/walas/rekomendasi');
                return;
            }
            $inClass = Database::fetch(
                "SELECT id FROM class_students WHERE student_id = ? AND class_id = ? AND academic_year_id = ?",
                [$studentId, $myClass['id'], $taId]
            );
            if (!$inClass) {
                $this->setFlash('danger', 'Akses ditolak: Santri ini bukan siswa di kelas yang Anda ampu.');
                $this->redirect(Auth::baseUrl() . '/walas/rekomendasi');
                return;
            }
        }

        $existing = Database::fetch("SELECT id FROM alumni_tracking WHERE student_id = ?", [$studentId]);
        if ($existing) {
            Database::execute(
                "UPDATE alumni_tracking SET destination_type = ?, destination_name = ?, is_favorite = ?, major = ?, notes = ?, graduation_year = ? WHERE id = ?",
                [$destType, $destName, $isFav, $major, $notes, $gradYear, $existing['id']]
            );
            AuditLog::log('UPDATE_REKOMENDASI_ALUMNI', 'alumni_tracking', $existing['id'], null, $_POST);
            $this->setFlash('success', "Rekomendasi studi lanjut untuk santri {$st['nama']} berhasil diperbarui.");
        } else {
            Database::execute(
                "INSERT INTO alumni_tracking (student_id, unit_id, graduation_year, destination_type, destination_name, is_favorite, major, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$studentId, $unitId, $gradYear, $destType, $destName, $isFav, $major, $notes]
            );
            AuditLog::log('CREATE_REKOMENDASI_ALUMNI', 'alumni_tracking', Database::lastInsertId(), null, $_POST);
            $this->setFlash('success', "Rekomendasi studi lanjut untuk santri {$st['nama']} berhasil disimpan.");
        }

        $this->redirect(Auth::baseUrl() . '/walas/rekomendasi');
    }

    public function walasRekomendasiDelete(): void {
        $this->requireRole(['walas', 'kepala_sekolah', 'operator_sekolah']);
        $trackingId = (int)($_POST['tracking_id'] ?? 0);
        $unitId = Auth::unitId();

        $rec = Database::fetch("SELECT at.*, s.nama FROM alumni_tracking at JOIN students s ON at.student_id = s.id WHERE at.id = ? AND at.unit_id = ?", [$trackingId, $unitId]);
        if ($rec) {
            Database::execute("DELETE FROM alumni_tracking WHERE id = ?", [$trackingId]);
            AuditLog::log('DELETE_REKOMENDASI_ALUMNI', 'alumni_tracking', $trackingId, $rec);
            $this->setFlash('success', "Rekomendasi santri {$rec['nama']} berhasil dihapus.");
        }
        $this->redirect(Auth::baseUrl() . '/walas/rekomendasi');
    }

    // --- 2. PENELUSURAN ALUMNI (FAVORIT & JURUSAN) ---
    public function alumniIndex(): void {
        $this->requireRole(['alumni', 'operator_sekolah', 'kepala_sekolah', 'deputi_pendidikan', 'operator_yayasan', 'walas']);
        $unitId = Auth::unitId();

        $alumniList = Database::fetchAll(
            "SELECT a.*, s.nama as student_name, s.nis, u.name as unit_name 
             FROM alumni_tracking a 
             JOIN students s ON a.student_id = s.id 
             JOIN units u ON a.unit_id = u.id 
             WHERE a.unit_id = ? 
             ORDER BY a.graduation_year DESC, s.nama ASC",
            [$unitId]
        );

        // Ambil hanya siswa yang telah berstatus Lulus, Pindah, atau Alumni
        $students = Database::fetchAll(
            "SELECT id, nis, nama, status, unit_id 
             FROM students 
             WHERE unit_id = ? AND status IN ('Lulus', 'Pindah', 'Alumni') 
             ORDER BY status ASC, nama ASC",
            [$unitId]
        );

        $favoriteCount = 0;
        foreach ($alumniList as $al) {
            if (!empty($al['is_favorite'])) $favoriteCount++;
        }

        $this->view('alumni/index', [
            'pageTitle'     => 'Penelusuran Jejak Alumni (Sekolah & Kampus Favorit)',
            'alumniList'    => $alumniList,
            'students'      => $students,
            'favoriteCount' => $favoriteCount
        ]);
    }

    public function alumniSave(): void {
        $this->requireRole(['alumni', 'operator_sekolah', 'operator_yayasan', 'walas', 'kepala_sekolah']);
        $unitId = Auth::unitId();

        $studentId = (int)($_POST['student_id'] ?? 0);
        $gradYear = $_POST['graduation_year'] ?? date('Y');
        $destType = $_POST['destination_type'] ?? 'Sekolah';
        $destName = trim($_POST['destination_name'] ?? '');
        $major = trim($_POST['major'] ?? '');
        $isFav = !empty($_POST['is_favorite']) ? 1 : 0;
        $notes = trim($_POST['notes'] ?? '');

        if ($studentId && !empty($destName)) {
            $existing = Database::fetch("SELECT id FROM alumni_tracking WHERE student_id = ?", [$studentId]);
            if ($existing) {
                Database::execute(
                    "UPDATE alumni_tracking 
                     SET unit_id = ?, graduation_year = ?, destination_type = ?, destination_name = ?, is_favorite = ?, major = ?, notes = ? 
                     WHERE id = ?",
                    [$unitId, $gradYear, $destType, $destName, $isFav, $major, $notes, $existing['id']]
                );
                AuditLog::log('UPDATE_ALUMNI_TRACKING', 'alumni_tracking', $existing['id'], null, ['student_id' => $studentId, 'dest' => $destName]);
                $this->setFlash('success', 'Data kelanjutan studi alumni berhasil diperbarui.');
            } else {
                Database::execute(
                    "INSERT INTO alumni_tracking (student_id, unit_id, graduation_year, destination_type, destination_name, is_favorite, major, notes) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$studentId, $unitId, $gradYear, $destType, $destName, $isFav, $major, $notes]
                );

                AuditLog::log('CREATE_ALUMNI_TRACKING', 'alumni_tracking', Database::lastInsertId(), null, ['student_id' => $studentId, 'dest' => $destName]);
                $this->setFlash('success', 'Data kelanjutan studi alumni berhasil dicatat.');
            }
        }

        $this->redirect(Auth::baseUrl() . '/alumni');
    }

    public function alumniTemplate(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan', 'kepala_sekolah']);
        $isYayasan = Auth::hasRole('operator_yayasan');
        $unitId = $isYayasan ? (int)($_GET['unit_id'] ?? Auth::unitId()) : Auth::unitId();

        $unit = Database::fetch("SELECT id, name, code FROM units WHERE id = ?", [$unitId]);
        $uCode = $unit['code'] ?? 'SCH';

        // Ambil santri berstatus Lulus, Pindah, atau Alumni di unit ini
        $students = Database::fetchAll(
            "SELECT s.id, s.nis, s.nama, s.status,
                    at.graduation_year, at.destination_type, at.destination_name, at.major, at.is_favorite, at.notes
             FROM students s
             LEFT JOIN alumni_tracking at ON s.id = at.student_id
             WHERE s.unit_id = ? AND s.status IN ('Alumni', 'Lulus', 'Pindah')
             ORDER BY s.nama ASC",
            [$unitId]
        );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Jejak Alumni');

        // Headers
        $headers = [
            'A1' => 'ID Siswa (Wajib/Sistem)',
            'B1' => 'NIS',
            'C1' => 'Nama Lengkap Alumni (Otomatis Terisi)',
            'D1' => 'Tahun Lulus (YYYY)',
            'E1' => 'Kategori Tujuan',
            'F1' => 'Nama Sekolah / Kampus Tujuan *',
            'G1' => 'Jurusan / Program Studi',
            'H1' => 'Favorit / PTN (Ya/Tidak)',
            'I1' => 'Catatan Tambahan (SNBP/Beasiswa/dll)'
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Header Styling
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '047857']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Populate rows with alumni students
        $rowNum = 2;
        $defaultYear = date('Y');
        if (!empty($students)) {
            foreach ($students as $st) {
                $sheet->setCellValue("A{$rowNum}", $st['id']);
                $sheet->setCellValue("B{$rowNum}", $st['nis'] ?? '-');
                $sheet->setCellValue("C{$rowNum}", $st['nama']);
                $sheet->setCellValue("D{$rowNum}", $st['graduation_year'] ?: $defaultYear);
                $sheet->setCellValue("E{$rowNum}", $st['destination_type'] ?: 'Universitas');
                $sheet->setCellValue("F{$rowNum}", $st['destination_name'] ?? '');
                $sheet->setCellValue("G{$rowNum}", $st['major'] ?? '');
                $sheet->setCellValue("H{$rowNum}", (!empty($st['is_favorite']) || $st['is_favorite'] === 1) ? 'Ya' : 'Tidak');
                $sheet->setCellValue("I{$rowNum}", $st['notes'] ?? '');
                $rowNum++;
            }
        } else {
            // Sample guide row
            $sheet->setCellValue("A2", "1");
            $sheet->setCellValue("B2", "2024001");
            $sheet->setCellValue("C2", "Contoh Nama Santri Alumni");
            $sheet->setCellValue("D2", $defaultYear);
            $sheet->setCellValue("E2", "Universitas");
            $sheet->setCellValue("F2", "Universitas Indonesia");
            $sheet->setCellValue("G2", "Kedokteran");
            $sheet->setCellValue("H2", "Ya");
            $sheet->setCellValue("I2", "Jalur SNBP");
            $rowNum = 3;
        }

        $maxRow = max(100, $rowNum);

        // Dropdown Validation: Kategori Tujuan (Column E)
        $valCat = $sheet->getDataValidation("E2:E{$maxRow}");
        $valCat->setType(DataValidation::TYPE_LIST);
        $valCat->setErrorStyle(DataValidation::STYLE_STOP);
        $valCat->setAllowBlank(false);
        $valCat->setShowDropDown(true);
        $valCat->setShowErrorMessage(true);
        $valCat->setErrorTitle('Kategori Tidak Valid');
        $valCat->setError('Pilih kategori tujuan dari daftar dropdown (Sekolah, Universitas, Kerja/Lainnya).');
        $valCat->setFormula1('"Sekolah,Universitas,Kerja/Lainnya"');

        // Dropdown Validation: Favorit (Column H)
        $valFav = $sheet->getDataValidation("H2:H{$maxRow}");
        $valFav->setType(DataValidation::TYPE_LIST);
        $valFav->setErrorStyle(DataValidation::STYLE_STOP);
        $valFav->setAllowBlank(false);
        $valFav->setShowDropDown(true);
        $valFav->setShowErrorMessage(true);
        $valFav->setErrorTitle('Pilihan Tidak Valid');
        $valFav->setError('Pilih Ya atau Tidak dari daftar dropdown.');
        $valFav->setFormula1('"Ya,Tidak"');

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $cleanUnit = preg_replace('/[^a-zA-Z0-9_-]/', '_', $uCode);
        $filename = "Template_Import_Alumni_{$cleanUnit}_" . date('Ymd') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function alumniImport(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan', 'kepala_sekolah']);
        $isYayasan = Auth::hasRole('operator_yayasan');
        $unitId = $isYayasan ? (int)($_POST['unit_id'] ?? Auth::unitId()) : Auth::unitId();

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('danger', 'Berkas Excel tidak valid atau gagal diunggah.');
            $this->redirect(Auth::baseUrl() . '/alumni');
            return;
        }

        $tmpFile = $_FILES['file']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'])) {
            $this->setFlash('danger', 'Hanya berkas Excel dengan ekstensi .xlsx atau .xls yang diperbolehkan.');
            $this->redirect(Auth::baseUrl() . '/alumni');
            return;
        }

        try {
            $spreadsheet = IOFactory::load($tmpFile);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Gagal membaca berkas Excel: ' . $e->getMessage());
            $this->redirect(Auth::baseUrl() . '/alumni');
            return;
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;

        for ($row = 2; $row <= $highestRow; $row++) {
            $studentId = (int)$sheet->getCell("A{$row}")->getValue();
            $nis = trim((string)$sheet->getCell("B{$row}")->getValue());
            $nama = trim((string)$sheet->getCell("C{$row}")->getValue());
            $gradYear = trim((string)$sheet->getCell("D{$row}")->getValue());
            $destType = trim((string)$sheet->getCell("E{$row}")->getValue());
            $destName = trim((string)$sheet->getCell("F{$row}")->getValue());
            $major = trim((string)$sheet->getCell("G{$row}")->getValue());
            $favText = strtolower(trim((string)$sheet->getCell("H{$row}")->getValue()));
            $notes = trim((string)$sheet->getCell("I{$row}")->getValue());

            if (empty($destName)) {
                continue;
            }

            // Validasi keberadaan santri di unit ini
            $student = null;
            if ($studentId > 0) {
                $student = Database::fetch("SELECT id, unit_id, nama FROM students WHERE id = ? AND unit_id = ?", [$studentId, $unitId]);
            }
            if (!$student && !empty($nis) && $nis !== '-') {
                $student = Database::fetch("SELECT id, unit_id, nama FROM students WHERE nis = ? AND unit_id = ?", [$nis, $unitId]);
            }
            if (!$student && !empty($nama)) {
                $student = Database::fetch("SELECT id, unit_id, nama FROM students WHERE nama = ? AND unit_id = ?", [$nama, $unitId]);
            }

            if (!$student) {
                $skipped++;
                continue;
            }

            $sId = (int)$student['id'];
            $destTypeRaw = trim((string)$sheet->getCell("E{$row}")->getValue());
            $allowedDest = ['Sekolah', 'Universitas', 'Kerja/Lainnya'];
            $destType = '';
            if (!empty($destTypeRaw)) {
                foreach ($allowedDest as $ad) {
                    if (strcasecmp($ad, $destTypeRaw) === 0) {
                        $destType = $ad;
                        break;
                    }
                }
                if (empty($destType)) {
                    $skipped++;
                    $errors[] = "Baris {$row} ({$nama}): Kategori Tujuan '{$destTypeRaw}' tidak valid (pilihan: Sekolah, Universitas, Kerja/Lainnya).";
                    continue;
                }
            } else {
                $destType = 'Universitas';
            }

            $favRaw = strtolower(trim((string)$sheet->getCell("H{$row}")->getValue()));
            if (!empty($favRaw) && !in_array($favRaw, ['ya', 'tidak', '1', '0', 'true', 'false', 'y', 't', 'favorit', 'ptn'], true)) {
                $skipped++;
                $errors[] = "Baris {$row} ({$nama}): Kolom Favorit '{$favRaw}' tidak valid (wajib Ya atau Tidak dari dropdown).";
                continue;
            }
            $isFav = in_array($favRaw, ['ya', '1', 'true', 'y', 'favorit', 'ptn'], true) ? 1 : 0;

            $existing = Database::fetch("SELECT id FROM alumni_tracking WHERE student_id = ?", [$sId]);
            if ($existing) {
                Database::execute(
                    "UPDATE alumni_tracking 
                     SET graduation_year = ?, destination_type = ?, destination_name = ?, is_favorite = ?, major = ?, notes = ?, unit_id = ? 
                     WHERE id = ?",
                    [$gradYear, $destType, $destName, $isFav, $major, $notes, $unitId, $existing['id']]
                );
                $updated++;
            } else {
                Database::execute(
                    "INSERT INTO alumni_tracking 
                        (student_id, unit_id, graduation_year, destination_type, destination_name, is_favorite, major, notes) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$sId, $unitId, $gradYear, $destType, $destName, $isFav, $major, $notes]
                );
                $imported++;
            }
        }

        AuditLog::log('IMPORT_ALUMNI_TRACKING', 'alumni_tracking', $unitId, null, [
            'inserted' => $imported, 'updated' => $updated, 'skipped' => $skipped
        ]);

        $msg = "Proses impor jejak alumni selesai: {$imported} data baru ditambahkan, {$updated} diperbarui.";
        if ($skipped > 0) {
            $msg .= " ({$skipped} baris dilewati karena ID/nama santri tidak ditemukan di unit ini).";
        }
        $this->setFlash('success', $msg);
        $this->redirect(Auth::baseUrl() . '/alumni');
    }
}
