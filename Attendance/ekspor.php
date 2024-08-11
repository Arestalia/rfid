<?php
include "database/db.php";
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// Koneksi ke database
$database = new Database();
$conn = $database->getConnection();

// Mengambil tanggal dari parameter POST
$tanggal_awal = $_POST['tanggal_awal'] ?? '2024-01-01';
$tanggal_akhir = $_POST['tanggal_akhir'] ?? '2024-12-31';

// Query SQL untuk mengambil data jurusan dan kelas
$sql_jurusan_kelas = "SELECT DISTINCT jurusan, kelas FROM siswa";
$result_jurusan_kelas = $conn->query($sql_jurusan_kelas);

// Buat spreadsheet baru
$spreadsheet = new Spreadsheet();

// Buat Main Sheet
$mainSheet = $spreadsheet->getActiveSheet();
$mainSheet->setTitle('Rekap Absensi');

// Set header untuk main sheet
$mainSheet->setCellValue('A1', 'Rekap Absensi Siswa SMKN 4 BANDAR LAMPUNG');
$mainSheet->mergeCells('A1:G1');
$mainSheet->getStyle('A1:G1')->getFont()->setBold(true)->setSize(14);
$mainSheet->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Set header kolom untuk main sheet
$mainSheet->setCellValue('A3', 'Nama');
$mainSheet->setCellValue('B3', 'NISN');
$mainSheet->setCellValue('C3', 'Kelas');
$mainSheet->setCellValue('D3', 'Jurusan');
$mainSheet->setCellValue('E3', 'Tanggal');
$mainSheet->setCellValue('F3', 'Waktu');
$mainSheet->setCellValue('G3', 'Status');

// Gaya untuk header kolom
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['argb' => 'FF000000'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFFFFFFF'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
];
$mainSheet->getStyle('A3:G3')->applyFromArray($headerStyle);

// Query untuk mengambil semua data absensi
$sql = "SELECT absensi.siswa_id, absensi.waktu, absensi.tanggal, absensi.status, 
               siswa.nama, siswa.nisn, siswa.kelas, siswa.jurusan
        FROM absensi
        INNER JOIN siswa ON absensi.siswa_id = siswa.id
        WHERE absensi.tanggal BETWEEN ? AND ?
        ORDER BY siswa.nama ASC, absensi.tanggal ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $tanggal_awal, $tanggal_akhir);
$stmt->execute();
$result = $stmt->get_result();

// Isi data di main sheet
$row = 4;
while ($data = $result->fetch_assoc()) {
    $mainSheet->setCellValue('A' . $row, $data['nama']);
    $mainSheet->setCellValue('B' . $row, $data['nisn']);
    $mainSheet->setCellValue('C' . $row, $data['kelas']);
    $mainSheet->setCellValue('D' . $row, $data['jurusan']);
    $mainSheet->setCellValue('E' . $row, $data['tanggal']);
    $mainSheet->setCellValue('F' . $row, $data['waktu']);
    $mainSheet->setCellValue('G' . $row, $data['status']);
    $row++;
}
$mainSheet->getStyle('A4:G' . ($row - 1))->applyFromArray($headerStyle);

// Buat sub sheets berdasarkan jurusan dan kelas
while ($jurusan_kelas = $result_jurusan_kelas->fetch_assoc()) {
    $jurusan = $jurusan_kelas['jurusan'];
    $kelas = $jurusan_kelas['kelas'];

    // Buat worksheet baru untuk setiap jurusan dan kelas
    $subSheet = $spreadsheet->createSheet();
    $subSheet->setTitle("$jurusan - $kelas");

    // Set header untuk sub sheet
    $subSheet->setCellValue('A1', "Absensi $jurusan - $kelas");
    $subSheet->mergeCells('A1:G1');
    $subSheet->getStyle('A1:G1')->getFont()->setBold(true)->setSize(14);
    $subSheet->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Set header kolom untuk sub sheet
    $subSheet->setCellValue('A3', 'Nama');
    $subSheet->setCellValue('B3', 'NISN');
    $subSheet->setCellValue('C3', 'Kelas');
    $subSheet->setCellValue('D3', 'Jurusan');
    $subSheet->setCellValue('E3', 'Tanggal');
    $subSheet->setCellValue('F3', 'Waktu');
    $subSheet->setCellValue('G3', 'Status');
    $subSheet->getStyle('A3:G3')->applyFromArray($headerStyle);

    // Query untuk mengambil data absensi berdasarkan jurusan dan kelas
    $sql_sub = "SELECT absensi.siswa_id, absensi.waktu, absensi.tanggal, absensi.status, 
                    siswa.nama, siswa.nisn, siswa.kelas, siswa.jurusan
                FROM absensi
                INNER JOIN siswa ON absensi.siswa_id = siswa.id
                WHERE absensi.tanggal BETWEEN ? AND ? AND siswa.jurusan = ? AND siswa.kelas = ?
                ORDER BY absensi.tanggal DESC";

    $stmt_sub = $conn->prepare($sql_sub);
    $stmt_sub->bind_param('ssss', $tanggal_awal, $tanggal_akhir, $jurusan, $kelas);
    $stmt_sub->execute();
    $result_sub = $stmt_sub->get_result();

    // Isi data di sub sheet
    $row_sub = 4;
    while ($data_sub = $result_sub->fetch_assoc()) {
        $subSheet->setCellValue('A' . $row_sub, $data_sub['nama']);
        $subSheet->setCellValue('B' . $row_sub, $data_sub['nisn']);
        $subSheet->setCellValue('C' . $row_sub, $data_sub['kelas']);
        $subSheet->setCellValue('D' . $row_sub, $data_sub['jurusan']);
        $subSheet->setCellValue('E' . $row_sub, $data_sub['tanggal']);
        $subSheet->setCellValue('F' . $row_sub, $data_sub['waktu']);
        $subSheet->setCellValue('G' . $row_sub, $data_sub['status']);
        $row_sub++;
    }

    // Terapkan gaya tabel pada sub sheet
    $subSheet->getStyle('A4:G' . ($row_sub - 1))->applyFromArray($headerStyle);
    foreach (range('A', 'G') as $columnID) {
        $subSheet->getColumnDimension($columnID)->setAutoSize(true);
    }
}

// Set auto size untuk kolom di main sheet
foreach (range('A', 'G') as $columnID) {
    $mainSheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Menulis file Excel
$writer = new Xlsx($spreadsheet);
$filename = 'Data_Siswa_Absensi.xlsx';

// Set header untuk file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Output file ke browser
$writer->save('php://output');

// Tutup koneksi
$conn->close();
