<?php
// บรรทัดแรกต้องชิดขอบบนสุด ห้ามมีเว้นวรรคก่อน <?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// เช็คว่ามีไฟล์ db.php อยู่จริงไหม
if (!file_exists('db.php')) {
    echo json_encode(['status' => 'error', 'message' => 'หาไฟล์ db.php ไม่เจอ! กรุณาเช็คว่าไฟล์อยู่ในโฟลเดอร์เดียวกัน']);
    exit();
}

require_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    // 1. ลบออเดอร์
    if ($action == 'delete' && isset($_GET['id'])) {
        $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['status' => 'success']);
        exit();
    }

    // 2. ดึงรายชื่อลูกค้า (สำหรับ Dropdown)
    if ($action == 'get_users') {
        // ใช้ full_name (ตัวเล็ก) ตาม Database ที่แก้ล่าสุด
        $stmt = $conn->query("SELECT user_id, username, full_name FROM users ORDER BY user_id ASC");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
        exit();
    }

    // 3. เพิ่มออเดอร์ใหม่ (POST)
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid JSON Input']);
            exit();
        }
        
        $conn->beginTransaction();

        // 3.1 บันทึกตาราง Orders
        $sql = "INSERT INTO orders (user_id, order_date, total_amount, discount_id, promotion_id, payment_id, order_status, note) 
                VALUES (:uid, :odate, :total, :did, :pid, :payid, :status, :note)";
        $stmt = $conn->prepare($sql);
        
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

        $lastOrderId = $conn->lastInsertId();

        // 3.2 บันทึกรายการสินค้า
        if (!empty($data['items']) && is_array($data['items'])) {
            $sqlDetail = "INSERT INTO order_detail (order_id, menu_id, quantity, unit_price, subtotal) 
                          VALUES (:oid, :mid, :qty, :price, :sub)";
            $stmtDetail = $conn->prepare($sqlDetail);

            foreach ($data['items'] as $item) {
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

        $conn->commit();
        echo json_encode(['status' => 'success']);
        exit();
    }

    // 4. ดึงรายการออเดอร์ทั้งหมด (GET)
    $sql = "SELECT o.*, u.username, u.full_name 
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            ORDER BY o.order_date DESC";
    $stmt = $conn->query($sql);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}

// --- ส่วนที่เพิ่ม: อัปเดตสถานะออเดอร์ ---
if ($action == 'update_status') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (isset($data['order_id']) && isset($data['status'])) {
        try {
            $sql = "UPDATE orders SET order_status = :status WHERE order_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':status' => $data['status'],
                ':id' => $data['order_id']
            ]);
            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    }
    exit();
}
?>