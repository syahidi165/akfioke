<?php
// core/ExportHelper.php
namespace Core;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Dompdf\Dompdf;
use Dompdf\Options;

class ExportHelper {
    public static function xlsx(array $headers, array $rows, string $filename, ?string $title = null): void {
        self::excel($filename, $headers, $rows, $title);
    }

    /**
     * Ekspor data tabel ke berkas XLSX dengan proteksi Formula Injection
     */
    public static function excel(string $filename, array $headers, array $rows, ?string $title = null): void {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($filename, 0, 30));

        $startRow = 1;
        if ($title) {
            $sheet->setCellValue('A1', SecurityHelper::cleanFormula($title));
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $startRow = 3;
        }

        // Tulis Headers
        $colIndex = 1;
        foreach ($headers as $header) {
            $cellCoord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $startRow;
            $sheet->setCellValue($cellCoord, SecurityHelper::cleanFormula($header));
            $colIndex++;
        }

        $headerEndCoord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . $startRow;
        $headerRange = "A{$startRow}:{$headerEndCoord}";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']], // Dark Blue
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $sheet->getRowDimension($startRow)->setRowHeight(24);

        // Tulis Data Rows
        $currentRow = $startRow + 1;
        foreach ($rows as $row) {
            $colIndex = 1;
            foreach ($row as $val) {
                $cellCoord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $currentRow;
                // Anti Formula Injection
                $sheet->setCellValue($cellCoord, SecurityHelper::cleanFormula($val));
                $colIndex++;
            }
            $currentRow++;
        }

        $lastRow = max($startRow + 1, $currentRow - 1);
        $dataEndCoord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . $lastRow;
        $tableRange = "A{$startRow}:{$dataEndCoord}";
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Auto-fit kolom
        foreach (range(1, count($headers)) as $col) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Kirim header unduhan
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Render dan cetak PDF menggunakan Dompdf dengan Kop Unit
     */
    public static function pdf(string $htmlContent, string $filename, string $paperSize = 'A4', string $orientation = 'portrait', bool $stream = true): void {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContent);
        $dompdf->setPaper($paperSize, $orientation);
        $dompdf->render();

