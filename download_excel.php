<?php
// ตรวจสอบให้แน่ใจว่าไม่มีการพิมพ์ข้อความออกมาก่อน header
ob_start(); // เริ่มต้น Output Buffering

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require_once 'db.php';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// ตั้งค่า header ของไฟล์ Excel
$sheet->setCellValue('A1', 'เลขทะเบียนหนังสือ');
$sheet->setCellValue('B1', 'ชื่อ-สกุล');
$sheet->setCellValue('C1', 'วิทยาลัย');
$sheet->setCellValue('D1', 'วัน / เดือน / ปี คณะรับเล่มผลงานทางวิชาการ');
$sheet->setCellValue('E1', 'วัน / เดือน / ปี ผ่านอนุกรรมการตรวจสอบ');
$sheet->setCellValue('F1', 'วัน / เดือน / ปี ผ่านคณะกรรมการประจำ');
$sheet->setCellValue('G1', 'เลขที่หนังสือ นำส่งทรัพยากรบุคคล');
$sheet->setCellValue('H1', 'ผ่านมติสภาสถาบัน พระบรมราชชนก');

$sql = "SELECT * FROM faculty_progress";
$result = $conn->query($sql);
$rowIndex = 2;

while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowIndex, $row['registration_number']);
    $sheet->setCellValue('B' . $rowIndex, $row['fullname']);
    $sheet->setCellValue('C' . $rowIndex, $row['college']);
    $sheet->setCellValue('D' . $rowIndex, !empty($row['date_faculty_received']) && $row['date_faculty_received'] !== '0000-00-00' ? date('d/m/Y', strtotime($row['date_faculty_received'])) : '-');
    $sheet->setCellValue('E' . $rowIndex, !empty($row['committee_approval_date']) && $row['committee_approval_date'] !== '0000-00-00' ? date('d/m/Y', strtotime($row['committee_approval_date'])) : '-');
    $sheet->setCellValue('F' . $rowIndex, !empty($row['faculty_approval_date']) && $row['faculty_approval_date'] !== '0000-00-00' ? date('d/m/Y', strtotime($row['faculty_approval_date'])) : '-');
    $sheet->setCellValue('G' . $rowIndex, $row['book_number_HR']);
    $sheet->setCellValue('H' . $rowIndex, $row['passed_institution']);
    $rowIndex++;
}

// ตั้งค่า headers เพื่อให้ไฟล์ Excel ถูกดาวน์โหลด
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="faculty_progress.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// ปิดการทำงานของ output buffering หลังจากส่งไฟล์
ob_end_flush();
exit;
?>
