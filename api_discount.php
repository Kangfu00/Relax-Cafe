<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// --- 1. ลบข้อมูล (Delete) ---
if ($action == 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM discounts WHERE discount_id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// --- 2. เพิ่มข้อมูล (Add - POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูล JSON จากหน้า HTML
    $data = json_decode(file_get_contents("php://input"), true);

    try {
        $sql = "INSERT INTO discounts (discount_name, discount_type, discount_value, start_date, end_date, status) 
                VALUES (:name, :type, :val, :start, :end, :status)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name'   => $data['discount_name'],
            ':type'   => $data['discount_type'],
            ':val'    => $data['discount_value'],
            ':start'  => $data['start_date'],
            ':end'    => $data['end_date'],
            ':status' => $data['status']
        ]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// --- 3. ดึงข้อมูลทั้งหมด (List - GET) ---
try {
    $stmt = $conn->query("SELECT * FROM discounts ORDER BY discount_id DESC");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>