        if ($stream) {
            if (ob_get_length()) ob_end_clean();
            $dompdf->stream($filename . '.pdf', ['Attachment' => false]);
            exit;
        }
    }

    /**
     * Template Kop Surat Standar Unit dengan Logo Kustom
     */
    public static function renderKop(array $unit): string {
        $uId = (int)($unit['unit_id'] ?? ($unit['id'] ?? 0));
        $dbUnit = null;
        if ($uId > 0) {
            $dbUnit = Database::fetch("SELECT * FROM units WHERE id = ?", [$uId]);
        } elseif (!empty($unit['code'])) {
            $dbUnit = Database::fetch("SELECT * FROM units WHERE code = ?", [$unit['code']]);
        }

        $source = array_merge($dbUnit ?: [], $unit);

        $kopTitle = !empty($source['kop_title']) ? $source['kop_title'] : 'YAYASAN ISLAM AL KAHFI BATAM';
        $kopSub = !empty($source['kop_subtitle']) ? $source['kop_subtitle'] : ($source['name'] ?? '');
        $akreditasi = trim($source['akreditasi'] ?? '');
        $izinOp = trim($source['nomor_izin_operasional'] ?? '');
        $npsn = trim($source['npsn'] ?? '');
        $address = trim($source['address'] ?? '');
        $phone = trim($source['phone'] ?? '');
        $email = trim($source['email'] ?? '');
        $logoPath = trim($source['logo_path'] ?? '');
        $code = trim($source['code'] ?? '');

        // 1. Logo Kiri: Logo Yayasan Al Kahfi
        $yayasanLogoPath = dirname(__DIR__) . '/public/images/logo_alkahfi.png';
        if (!file_exists($yayasanLogoPath)) {
            $yayasanLogoPath = dirname(__DIR__) . '/public/uploads/logos/app_logo_1790375160.png';
        }
        $yayasanLogoHtml = '';
        if (file_exists($yayasanLogoPath)) {
            $base64Y = base64_encode(file_get_contents($yayasanLogoPath));
            $yayasanLogoHtml = "<img src='data:image/png;base64,{$base64Y}' style='max-height: 75px; max-width: 80px; object-fit: contain; display: block; margin: auto;'>";
        } else {
            $yayasanLogoHtml = "<div style='width: 65px; height: 65px; border-radius: 50%; background: #0284c7; color: #fff; font-weight: bold; line-height: 65px; font-size: 14px; text-align: center; margin: auto;'>YIAK</div>";
        }

        // 2. Logo Kanan: Logo Unit Sekolah
        $unitLogoHtml = '';
        $fullUnitLogoPath = !empty($logoPath) ? dirname(__DIR__) . '/public/' . ltrim($logoPath, '/') : '';
        if (!empty($fullUnitLogoPath) && file_exists($fullUnitLogoPath)) {
            $ext = strtolower(pathinfo($fullUnitLogoPath, PATHINFO_EXTENSION));
            $mime = match($ext) {
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'svg' => 'image/svg+xml',
                'webp' => 'image/webp',
                default => 'image/png'
            };
            $base64U = base64_encode(file_get_contents($fullUnitLogoPath));
            $unitLogoHtml = "<img src='data:{$mime};base64,{$base64U}' style='max-height: 75px; max-width: 80px; object-fit: contain; display: block; margin: auto;'>";
        } else {
            $abbr = !empty($code) ? htmlspecialchars($code, ENT_QUOTES, 'UTF-8') : 'UNIT';
            $unitLogoHtml = "<div style='width: 65px; height: 65px; border-radius: 50%; border: 2px solid #38bdf8; color: #0284c7; font-weight: bold; line-height: 61px; font-size: 13px; text-align: center; margin: auto;'>{$abbr}</div>";
        }

        // Subline: Akreditasi
        $akreditasiHtml = '';
        if (!empty($akreditasi)) {
            $akreditasiClean = htmlspecialchars($akreditasi, ENT_QUOTES, 'UTF-8');
            $akreditasiHtml = "<div style='font-size: 12px; font-weight: bold; color: #0284c7; margin-top: 2px; letter-spacing: 0.5px;'>&ldquo;AKREDITASI {$akreditasiClean}&rdquo;</div>";
        }

        // Line: Izin Operasional & NPSN
        $legalParts = [];
        if (!empty($izinOp)) {
            $legalParts[] = "Izin Operasional: " . htmlspecialchars($izinOp, ENT_QUOTES, 'UTF-8');
        }
        if (!empty($npsn)) {
            $legalParts[] = "NPSN: " . htmlspecialchars($npsn, ENT_QUOTES, 'UTF-8');
        }
        $legalHtml = '';
        if (!empty($legalParts)) {
            $legalText = implode('; ', $legalParts);
            $legalHtml = "<div style='font-size: 9.5px; color: #0284c7; margin-top: 2px; font-weight: 500;'>{$legalText}</div>";
        }

        // Line: Alamat
        $addressParts = [];
        if (!empty($address)) {
            $addressParts[] = htmlspecialchars($address, ENT_QUOTES, 'UTF-8');
        }
        if (!empty($phone)) {
            $addressParts[] = "Telp: " . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
        }
        if (!empty($email)) {
            $addressParts[] = "Email: " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        }
        $addressText = implode(' | ', $addressParts);
        $addressHtml = "<div style='font-size: 8.5px; color: #3b82f6; margin-top: 3px;'>{$addressText}</div>";

        $escapedKopTitle = htmlspecialchars($kopTitle, ENT_QUOTES, 'UTF-8');
        $escapedKopSub = htmlspecialchars($kopSub, ENT_QUOTES, 'UTF-8');

        return "
        <div style='margin-bottom: 16px; font-family: Helvetica, Arial, sans-serif;'>
            <table style='width: 100%; border-collapse: collapse; margin-bottom: 4px;'>
                <tr>
                    <td style='width: 85px; text-align: center; vertical-align: middle;'>
                        {$yayasanLogoHtml}
                    </td>
                    <td style='text-align: center; vertical-align: middle; padding: 0 10px;'>
                        <div style='font-size: 13.5px; font-weight: bold; text-transform: uppercase; color: #38bdf8; letter-spacing: 0.8px;'>{$escapedKopTitle}</div>
                        <div style='font-size: 15px; font-weight: 900; text-transform: uppercase; color: #2563eb; margin-top: 2px; letter-spacing: 0.3px;'>{$escapedKopSub}</div>
                        {$akreditasiHtml}
                        {$legalHtml}
                        {$addressHtml}
                    </td>
                    <td style='width: 85px; text-align: center; vertical-align: middle;'>
                        {$unitLogoHtml}
                    </td>
                </tr>
            </table>
            <div style='border-top: 2.5px solid #0284c7; border-bottom: 1px solid #0284c7; height: 2px; margin-top: 4px;'></div>
        </div>
        ";
    }

    /**
     * Template Kop Surat Resmi Tingkat YAYASAN (Deputi Direktur Pendidikan)
     */
    public static function renderKopYayasan(): string {
        $logoPath = dirname(__DIR__) . '/public/images/logo_alkahfi.png';
        if (!file_exists($logoPath)) {
            $logoPath = dirname(__DIR__) . '/public/uploads/logos/app_logo_1790375160.png';
        }

        $logoHtml = '';
        if (file_exists($logoPath)) {
            $base64 = base64_encode(file_get_contents($logoPath));
            $logoHtml = "<img src='data:image/png;base64,{$base64}' style='max-height: 72px; max-width: 82px; object-fit: contain; margin: 0 auto; display: block;'>";
        } else {
            $logoHtml = "<div style='width: 65px; height: 65px; border-radius: 50%; background: #0f766e; color: #fff; font-weight: bold; line-height: 65px; font-size: 20px; text-align: center; margin: 0 auto;'>YIAK</div>";
        }

        return "
        <div style='border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 16px; text-align: center; font-family: Helvetica, Arial, sans-serif;'>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='width: 85px; text-align: center; vertical-align: middle;'>
                        {$logoHtml}
                    </td>
                    <td style='text-align: center; vertical-align: middle; padding-left: 8px;'>
                        <div style='font-size: 16px; font-weight: bold; text-transform: uppercase; color: #0f766e; letter-spacing: 0.8px;'>YAYASAN ISLAM AL KAHFI BATAM</div>
                        <div style='font-size: 13px; font-weight: bold; text-transform: uppercase; color: #1e293b; margin-top: 2px;'>DIREKTORAT PENDIDIKAN</div>
                        <div style='font-size: 9.5px; color: #475569; margin-top: 4px;'>Gedung Graha Al-Kahfi Lt. 2, Komp. Islamic Center Batam Centre, Kota Batam - Kepulauan Riau</div>
                        <div style='font-size: 9px; color: #64748b; margin-top: 2px;'>Telp: (0778) 469123 | Pos-el: pendidikan@alkahfi.sch.id | Laman: www.aisbatam.sch.id</div>
                    </td>
                </tr>
            </table>
        </div>
        ";
    }
}

