<?php
// ไฟล์นี้ชื่อ api_users.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'db.php'; // เรียกไฟล์เชื่อมต่อฐานข้อมูล

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. ลบผู้ใช้งาน
if ($action == 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// 2. เพิ่มผู้ใช้งาน (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    try {
        // แฮชรหัสผ่านก่อนเก็บ (เพื่อความปลอดภัย) แต่ถ้าเอาแบบง่ายๆ เก็บ text ธรรมดาก็ได้
        // $password = password_hash($data['password'], PASSWORD_DEFAULT); 
        $password = $data['password']; 

        $sql = "INSERT INTO users (username, password, full_name, role, phone, email, address, created_at) 
                VALUES (:usr, :pwd, :fname, :role, :phone, :email, :addr, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':usr' => $data['username'],
            ':pwd' => $password,
            ':fname' => $data['fullname'],
            ':role' => $data['role'],
            ':phone' => $data['phone'],
            ':email' => $data['email'],
            ':addr' => $data['address']
        ]);

        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// 3. ดึงข้อมูลผู้ใช้งานทั้งหมด (GET)
try {
    // เลือกฟิลด์มาแสดง (ไม่ควรส่ง password กลับไป)
    $stmt = $conn->query("SELECT user_id, username, full_name, role, phone, email, address, created_at FROM users ORDER BY user_id DESC");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>