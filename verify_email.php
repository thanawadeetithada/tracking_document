<?php
include('db.php');

if (!isset($_GET['token'])) {
    die("❌ ไม่พบโทเค็นยืนยันอีเมล");
}

$token = $_GET['token'];
$stmt = $conn->prepare("SELECT email FROM users WHERE verification_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("❌ โทเค็นไม่ถูกต้อง หรือหมดอายุ");
}

// อัปเดตสถานะเป็นยืนยันแล้ว
$stmt = $conn->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE verification_token = ?");
$stmt->bind_param("s", $token);

if ($stmt->execute()) {
    echo "<script>
            alert('✅ ยืนยันอีเมลสำเร็จ!'); 
            window.location.href='index.php';
          </script>";
} else {
    echo "<script>alert('❌ เกิดข้อผิดพลาดในการยืนยันอีเมล');</script>";
}

$conn->close();
?>
