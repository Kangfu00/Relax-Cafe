<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. ลบออเดอร์
if ($action == 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM Orders WHERE Order_id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// 2. ดึงรายชื่อลูกค้า (สำหรับ Dropdown ในหน้าเพิ่มออเดอร์)
if ($action == 'get_users') {
    try {
        $stmt = $conn->query("SELECT User_id, Username, Full_Name FROM Users ORDER BY User_id ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode([]);
    }
    exit();
}

// 3. เพิ่มออเดอร์ใหม่ (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    try {
        $sql = "INSERT INTO Orders (User_id, Order_date, Total_amount, Discount_id, Promotion_id, Payment_id, Order_status, Note) 
                VALUES (:uid, :odate, :total, :did, :pid, :payid, :status, :note)";
        $stmt = $conn->prepare($sql);
        
        // แปลงค่าว่างเป็น NULL
        $disc = !empty($data['discount_id']) ? $data['discount_id'] : null;
        $prom = !empty($data['promotion_id']) ? $data['promotion_id'] : null;
        $pay  = !empty($data['payment_id']) ? $data['payment_id'] : null;

        $stmt->execute([
            ':uid' => $data['user_id'],
            ':odate' => $data['order_date'],
            ':total' => $data['total_amount'],
            ':did' => $disc,
            ':pid' => $prom,
            ':payid' => $pay,
            ':status' => $data['order_status'],
            ':note' => $data['note']
        ]);
        
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// 4. ดึงรายการออเดอร์ทั้งหมด (GET ปกติ)
try {
    // Join ตาราง Users เพื่อเอาชื่อลูกค้ามาแสดง
    $sql = "SELECT o.*, u.Username, u.Full_Name 
            FROM Orders o
            LEFT JOIN Users u ON o.User_id = u.User_id
            ORDER BY o.Order_date DESC";
    $stmt = $conn->query($sql);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>