<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | Smart Pet Feeder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Kanit', sans-serif; background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%); min-height: 100vh; display: flex; align-items: center; margin: 0; overflow-x: hidden; }
        .login-card { border: none; border-radius: 30px; box-shadow: 0 15px 35px rgba(186, 230, 253, 0.5); width: 100%; max-width: 420px; margin: auto; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); overflow: hidden; position: relative; z-index: 10; }
        .login-header { background: linear-gradient(135deg, #7dd3fc 0%, #0ea5e9 100%); padding: 40px 20px; text-align: center; color: white; }
        .form-control { border-radius: 12px; padding: 12px; border: 1px solid #e0f2fe; background-color: #f8fafc; }
        .form-control:focus { box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.15); border-color: #7dd3fc; }
        .btn-blue { background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%); border: none; border-radius: 15px; padding: 12px; font-weight: 600; color: white; transition: 0.3s; box-shadow: 0 5px 15px rgba(14, 165, 233, 0.3); }
        .btn-blue:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(14, 165, 233, 0.4); color: white; filter: brightness(1.1); }
        .text-blue { color: #0ea5e9; }

        /* --- สไตล์สำหรับน้องสัตว์วิ่งพื้นหลัง --- */
        #bg-playground { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; pointer-events: none; }
        .bg-pet { position: absolute; font-size: 60px; right: -120px; filter: opacity(0.12); animation: walk-left linear infinite; }
        @keyframes walk-left { from { transform: translateX(0); } to { transform: translateX(calc(-100vw - 250px)); } }
        .wiggle { display: inline-block; animation: move-wiggle 0.5s ease-in-out infinite alternate; }
        @keyframes move-wiggle { from { transform: rotate(-8deg) translateY(0); } to { transform: rotate(8deg) translateY(-12px); } }
    </style>
</head>
<body>
    <div id="bg-playground"></div>

    <div class="container p-3">
        <div class="card login-card shadow">
            <div class="login-header">
                <i class="fa-solid fa-paw fa-3x mb-3"></i>
                <h2 class="fw-bold m-0">เข้าสู่ระบบ</h2>
                <p class="small mb-0 opacity-75">Smart Pet Feeder System</p>
            </div>
            <div class="card-body p-4 p-md-5">
                <form action="auth_process.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">ชื่อผู้ใช้</label>
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">รหัสผ่าน</label>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-blue w-100 py-3">เข้าสู่ระบบ</button>
                </form>
                <div class="text-center mt-4">
                    <p class="text-muted small">ยังไม่มีบัญชี? <a href="register.php" class="text-blue text-decoration-none fw-bold">สมัครสมาชิก</a></p>
                    <a href="forgot.php" class="text-blue text-decoration-none fw-bold">ลืมรหัสผ่าน?</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const playground = document.getElementById('bg-playground');
        const petIcons = ['🐈', '🐕', '🐩', '🐈‍⬛','🦮'];
        const totalPets = 12; // จำนวนน้องๆ ที่วิ่ง

        for (let i = 0; i < totalPets; i++) {
            const petDiv = document.createElement('div');
            petDiv.className = 'bg-pet';
            petDiv.style.top = Math.random() * 90 + 'vh'; 
            petDiv.style.animationDuration = (Math.random() * 10 + 10) + 's'; 
            petDiv.style.animationDelay = (Math.random() * 10) + 's'; 
            petDiv.innerHTML = `<span class="wiggle">${petIcons[Math.floor(Math.random() * petIcons.length)]}</span>`;
            playground.appendChild(petDiv);
        }
    </script>
</body>
</html>