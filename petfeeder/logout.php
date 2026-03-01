<?php
session_start();
// ทำลาย Session ทั้งหมด
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ออกจากระบบ | Smart Pet Feeder</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { 
            font-family: 'Kanit', sans-serif; 
            background: #f0f9ff; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            overflow: hidden;
            margin: 0;
        }
        
        .logout-card { 
            border: none;
            border-radius: 30px; 
            background: rgba(255, 255, 255, 0.9); 
            backdrop-filter: blur(10px);
            padding: 50px; 
            text-align: center; 
            box-shadow: 0 15px 35px rgba(14, 165, 233, 0.2); 
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 400px;
        }

        .icon-box {
            font-size: 4rem;
            color: #0ea5e9;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-20px);}
            60% {transform: translateY(-10px);}
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.25em;
            color: #38bdf8 !important;
        }

        /* --- น้องสัตว์เลี้ยงวิ่งพื้นหลัง (ธีม Dashboard) --- */
        #bg-playground { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 1; pointer-events: none; }
        .bg-pet { position: absolute; font-size: 50px; right: -120px; filter: opacity(0.15); animation: walk-left linear infinite; }
        @keyframes walk-left { from { transform: translateX(0); } to { transform: translateX(calc(-100vw - 250px)); } }
        .wiggle { display: inline-block; animation: move-wiggle 0.5s ease-in-out infinite alternate; }
        @keyframes move-wiggle { from { transform: rotate(-8deg) translateY(0); } to { transform: rotate(8deg) translateY(-10px); } }
    </style>
</head>
<body>
    <div id="bg-playground"></div>

    <div class="logout-card">
        <div class="icon-box">
            <i class="fa-solid fa-door-open"></i>
        </div>
        <h3 class="fw-bold text-dark mb-3">ออกจากระบบสำเร็จ</h3>
        <p class="text-muted mb-4">ขอบคุณที่ใช้บริการค่ะ <br>กำลังพากลับไปหน้าล็อกอิน...</p>
        
        <div class="spinner-border text-info mb-2" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script>
        /* --- น้องสัตว์เลี้ยงวิ่งพื้นหลัง --- */
        const pg = document.getElementById('bg-playground');
        const pets = ['🐈', '🐕', '🐩', '🐾', '🦴'];
        for (let i = 0; i < 8; i++) {
            const d = document.createElement('div');
            d.className = 'bg-pet';
            d.style.top = Math.random() * 90 + 'vh';
            d.style.animationDuration = (Math.random() * 10 + 10) + 's';
            d.style.animationDelay = (Math.random() * 5) + 's';
            d.innerHTML = `<span class="wiggle">${pets[Math.floor(Math.random() * pets.length)]}</span>`;
            pg.appendChild(d);
        }

        // รอ 2.5 วินาทีแล้วไปหน้า Login
        setTimeout(() => { 
            window.location.href = 'login.php'; 
        }, 2500);
    </script>
</body>
</html>