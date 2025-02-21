<?php
include('db.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {    
    $prefix = $_POST['prefix'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password != $confirm_password) {  
        $error_message = "รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน"; 
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // ตรวจสอบว่าอีเมลนี้ถูกใช้งานแล้วหรือไม่
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "อีเมลนี้มีผู้ใช้งานแล้ว";
        } else {
            $userrole = 'user';
            $verification_token = bin2hex(random_bytes(32)); // สร้างโทเค็นสุ่ม

            // ส่งค่า email_verified เป็น 0 โดยตรง
            $stmt = $conn->prepare("INSERT INTO users (prefix, fullname, email, password, userrole, email_verified, verification_token) VALUES (?, ?, ?, ?, ?, 0, ?)");
            $stmt->bind_param("ssssss", $prefix, $fullname, $email, $hashed_password, $userrole, $verification_token);

            if ($stmt->execute()) {
                if (sendVerificationEmail($email, $verification_token)) { // ส่งอีเมลยืนยัน
                    header("Location: index.php?success=1");
                    exit();
                } else {
                    $error_message = "เกิดข้อผิดพลาดในการส่งอีเมลยืนยัน กรุณาลองใหม่";
                }
            } else {
                $error_message = "เกิดข้อผิดพลาดในการลงทะเบียน กรุณาลองใหม่";
            }
        }
        $stmt->close();
    }
    $conn->close();
}

// ✅ ฟังก์ชันส่งอีเมลยืนยัน (ปรับปรุงใหม่)
function sendVerificationEmail($email, $token) {
    $mail = new PHPMailer(true);
    try {
        // ตั้งค่า SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // ใช้ Gmail SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'mitinventor015@gmail.com'; // 🔹 เปลี่ยนเป็นอีเมลของคุณ
        $mail->Password = 'etptordrjdzhhsas'; // 🔹 ใช้ App Password แทนรหัสผ่านจริง
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // ตั้งค่าผู้ส่งและผู้รับ
        $mail->setFrom('mitinventor015@gmail.com', 'ระบบติดตามความก้าวหน้าตำแหน่งทางวิชาการ'); 
        $mail->addAddress($email);

        // ✅ ใช้ `urlencode()` ป้องกันปัญหาลิงก์เสีย
        $verification_link = "http://localhost/tracking_document/verify_email.php?token=" . urlencode($token);

        // ตั้งค่าอีเมล
        $mail->isHTML(true);
        $mail->CharSet = "UTF-8"; // รองรับภาษาไทย
        $mail->Subject = "ยืนยันอีเมลของคุณ";
        $mail->Body = "<p>สวัสดี,</p>
                       <p>กรุณาคลิกลิงก์ด้านล่างเพื่อยืนยันอีเมลของคุณ:</p>
                       <p><a href='$verification_link'>$verification_link</a></p>
                       <p>หากคุณไม่ได้สมัครใช้งาน โปรดละเว้นอีเมลนี้</p>";

        // ✅ ส่งอีเมลและเช็คผลลัพธ์
        if ($mail->send()) {
            return true; // ส่งอีเมลสำเร็จ
        } else {
            error_log("❌ ไม่สามารถส่งอีเมลได้: {$mail->ErrorInfo}"); // บันทึก Log error
            return false;
        }
    } catch (Exception $e) {
        error_log("❌ PHPMailer Error: {$e->getMessage()}"); // บันทึก Log error
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบติดตาม</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
    * {
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f9fafc;
    }

    .login-container {
        background-color: #ffffff;
        padding: 2rem;
        width: 90%;
        max-width: 500px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        transition: box-shadow 0.3s ease;
    }

    .login-container:hover {
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);
    }

    p {
        margin-top: 15px;
        font-size: 0.9rem;
        color: #000;
    }

    .login-title {
        color: #000;
        font-size: 2rem;
        margin-bottom: 2rem;
        text-align: center;
        width: 100%;
        padding-left: 20px;

    }

    .login-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100vh;
        justify-content: center;
    }

    .form-group {
        text-align: justify;
    }

    form button {
        width: 100%;
        margin-top: 15px;
    }

    .login-wrapper i {
        font-size: -webkit-xxx-large;
        color: #045cbb;
    }

    .alert-danger {
        font-size: 1.2rem;
        background-color: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
    }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <i class="fa-regular fa-file-lines"></i>
        <br>
        <h2 class="login-title">ลงทะเบียนผู้ใช้งานใหม่</h2>
        <div class="login-container">
            <!-- โชว์ error จากที่เขียนเช็คไว้ บรรทัด 11, 21-->
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="prefix">คำนำหน้าชื่อ</label>
                    <select class="form-control" id="prefix" name="prefix" required>
                        <option value="">เลือกคำนำหน้า</option>
                        <option value="นาย" <?php echo (isset($prefix) && $prefix == "นาย") ? "selected" : ""; ?>>นาย
                        </option>
                        <option value="นาง" <?php echo (isset($prefix) && $prefix == "นาง") ? "selected" : ""; ?>>นาง
                        </option>
                        <option value="นางสาว" <?php echo (isset($prefix) && $prefix == "นางสาว") ? "selected" : ""; ?>>
                            นางสาว</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fullname">ชื่อ-สกุล</label>
                    <input type="text" class="form-control" id="fullname" name="fullname" required
                        value="<?php echo isset($fullname) ? $fullname : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="email">อีเมล</label>
                    <input type="email" class="form-control" id="email" name="email" required
                        value="<?php echo isset($email) ? $email : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="password">รหัสผ่าน</label>
                    <input type="password" class="form-control" id="password" name="password" required value="">
                </div>
                <div class="form-group">
                    <label for="confirm_password">ยืนยันรหัสผ่าน</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                        value="">
                </div>
                <!-- ปุ่มเข้าสู่ระบบ -->
                <button type="submit" class="btn btn-primary">ลงทะเบียน</button>
            </form>

            <p>
                <a href="index.php">มีบัญชีใช้แล้ว? เข้าสู่ระบบ</a>
            </p>
        </div>
    </div>
</body>

</html>