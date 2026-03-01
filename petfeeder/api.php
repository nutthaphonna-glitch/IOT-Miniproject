<?php
require_once 'db_config.php';
header('Content-Type: application/json');

// --- ⚙️ ส่วนตั้งค่า Telegram (คงเดิม) ---
$telegramToken = "8751733107:AAG1cSxWdfvDEMcaZ8blyubs3FeobnDw2NY"; 
$chatId = "8579434234"; 

function sendTelegram($message, $token, $chat) {
    $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chat . "&text=" . urlencode($message);
    @file_get_contents($url);
}

// --- 1. ส่วนรับคำสั่งจากปุ่มหน้า Dashboard (GET cmd) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['cmd'])) {
    $command = $_GET['cmd'];
    
    // อัปเดตสถานะในตาราง control_panel เป็น pending เพื่อให้ ESP32 มาดึงไปทำงาน
    $stmt = $conn->prepare("UPDATE control_panel SET status = 'pending' WHERE command = ?");
    $stmt->bind_param("s", $command);
    
    if ($stmt->execute()) {
        // --- กรณีสั่ง Reset WiFi ---
        if ($command === 'reset_wifi') {
            sendTelegram("⚠️ แจ้งเตือน: มีการสั่ง 'รีเซ็ต WiFi' ผ่านหน้า Dashboard! อุปกรณ์จะเริ่มใหม่เข้าโหมด Config 🔌", $telegramToken, $chatId);
            echo json_encode(["status" => "success", "message" => "WiFi Reset command is pending"]);
        } 
        // --- กรณีสั่งจ่ายอาหาร หรือ จ่ายน้ำ ---
        else if ($command === 'feed' || $command === 'water') {
            // คำนวณสถิติรายวัน
            $res = $conn->query("SELECT MAX(daily_feed_count) as f, MAX(daily_water_count) as w FROM pet_feeder_logs WHERE DATE(log_time) = CURDATE()");
            $row = $res->fetch_assoc();
            $current_f = (int)$row['f']; 
            $current_w = (int)$row['w'];
            
            if ($command === 'feed') $current_f++;
            if ($command === 'water') $current_w++;

            // ดึงค่าระดับล่าสุดมาบันทึกคู่กัน
            $last = $conn->query("SELECT food_level, water_level FROM pet_feeder_logs ORDER BY log_time DESC LIMIT 1")->fetch_assoc();
            $f_lv = $last['food_level'] ?? 0; 
            $w_lv = $last['water_level'] ?? 0;

            $stmt_log = $conn->prepare("INSERT INTO pet_feeder_logs (food_level, water_level, event_type, daily_feed_count, daily_water_count) VALUES (?, ?, ?, ?, ?)");
            $stmt_log->bind_param("ddsii", $f_lv, $w_lv, $command, $current_f, $current_w);
            $stmt_log->execute();

            $emoji = ($command == 'feed') ? "🍴" : "💧";
            $text = ($command == 'feed') ? "อาหาร" : "น้ำ";
            sendTelegram("$emoji แจ้งเตือน: มีการสั่งจ่าย '$text' ผ่านหน้า Dashboard! 🦴", $telegramToken, $chatId);
            
            echo json_encode(["status" => "success", "command" => $command, "count" => ($command == 'feed' ? $current_f : $current_w)]);
        }
    }
    exit;
}

// --- 2. ส่วนที่ ESP32 จะมาดึงคำสั่งไปทำงาน (GET check_command) ---
// (ใช้โค้ดเดิมของคุณ ซึ่งรองรับทุกคำสั่งที่ status = 'pending')
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_command'])) {
    header('Content-Type: text/plain'); 
    $result = $conn->query("SELECT * FROM control_panel WHERE status = 'pending' LIMIT 1");
    if ($row = $result->fetch_assoc()) {
        $cmd_id = $row['id'];
        $cmd_name = $row['command'];
        $conn->query("UPDATE control_panel SET status = 'success' WHERE id = $cmd_id");
        echo $cmd_name; 
    } else { 
        echo "none"; 
    }
    exit;
}

// --- 3. ส่วนรับข้อมูล Monitor จาก ESP32 (POST METHOD) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $food  = isset($_POST['food']) ? (float)$_POST['food'] : 0;
    $water = isset($_POST['water']) ? (float)$_POST['water'] : 0; 
    $token = isset($_POST['token']) ? $_POST['token'] : "";
    $type  = isset($_POST['type']) ? $_POST['type'] : "monitor";

    if ($token !== "MY_SECRET_TOKEN") {
        echo json_encode(["status" => "error", "message" => "Invalid Token"]);
        exit;
    }

    $current_f = 0; $current_w = 0;
    $res = $conn->query("SELECT MAX(daily_feed_count) as f, MAX(daily_water_count) as w FROM pet_feeder_logs WHERE DATE(log_time) = CURDATE()");
    if ($row = $res->fetch_assoc()) {
        $current_f = (int)$row['f']; $current_w = (int)$row['w'];
    }

    if ($type === 'feed') $current_f++;
    if ($type === 'water') $current_w++;

    $stmt = $conn->prepare("INSERT INTO pet_feeder_logs (food_level, water_level, event_type, daily_feed_count, daily_water_count) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ddsii", $food, $water, $type, $current_f, $current_w);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "feed_count" => $current_f, "water_count" => $current_w]);
    }
    $stmt->close();
    exit;
}

// --- 4. ส่วนดึงข้อมูล (Latest & Stats) ---
if (isset($_GET['get_latest'])) {
    $result = $conn->query("SELECT * FROM pet_feeder_logs ORDER BY log_time DESC LIMIT 1");
    echo json_encode($result ? $result->fetch_assoc() : null);
    exit;
}

if (isset($_GET['get_stats'])) {
    $sql = "SELECT DATE(log_time) as log_date, 
            COUNT(CASE WHEN event_type = 'feed' THEN 1 END) as food_fills,
            COUNT(CASE WHEN event_type = 'water' THEN 1 END) as water_fills
            FROM pet_feeder_logs WHERE log_time >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(log_time) ORDER BY log_date ASC";
    $result = $conn->query($sql);
    $stats_map = [];
    for($i=6; $i>=0; $i--) { $d = date('Y-m-d', strtotime("-$i days")); $stats_map[$d] = ['food' => 0, 'water' => 0]; }
    if ($result) {
        while($row = $result->fetch_assoc()) {
            $stats_map[$row['log_date']] = ['food' => (int)$row['food_fills'], 'water' => (int)$row['water_fills']];
        }
    }
    $labels = []; $food_data = []; $water_data = [];
    foreach($stats_map as $date => $vals) {
        $labels[] = date("d M", strtotime($date));
        $food_data[] = $vals['food']; $water_data[] = $vals['water'];
    }
    echo json_encode(["labels" => $labels, "food_fills" => $food_data, "water_fills" => $water_data]);
    exit;
}
?>