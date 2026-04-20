<?php
/**
 * ======================================================================
 *  FUNGSI HELPER UNTUK EXPORT PDF & EXCEL (FIXED EXCEL)
 * ======================================================================
 */

/**
 * ======================================================================
 *  EXPORT KE PDF menggunakan TCPDF (SIMPLE & CLEAN)
 * ======================================================================
 */
function generatePDF($data, $filename = 'data_karyawan.pdf')
{
    // Bersihkan semua output buffer untuk menghindari error
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Start fresh output buffer
    ob_start();
    
    // Menyertakan library TCPDF
    require_once __DIR__ . '/../lib/tcpdf/tcpdf.php';
    $pdo = getPDO();

    // Ambil semua kolom dari tabel karyawan
    $cols = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'karyawan'
        ORDER BY ordinal_position
    ")->fetchAll(PDO::FETCH_COLUMN);

    // Hapus kolom ID dari daftar kolom
    $cols = array_filter($cols, fn($c) => $c !== 'id_karyawan');

    // Inisialisasi TCPDF dengan orientasi landscape
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

    // Set properties dokumen PDF
    $pdf->SetCreator('PT NT HRD System');
    $pdf->SetAuthor('Admin HRD');
    $pdf->SetTitle('Data Karyawan');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set margin
    $pdf->SetMargins(12, 12, 12);
    $pdf->SetAutoPageBreak(TRUE, 12);
    $pdf->AddPage();

    // Judul dokumen
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'DATA KARYAWAN PT NT', 0, 1, 'C');
    $pdf->Ln(2);

    // Info tanggal
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 5, 'Tanggal Export: ' . date('d-m-Y H:i:s') . ' | Total Data: ' . count($data) . ' karyawan', 0, 1, 'C');
    $pdf->Ln(5);

    // Generate tabel HTML dengan styling sederhana
    $html = '
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th {
            background-color: #34495e;
            color: #ffffff;
            font-weight: bold;
            padding: 6px 4px;
            text-align: center;
            font-size: 8px;
            border: 1px solid #2c3e50;
        }
        td {
            padding: 5px 4px;
            font-size: 8px;
            border: 1px solid #cccccc;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f5f5f5;
        }
    </style>
    <table>
        <thead>
            <tr>';

    // Generate header tabel
    foreach ($cols as $col) {
        $colName = str_replace('_', ' ', $col);
        $colName = ucwords($colName);
        $html .= '<th>' . $colName . '</th>';
    }
    $html .= '</tr></thead><tbody>';

    // Generate baris data
    foreach ($data as $row) {
        $html .= '<tr>';

        foreach ($cols as $col) {
            $value = $row[$col] ?? '-';
            
            // Format tanggal jika kolom mengandung kata 'tanggal'
            if (strpos($col, 'tanggal') !== false && !empty($row[$col])) {
                $value = date('d-m-Y', strtotime($row[$col]));
            }
            
            // Format nominal uang
            if (stripos($col, 'gaji') !== false || stripos($col, 'salary') !== false) {
                if (is_numeric($value)) {
                    $value = 'Rp ' . number_format($value, 0, ',', '.');
                }
            }

            // Sanitize dan potong teks panjang
            $value = htmlspecialchars($value);
            if (strlen($value) > 50) {
                $value = substr($value, 0, 47) . '...';
            }

            $html .= '<td>' . $value . '</td>';
        }

        $html .= '</tr>';
    }

    $html .= '</tbody></table>';

    // Tulis HTML ke PDF
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Bersihkan output buffer sebelum kirim headers
    ob_end_clean();
    
    // Set headers untuk download PDF
    if (!headers_sent()) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
    }
    
    // Output PDF sebagai string dan kirim ke browser
    echo $pdf->Output($filename, 'S');
    exit;
}

/**
 * ======================================================================
 *  EXPORT KE EXCEL menggunakan SimpleXLSXGen (FIXED & SIMPLE)
 * ======================================================================
 */
function generateExcel($data, $filename = 'data_karyawan.xlsx')
{
    // Bersihkan semua output buffer
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Menyertakan library SimpleXLSXGen
    require_once __DIR__ . '/../lib/simplexlsxgen/src/SimpleXLSXGen.php';
    $pdo = getPDO();

    // Ambil seluruh kolom dari tabel karyawan
    $cols = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'karyawan'
        ORDER BY ordinal_position
    ")->fetchAll(PDO::FETCH_COLUMN);

    // Hapus kolom id
    $cols = array_filter($cols, fn($c) => $c !== 'id_karyawan');

    $rows = [];

    // Baris 1: Judul
    $titleRow = ['DATA KARYAWAN PT NT'];
    for ($i = 1; $i < count($cols); $i++) {
        $titleRow[] = '';
    }
    $rows[] = $titleRow;

    // Baris 2: Info
    $infoRow = ['Tanggal Export: ' . date('d-m-Y H:i:s') . ' | Total: ' . count($data) . ' karyawan'];
    for ($i = 1; $i < count($cols); $i++) {
        $infoRow[] = '';
    }
    $rows[] = $infoRow;
    
    // Baris 3: Kosong
    $emptyRow = [];
    for ($i = 0; $i < count($cols); $i++) {
        $emptyRow[] = '';
    }
    $rows[] = $emptyRow;

    // Baris 4: Header kolom tabel
    $headerRow = [];
    foreach ($cols as $col) {
        $colName = str_replace('_', ' ', $col);
        $colName = ucwords($colName);
        $headerRow[] = $colName;
    }
    $rows[] = $headerRow;

    // Baris 5+: Data
    foreach ($data as $row) {
        $newRow = [];

        foreach ($cols as $col) {
            $value = $row[$col] ?? '-';
            
            // Format tanggal
            if (strpos($col, 'tanggal') !== false && !empty($row[$col]) && $row[$col] != '-') {
                $value = date('d-m-Y', strtotime($row[$col]));
            }
            
            // Format nominal uang - simpan sebagai text dengan format Rp
            if ((stripos($col, 'gaji') !== false || stripos($col, 'salary') !== false) && is_numeric($value) && $value != '-') {
                $value = 'Rp ' . number_format((float)$value, 0, ',', '.');
            }

            $newRow[] = $value;
        }

        $rows[] = $newRow;
    }

    // Generate Excel tanpa styling kompleks
    $xlsx = new \Shuchkin\SimpleXLSXGen();
    $xlsx->addSheet($rows, 'Data Karyawan');

    // Set headers untuk download Excel
    if (!headers_sent()) {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
    }
    
    // Output file ke browser
    $xlsx->saveAs('php://output');
    exit;
}