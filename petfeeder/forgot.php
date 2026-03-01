<?php
require_once 'db_config.php';
$message = "";
if (isset($_POST['check_email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $res = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($row = $res->fetch_assoc()) {
        $message = "<div class='alert alert-success'>พบข้อมูล! <a href='reset_password.php?id=".$row['id']."' class='btn btn-sm btn-success d-block mt-2'>ตั้งรหัสผ่านใหม่ที่นี่</a></div>";
    } else {
        $message = "<div class='alert alert-danger'>ไม่พบอีเมลนี้ในระบบ</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>กู้คืนรหัสผ่าน | Smart Pet Feeder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Kanit', sans-serif; background: #f0f9ff; height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; margin: 0; }
        .forgot-card { border-radius: 30px; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); padding: 40px; width: 400px; box-shadow: 0 15px 35px rgba(245, 158, 11, 0.1); z-index: 10; }
        .btn-send { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none; border-radius: 15px; color: white; padding: 12px; width: 100%; font-weight: 600; }
        #bg-playground { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 1; pointer-events: none; }
        .bg-pet { position: absolute; font-size: 45px; right: -120px; filter: opacity(0.12); animation: walk-left linear infinite; }
        @keyframes walk-left { from { transform: translateX(0); } to { transform: translateX(calc(-100vw - 250px)); } }
    </style>
</head>
<body>
    <div id="bg-playground"></div>
    <div class="forgot-card text-center">
        <div class="mb-3 text-warning" style="font-size: 3rem;"><i class="fa-solid fa-key"></i></div>
        <h4 class="fw-bold mb-4">ลืมรหัสผ่าน?</h4>
        <?php echo $message; ?>
        <form method="POST">
            <div class="mb-4 text-start">
                <label class="small ms-2 text-muted">อีเมลที่ลงทะเบียน</label>
                <input type="email" name="email" class="form-control rounded-pill px-3" placeholder="example@mail.com" required>
            </div>
            <button type="submit" name="check_email" class="btn btn-send mb-3">ตรวจสอบข้อมูล</button>
            <a href="login.php" class="text-decoration-none small text-muted"><i class="fa-solid fa-arrow-left me-1"></i> กลับไปหน้าล็อกอิน</a>
        </form>
    </div>
    <script>
        const pg = document.getElementById('bg-playground');
        const icons = ['🦴', '🐾', '🐈', '🐕'];
        for (let i = 0; i < 6; i++) {
            const d = document.createElement('div');
            d.className = 'bg-pet';
            d.style.top = Math.random() * 90 + 'vh';
            d.style.animationDuration = (Math.random() * 10 + 12) + 's';
            d.innerHTML = icons[Math.floor(Math.random() * icons.length)];
            pg.appendChild(d);
        }
    </script>
</body>
</html>