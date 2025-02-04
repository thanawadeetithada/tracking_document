<?php
// เรียกใช้ autoload ของ Composer (ถ้าติดตั้งผ่าน Composer)
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// เชื่อมต่อกับฐานข้อมูล (ตรวจสอบว่าใช้การเชื่อมต่อที่ถูกต้อง)
require_once 'db.php';

// สร้างไฟล์ Excel ใหม่
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// ตั้งชื่อคอลัมน์ใน Excel (หัวตาราง)
$sheet->setCellValue('A1', 'เลขทะเบียนหนังสือ');
$sheet->setCellValue('B1', 'ชื่อ-สกุล');
$sheet->setCellValue('C1', 'วิทยาลัย');
$sheet->setCellValue('D1', 'วัน / เดือน / ปี คณะรับเล่มผลงานทางวิชาการ');
$sheet->setCellValue('E1', 'วัน / เดือน / ปี ผ่านอนุกรรมการตรวจสอบ');
$sheet->setCellValue('F1', 'วัน / เดือน / ปี ผ่านคณะกรรมการประจำ');
$sheet->setCellValue('G1', 'เลขที่หนังสือ นำส่งทรัพยากรบุคคล');
$sheet->setCellValue('H1', 'ผ่านมติสภาสถาบัน พระบรมราชชนก');

// ดึงข้อมูลจากฐานข้อมูล
$sql = "SELECT * FROM faculty_progress";
$result = $conn->query($sql);
$rowIndex = 2; // เริ่มต้นจากแถวที่ 2 หลังจากหัวตาราง

// เพิ่มข้อมูลลงใน Excel
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

// ตั้งค่า Header สำหรับการดาวน์โหลดไฟล์ Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="faculty_progress.xlsx"');
header('Cache-Control: max-age=0');

// เขียนไฟล์ Excel และดาวน์โหลด
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
