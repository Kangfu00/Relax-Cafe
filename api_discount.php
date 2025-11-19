<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// --- 1. ลบข้อมูล (Delete) ---
if ($action == 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM Discounts WHERE Discount_id = ?");
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
        $sql = "INSERT INTO Discounts (Discount_name, Discount_type, Discount_value, Start_date, End_date, Status) 
                VALUES (:name, :type, :val, :start, :end, :status)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name'   => $data['Discount_name'],
            ':type'   => $data['Discount_type'],
            ':val'    => $data['Discount_value'],
            ':start'  => $data['Start_date'],
            ':end'    => $data['End_date'],
            ':status' => $data['Status']
        ]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// --- 3. ดึงข้อมูลทั้งหมด (List - GET) ---
try {
    $stmt = $conn->query("SELECT * FROM Discounts ORDER BY Discount_id DESC");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>