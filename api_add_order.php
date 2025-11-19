<?php
header('Content-Type: application/json');
require_once 'db.php';

// รับข้อมูล JSON ที่ส่งมาจาก HTML
$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data)) {
    try {
        $sql = "INSERT INTO Orders 
                (User_id, Order_date, Total_amount, Discount_id, Promotion_id, Payment_id, Order_status, Note) 
                VALUES 
                (:user_id, :order_date, :total_amount, :discount_id, :promotion_id, :payment_id, :order_status, :note)";
        
        $stmt = $conn->prepare($sql);
        
        // แปลงค่าว่างให้เป็น NULL สำหรับฟิลด์ที่อนุญาตให้ NULL ได้
        $discount = !empty($data['discount_id']) ? $data['discount_id'] : null;
        $promotion = !empty($data['promotion_id']) ? $data['promotion_id'] : null;
        $payment = !empty($data['payment_id']) ? $data['payment_id'] : null;

        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':order_date', $data['order_date']);
        $stmt->bindParam(':total_amount', $data['total_amount']);
        $stmt->bindParam(':discount_id', $discount);
        $stmt->bindParam(':promotion_id', $promotion);
        $stmt->bindParam(':payment_id', $payment);
        $stmt->bindParam(':order_status', $data['order_status']);
        $stmt->bindParam(':note', $data['note']);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'บันทึกออเดอร์สำเร็จ']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'บันทึกข้อมูลไม่สำเร็จ']);
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลที่ส่งมา']);
}
?>