<?php
header('Content-Type: application/json');
require_once 'db.php'; // เรียกใช้ไฟล์เชื่อมต่อ Database ตัวเดิม

try {
    // เขียน SQL โดย JOIN กับตาราง Users เพื่อเอาชื่อลูกค้ามาแสดง
    // (สมมติว่าตาราง Users มีคอลัมน์ User_id และ Username)
    $sql = "SELECT 
                o.*, 
                u.Username, 
                u.Full_Name 
            FROM Orders o
            LEFT JOIN Users u ON o.User_id = u.User_id
            ORDER BY o.Order_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>