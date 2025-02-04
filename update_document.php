<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $registration_number = $_POST['registration_number'];
    $fullname = $_POST['fullname'];
    $college = $_POST['college'];
    $date_faculty_received = $_POST['date_faculty_received'];
    $committee_approval_date = $_POST['committee_approval_date'];
    $faculty_approval_date = $_POST['faculty_approval_date'];
    $book_number_HR = $_POST['book_number_HR'];
    $passed_institution = $_POST['passed_institution'];

    $sql = "UPDATE faculty_progress SET registration_number = ?, fullname = ?, college = ?, date_faculty_received = ?, committee_approval_date = ?, faculty_approval_date = ?, book_number_HR = ?, passed_institution = ? WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $registration_number, $fullname, $college, $date_faculty_received, $committee_approval_date, $faculty_approval_date, $book_number_HR, $passed_institution, $id);

    if ($stmt->execute()) {
        header("Location: index.php");
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>
