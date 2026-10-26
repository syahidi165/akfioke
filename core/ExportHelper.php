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
        $kopTitle = htmlspecialchars($unit['kop_title'] ?? 'YAYASAN ISLAM AL KAHFI BATAM', ENT_QUOTES, 'UTF-8');
        $kopSub = htmlspecialchars($unit['kop_subtitle'] ?? '', ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($unit['address'] ?? '', ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars($unit['phone'] ?? '', ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($unit['email'] ?? '', ENT_QUOTES, 'UTF-8');

        // Resolve logo_path jika tidak disediakan langsung di array $unit
        $logoPath = $unit['logo_path'] ?? '';
        $code = $unit['code'] ?? '';
        if (empty($logoPath)) {
            $uId = (int)($unit['unit_id'] ?? ($unit['id'] ?? 0));
            if ($uId > 0) {
                $dbUnit = Database::fetch("SELECT logo_path, code, kop_title, kop_subtitle, address, phone, email FROM units WHERE id = ?", [$uId]);
                if ($dbUnit) {
                    $logoPath = $dbUnit['logo_path'] ?? '';
                    if (empty($code)) $code = $dbUnit['code'] ?? '';
                    if (empty($unit['kop_title']) && !empty($dbUnit['kop_title'])) $kopTitle = htmlspecialchars($dbUnit['kop_title'], ENT_QUOTES, 'UTF-8');
                    if (empty($unit['kop_subtitle']) && !empty($dbUnit['kop_subtitle'])) $kopSub = htmlspecialchars($dbUnit['kop_subtitle'], ENT_QUOTES, 'UTF-8');
                    if (empty($unit['address']) && !empty($dbUnit['address'])) $address = htmlspecialchars($dbUnit['address'], ENT_QUOTES, 'UTF-8');
                    if (empty($unit['phone']) && !empty($dbUnit['phone'])) $phone = htmlspecialchars($dbUnit['phone'], ENT_QUOTES, 'UTF-8');
                    if (empty($unit['email']) && !empty($dbUnit['email'])) $email = htmlspecialchars($dbUnit['email'], ENT_QUOTES, 'UTF-8');
                }
            }
        }

        // Render gambar logo jika ada berkas fisiknya di server
        $logoHtml = '';
        $fullLogoPath = !empty($logoPath) ? dirname(__DIR__) . '/public/' . ltrim($logoPath, '/') : '';
        if (!empty($fullLogoPath) && file_exists($fullLogoPath)) {
            $ext = strtolower(pathinfo($fullLogoPath, PATHINFO_EXTENSION));
            $mime = match($ext) {
                'png'  => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'svg'  => 'image/svg+xml',
                'webp' => 'image/webp',
                default => 'image/png'
            };
            $base64 = base64_encode(file_get_contents($fullLogoPath));
            $logoHtml = "<img src='data:{$mime};base64,{$base64}' style='max-height: 68px; max-width: 78px; object-fit: contain; margin: 0 auto; display: block;'>";
        } else {
            $abbr = htmlspecialchars(!empty($code) ? $code : 'AK', ENT_QUOTES, 'UTF-8');
            $logoHtml = "<div style='width: 60px; height: 60px; border-radius: 50%; background: #1e3a8a; color: #fff; font-weight: bold; line-height: 60px; font-size: 18px; text-align: center; margin: 0 auto;'>{$abbr}</div>";
        }

        return "
        <div style='border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 16px; text-align: center; font-family: Helvetica, Arial, sans-serif;'>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='width: 82px; text-align: center; vertical-align: middle;'>
                        {$logoHtml}
                    </td>
                    <td style='text-align: center; vertical-align: middle; padding-left: 6px;'>
                        <div style='font-size: 15px; font-weight: bold; text-transform: uppercase; color: #1e3a8a; letter-spacing: 0.5px;'>{$kopTitle}</div>
                        <div style='font-size: 13px; font-weight: bold; text-transform: uppercase; color: #111; margin-top: 2px;'>{$kopSub}</div>
                        <div style='font-size: 9.5px; color: #444; margin-top: 4px;'>Alamat: {$address} | Telp: {$phone} | Email: {$email}</div>
                    </td>
                </tr>
            </table>
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

