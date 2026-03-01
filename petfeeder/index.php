<?php 
session_start();
require_once 'db_config.php'; 

// --- 🔒 ระบบความปลอดภัย: ถ้ายังไม่ได้ Login ให้ดีดกลับไปหน้า login.php ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Pet Feeder | Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Kanit', sans-serif; background: #f0f9ff; color: #334155; margin: 0; overflow-x: hidden; }
        .navbar { background: linear-gradient(135deg, #7dd3fc 0%, #0ea5e9 100%); box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2); position: relative; z-index: 10; }
        .card { border: none; border-radius: 30px; box-shadow: 0 10px 25px rgba(186, 230, 253, 0.3); background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); transition: 0.3s; }
        .card:hover { transform: translateY(-5px); }
        .gauge-card { padding: 30px 20px; position: relative; }
        .gauge-center-content { position: absolute; top: 58%; left: 50%; transform: translate(-50%, -50%); text-align: center; width: 100%; }
        .icon-bone { color: #38bdf8; } 
        .icon-water { color: #0ea5e9; }
        .gauge-value { font-size: 2.5rem; font-weight: 700; line-height: 1; }
        .btn-feed { background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%); border: none; border-radius: 20px; padding: 18px 40px; font-weight: 600; color: white; transition: 0.3s; font-size: 1.1rem; }
        .btn-water { background: linear-gradient(135deg, #0ea5e9 0%, #035683 100%); border: none; border-radius: 20px; padding: 18px 40px; font-weight: 600; color: white; transition: 0.3s; font-size: 1.1rem; }
        .btn-feed:hover, .btn-water:hover { transform: scale(1.05); color: white; box-shadow: 0 8px 25px rgba(14, 165, 233, 0.4); }
        .status-badge { font-size: 0.8rem; padding: 8px 15px; border-radius: 50px; background: rgba(255,255,255,0.2); color: white; }
        #bg-playground { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; pointer-events: none; }
        .bg-pet { position: absolute; font-size: 60px; right: -120px; filter: opacity(0.12); animation: walk-left linear infinite; }
        @keyframes walk-left { from { transform: translateX(0); } to { transform: translateX(calc(-100vw - 250px)); } }
        .wiggle { display: inline-block; animation: move-wiggle 0.5s ease-in-out infinite alternate; }
        @keyframes move-wiggle { from { transform: rotate(-8deg) translateY(0); } to { transform: rotate(8deg) translateY(-12px); } }
    </style>
</head>
<body>
    <div id="bg-playground"></div>

    <nav class="navbar navbar-dark py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="navbar-brand fw-bold mb-0 h1"><i class="fa-solid fa-paw me-2"></i> SMART PET FEEDER</span>
            <div class="d-flex align-items-center gap-3">
                <div class="status-badge d-none d-md-block"><i class="fa-solid fa-user me-1"></i> <?php echo $_SESSION['username']; ?></div>
                <div class="status-badge"><i class="fa-solid fa-circle-check me-1"></i> ระบบออนไลน์</div>
                <a href="logout.php" class="btn btn-danger btn-sm rounded-pill px-3"><i class="fa-solid fa-right-from-bracket"></i> ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row g-3 mb-4 text-center">
            <div class="col-md-6">
                <div class="card p-3 border-0 shadow-sm" style="background: rgba(56, 189, 248, 0.1);">
                    <h6 class="text-muted mb-1">วันนี้ให้สะสม</h6>
                    <h3 class="fw-bold text-primary mb-0"><span id="daily-feed-val">0</span> ครั้ง</h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3 border-0 shadow-sm" style="background: rgba(14, 165, 233, 0.1);">
                    <h6 class="text-muted mb-1">วันนี้ให้น้ำสะสม</h6>
                    <h3 class="fw-bold text-info mb-0"><span id="daily-water-val">0</span> ครั้ง</h3>
                </div>
            </div>
        </div>

        <div class="row g-4 justify-content-center text-center">
            <div class="col-lg-5 col-md-6">
                <div class="card gauge-card">
                    <h5 class="fw-bold mb-4 text-muted">ระดับอาหารคงเหลือ</h5>
                    <div style="width: 250px; margin: 0 auto; position: relative;">
                        <canvas id="foodChart"></canvas>
                        <div class="gauge-center-content">
                            <i class="fa-solid fa-bone gauge-icon icon-bone"></i>
                            <div id="food-text" class="gauge-value" style="color: #38bdf8;">0</div>
                            <div class="gauge-unit">PERCENT</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-6">
                <div class="card gauge-card">
                    <h5 class="fw-bold mb-4 text-muted">ระดับน้ำในถัง</h5>
                    <div style="width: 250px; margin: 0 auto; position: relative;">
                        <canvas id="waterChart"></canvas>
                        <div class="gauge-center-content">
                            <i class="fa-solid fa-droplet gauge-icon icon-water"></i>
                            <div id="water-text" class="gauge-value" style="color: #0ea5e9;">0</div>
                            <div class="gauge-unit">PERCENT</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5 mb-5">
            <div class="col-12 text-center">
                <div class="card p-5 border-0 shadow-sm" style="border-radius: 40px;">
                    <h4 class="mb-4 fw-bold text-dark"><i class="fa-solid fa-gamepad me-2 text-primary"></i>ศูนย์ควบคุมการสั่งการ</h4>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <button onclick="sendControl('feed')" id="btn-feed-action" class="btn btn-feed shadow-sm">
                            <i class="fa-solid fa-utensils me-2"></i> จ่ายอาหาร
                        </button>
                        <button onclick="sendControl('water')" id="btn-water-action" class="btn btn-water shadow-sm">
                            <i class="fa-solid fa-faucet-drip me-2"></i> จ่ายน้ำ
                        </button>
                    </div>
                    <div class="mt-4 text-muted small">
                        <i class="fa-solid fa-clock me-1"></i> ซิงค์ข้อมูลล่าสุด: <span id="time-val">-</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mb-5">
            <div class="col-lg-12">
                <div class="card p-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold m-0 text-dark"><i class="fa-solid fa-chart-area me-2 text-info"></i>สถิติการทำงานย้อนหลัง (7 วัน)</h5>
                        <button class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="updateUI()"><i class="fa-solid fa-sync me-1"></i> รีเฟรช</button>
                    </div>
                    <div style="height: 350px;">
                        <canvas id="historyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button onclick="reset_wifi()" style="background-color: red; color: white;">
    Reset Device WiFi
    </button>

    <script>
        /* --- 1. สุ่มน้องสัตว์เลี้ยงวิ่งพื้นหลัง --- */
        const playground = document.getElementById('bg-playground');
        const petIcons = ['🐈', '🐕', '🐩', '🐈‍⬛','🦮'];
        for (let i = 0; i < 12; i++) {
            const petDiv = document.createElement('div');
            petDiv.className = 'bg-pet';
            petDiv.style.top = Math.random() * 90 + 'vh';
            petDiv.style.animationDuration = (Math.random() * 10 + 10) + 's';
            petDiv.style.animationDelay = (Math.random() * 10) + 's';
            petDiv.innerHTML = `<span class="wiggle">${petIcons[Math.floor(Math.random() * petIcons.length)]}</span>`;
            playground.appendChild(petDiv);
        }

        /* --- 2. สร้างเข็มไมล์ (Gauge) --- */
        function createGauge(id, color) {
            return new Chart(document.getElementById(id), {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [0, 100],
                        backgroundColor: [color, '#f1f5f9'],
                        borderWidth: 0, circumference: 240, rotation: 240, borderRadius: 20
                    }]
                },
                options: { cutout: '88%', plugins: { tooltip: { enabled: false }, legend: { display: false } } }
            });
        }
        const foodChart = createGauge('foodChart', '#38bdf8');
        const waterChart = createGauge('waterChart', '#0b71a0');

        /* --- 3. สร้างกราฟเส้น (History) --- */
        const historyChart = new Chart(document.getElementById('historyChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: [], 
                datasets: [
                    { label: 'อาหาร (ครั้ง)', data: [], borderColor: '#38bdf8', backgroundColor: 'rgba(56, 189, 248, 0.1)', fill: true, tension: 0.4 },
                    { label: 'น้ำ (ครั้ง)', data: [], borderColor: '#0b71a0', backgroundColor: 'rgba(14, 165, 233, 0.1)', fill: true, tension: 0.4 }
                ]
            },
            options: { 
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { family: 'Kanit' } } } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
            }
        });

        /* --- 4. ฟังก์ชันดึงข้อมูลจาก API --- */
         function updateUI() {
            fetch('api.php?get_latest=1')
                .then(r => r.json())
                .then(data => {
                    if(data) {
                        // อาหาร
                        let foodVal = Math.round(parseFloat(data.food_level));
                        let foodPercent = Math.max(0, Math.min(100, foodVal));
                        document.getElementById('food-text').innerText = foodPercent;
                        foodChart.data.datasets[0].data = [foodPercent, 100 - foodPercent];
                        foodChart.update();

                        // น้ำ (ใช้เลข 42 หรือค่าจริงจาก DB มาวาดกราฟ)
                        let waterVal = Math.round(parseFloat(data.water_level));
                        let waterShow = Math.max(0, Math.min(100, waterVal)); // ป้องกันค่าเกิน 100 สำหรับกราฟ
                        
                        document.getElementById('water-text').innerText = waterVal;
                        // อัปเดตข้อมูลใน Gauge ให้น้ำขยับตามตัวเลข
                        waterChart.data.datasets[0].data = [waterShow, 100 - waterShow];
                        waterChart.update();

                         // แสดงจำนวนครั้งสะสมรายวันจากแถวล่าสุด
                        document.getElementById('daily-feed-val').innerText = data.daily_feed_count;
                        document.getElementById('daily-water-val').innerText = data.daily_water_count;

                        document.getElementById('time-val').innerText = data.log_time;
                    }
                });

            fetch('api.php?get_stats=1').then(r => r.json()).then(stats => {
                if(stats && stats.labels) {
                    historyChart.data.labels = stats.labels;
                    historyChart.data.datasets[0].data = stats.food_fills;
                    historyChart.data.datasets[1].data = stats.water_fills;
                    historyChart.update();
                }
            });
        }

        /* --- 5. ฟังก์ชันส่งคำสั่งควบคุม --- */
        function sendControl(type) {
    const btnId = (type === 'feed') ? 'btn-feed-action' : 'btn-water-action';
    const btn = document.getElementById(btnId);
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin me-2"></i> กำลังสั่งการ...`;

    // เปลี่ยนจาก fetch(`api.php?cmd=${type}`) เป็นการตรวจสอบผลลัพธ์ด้วย
    fetch(`api.php?cmd=${type}`)
        .then(r => r.json())
        .then(data => {
            console.log("Command sent:", data);
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = original;
                updateUI();
            }, 2000);
        }).catch(err => {
            console.error("Error:", err);
            btn.disabled = false;
            btn.innerHTML = original;
        });
}

/* --- แก้ไขฟังก์ชัน resetWifi --- */
function reset_wifi() {
    if (confirm("คุณแน่ใจหรือไม่ที่จะรีเซ็ตการตั้งค่า WiFi? บอร์ดจะออฟไลน์ทันที")) {
        // *** แก้ไขตรงนี้: จาก set_command เป็น cmd เพื่อให้ตรงกับ api.php ***
        fetch('api.php?cmd=reset_wifi') 
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert("ส่งคำสั่งรีเซ็ตแล้ว รออุปกรณ์ดำเนินการภายใน 5-10 วินาที");
                }
            });
    }
}

        setInterval(updateUI, 10000); 
        updateUI();
    </script>
</body>
</html>