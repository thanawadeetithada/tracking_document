<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {    //เพิ่มข้อมูลลง database การทำงานท้งหมดของ ปุ่มเพิ่มข้อมูล
    $registration_number = $_POST['registration_number'];
    $fullname = $_POST['fullname'];
    $college = $_POST['college'];
    $date_faculty_received = $_POST['date_faculty_received'];
    $committee_approval_date = $_POST['committee_approval_date'];
    $faculty_approval_date = $_POST['faculty_approval_date'];
    $book_number_HR = $_POST['book_number_HR'];
    $passed_institution = $_POST['passed_institution'];

    $sql = "INSERT INTO faculty_progress (registration_number, fullname, college, date_faculty_received, committee_approval_date, faculty_approval_date, book_number_HR, passed_institution)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssssss", $registration_number, $fullname, $college, $date_faculty_received, $committee_approval_date, $faculty_approval_date, $book_number_HR, $passed_institution);

        if ($stmt->execute()) {
            header("Location: dashboard.php");   //กดตกลงแล้วไปหน้า dashboard (ตารางข้อมูล)
            exit();
        } else {
            echo "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "ไม่สามารถเตรียมคำสั่ง SQL ได้: " . $conn->error;
    }
}

$conn->close();
?>
