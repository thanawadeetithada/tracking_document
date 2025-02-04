<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $userrole = $_POST['userrole'];
    
    $sql = "UPDATE users SET name = ?, email = ?, userrole = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $email, $userrole, $id);
    
    if ($stmt->execute()) {
        header("Location: user_management.php");
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>