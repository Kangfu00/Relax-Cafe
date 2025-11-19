<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// --- 1. ลบข้อมูล (Delete) ---
if ($action == 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM Promotions WHERE Promotion_id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// --- 2. เพิ่มข้อมูล (Add - POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    try {
        $sql = "INSERT INTO Promotions (Promotion_name, Menu_id, Description, Discount_percent, Start_date, end_date, Status) 
                VALUES (:name, :menu_id, :desc, :percent, :start, :end, :status)";
        
        $stmt = $conn->prepare($sql);
        
        // จัดการ Menu_id: ถ้าไม่ได้กรอกมา (เป็นค่าว่าง) ให้ใส่ NULL ลงฐานข้อมูล
        $menuId = !empty($data['Menu_id']) ? $data['Menu_id'] : null;

        $stmt->execute([
            ':name'    => $data['Promotion_name'],
            ':menu_id' => $menuId,
            ':desc'    => $data['Description'],
            ':percent' => $data['Discount_percent'],
            ':start'   => $data['Start_date'],
            ':end'     => $data['end_date'], // ใน HTML คุณส่ง key นี้เป็นตัวพิมพ์เล็ก
            ':status'  => $data['Status']
        ]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// --- 3. ดึงข้อมูลทั้งหมด (List - GET) ---
try {
    $stmt = $conn->query("SELECT * FROM Promotions ORDER BY Promotion_id DESC");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>