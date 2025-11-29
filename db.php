<?php
$servername = "fdb1033.awardspace.net";
$username = "4689745_dbaie313";
$password = "M16ak47mk18Ar15!";
$dbname = "4689745_dbaie313";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    // ตั้งค่าให้แจ้ง error แบบ Exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    // แก้ตรงนี้: ส่ง Error กลับเป็น JSON เพื่อให้หน้าเว็บรู้เรื่อง
    header("Content-Type: application/json");
    echo json_encode([
        'status' => 'error', 
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit(); // จบการทำงานทันที
}
?>