<?php
header('Content-Type: application/json'); // บอก Browser ว่าไฟล์นี้คืนค่าเป็น JSON
require_once 'db.php';

try {
    // ดึงข้อมูลเรียงจากใหม่ไปเก่า
    $sql = "SELECT * FROM Menu ORDER BY Menu_id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ส่งข้อมูลกลับเป็น JSON
    echo json_encode($result);

} catch (Exception $e) {
    // กรณี Error ให้ส่ง JSON ว่า error
    echo json_encode(['error' => $e->getMessage()]);
}
?>