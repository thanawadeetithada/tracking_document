<?php
session_start();

if (isset($_POST['id']) && isset($_SESSION['user_id'])) {
    $id = $_POST['id'];
    $loggedInUserId = $_SESSION['user_id'];

    require_once 'db.php';

    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        if ($id == $loggedInUserId) {
            echo 'success';  // ถ้าลบผู้ใช้ที่ล็อกอิน
        } else {
            echo 'user_deleted';  // ถ้าลบผู้ใช้อื่น
        }
    } else {
        echo 'error';  // ถ้าเกิดข้อผิดพลาดในการลบ
    }

    $stmt->close();
    $conn->close();
}
?>
