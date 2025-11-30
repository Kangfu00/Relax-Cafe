<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. ลบออเดอร์
if ($action == 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// 2. ดึงรายชื่อลูกค้า (สำหรับ Dropdown)
if ($action == 'get_users') {
    try {
        // แก้ไขชื่อฟิลด์ให้ตรงกับ Database (full_name ตัวเล็ก)
        $stmt = $conn->query("SELECT user_id, username, full_name FROM users ORDER BY user_id ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode([]);
    }
    exit();
}

// 3. เพิ่มออเดอร์ใหม่ (POST) - *** ส่วนที่แก้ไขสำคัญ ***
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    try {
        // เริ่ม Transaction (บันทึก 2 ตารางพร้อมกัน ถ้าพลาดให้ยกเลิกทั้งหมด)
        $conn->beginTransaction();

        // 3.1 บันทึกตาราง Orders (หัวบิล)
        $sql = "INSERT INTO orders (user_id, order_date, total_amount, discount_id, promotion_id, payment_id, order_status, note) 
                VALUES (:uid, :odate, :total, :did, :pid, :payid, :status, :note)";
        $stmt = $conn->prepare($sql);
        
        $disc = !empty($data['discount_id']) ? $data['discount_id'] : null;
        $prom = !empty($data['promotion_id']) ? $data['promotion_id'] : null;
        $pay  = !empty($data['payment_id']) ? $data['payment_id'] : null;

        $stmt->execute([
            ':uid' => $data['user_id'],
            ':odate' => $data['order_date'],
            ':total' => $data['total_amount'], // ยอดสุทธิที่คำนวณมาจาก JS
            ':did' => $disc,
            ':pid' => $prom,
            ':payid' => $pay,
            ':status' => $data['order_status'],
            ':note' => $data['note']
        ]);

        // หา ID ของออเดอร์ล่าสุดที่เพิ่งสร้าง
        $lastOrderId = $conn->lastInsertId();

        // 3.2 บันทึกรายการสินค้าลงตาราง Order_Detail (วนลูป)
        if (!empty($data['items']) && is_array($data['items'])) {
            $sqlDetail = "INSERT INTO order_detail (order_id, menu_id, quantity, unit_price, subtotal) 
                          VALUES (:oid, :mid, :qty, :price, :sub)";
            $stmtDetail = $conn->prepare($sqlDetail);

            foreach ($data['items'] as $item) {
                // คำนวณราคารวมย่อย (Subtotal) ของรายการนั้น
                $subtotal = $item['price'] * $item['quantity'];
                
                $stmtDetail->execute([
                    ':oid' => $lastOrderId,
                    ':mid' => $item['menu_id'],
                    ':qty' => $item['quantity'],
                    ':price' => $item['price'],
                    ':sub' => $subtotal
                ]);
            }
        }

        // ยืนยันการบันทึกข้อมูลทั้งหมด
        $conn->commit();
        
        echo json_encode(['status' => 'success']);

    } catch (PDOException $e) {
        // ถ้ามี Error ให้ยกเลิก (Rollback) ข้อมูลที่บันทึกไปแล้ว
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// 4. ดึงรายการออเดอร์ทั้งหมด (GET)
try {
    $sql = "SELECT o.*, u.username, u.full_name 
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            ORDER BY o.order_date DESC";
    $stmt = $conn->query($sql);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>