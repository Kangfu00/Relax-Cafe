<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// --- 1. ลบข้อมูล (Delete) ---
if ($action == 'delete' && isset($_GET['id'])) {
    try {
        // แก้ Promotions -> promotion
        $stmt = $conn->prepare("DELETE FROM promotion WHERE promotion_id = ?");
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
        // แก้ Promotions -> promotion
        $sql = "INSERT INTO promotion (promotion_name, menu_id, description, discount_percent, start_date, end_date, status) 
                VALUES (:name, :menu_id, :desc, :percent, :start, :end, :status)";
        
        $stmt = $conn->prepare($sql);
        
        $menuId = !empty($data['menu_id']) ? $data['menu_id'] : null;

        $stmt->execute([
            ':name'    => $data['promotion_name'],
            ':menu_id' => $menuId,
            ':desc'    => $data['description'],
            ':percent' => $data['discount_percent'],
            ':start'   => $data['start_date'],
            ':end'     => $data['end_date'],
            ':status'  => $data['status']
        ]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// --- 3. ดึงข้อมูลทั้งหมด (List - GET) ---
try {
    // แก้ Promotions -> promotion
    $stmt = $conn->query("SELECT * FROM promotion ORDER BY promotion_id DESC");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
} catch (PDOException $e) {
    // ถ้า Error ให้ส่ง Array ว่างกลับไป
    echo json_encode([]);
}
?>