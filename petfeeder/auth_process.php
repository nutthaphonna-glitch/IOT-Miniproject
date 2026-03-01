<?php
session_start();
require_once 'db_config.php';

// --- ส่วนที่ 1: การเข้าสู่ระบบ (Login) ---
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // ปรับให้ตรงตามโครงสร้างภาพ: id, username, password
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php"); 
            exit(); 
        } else {
            echo "<script>alert('รหัสผ่านไม่ถูกต้อง!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('ไม่พบชื่อผู้ใช้นี้!'); window.history.back();</script>";
    }
}

// --- ส่วนที่ 2: การสมัครสมาชิก (Register) ---
if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo "<script>alert('รหัสผ่านไม่ตรงกัน!'); window.history.back();</script>";
        exit();
    }

    // ตรวจสอบเฉพาะ username (เนื่องจากในภาพไม่มีคอลัมน์ email)
    $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
    if ($check->num_rows > 0) {
        echo "<script>alert('ชื่อผู้ใช้ี้มีในระบบแล้ว!'); window.history.back();</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // ปรับคำสั่ง INSERT ให้ตรงกับคอลัมน์ในภาพ: username, password
        // คอลัมน์ created_at มักจะเป็น CURRENT_TIMESTAMP อัตโนมัติ
        $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";
        
        if ($conn->query($sql)) {
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['username'] = $username;

            echo "<script>
                    alert('สมัครสมาชิกสำเร็จ!');
                    window.location.href = 'index.php'; 
                  </script>";
            exit();
        } else {
            // หากเกิดข้อผิดพลาด ให้พ่น error ออกมาดู
            echo "Error: " . $conn->error;
        }
    }
}
?>