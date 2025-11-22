<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'db.php'; // เรียกไฟล์ connect database เดิม

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

// 2. เพิ่มผู้ใช้งานใหม่ (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    try {
        // เข้ารหัส Password ก่อนบันทึก (เพื่อความปลอดภัย)
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, Password, full_name, role, phone, email, address) 
                VALUES (:usr, :pwd, :fname, :role, :phone, :email, :addr)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':usr' => $data['username'],
            ':pwd' => $hashed_password,
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
    $stmt = $conn->query("SELECT * FROM users ORDER BY user_id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ลบ field password ออกก่อนส่งกลับไปหน้าเว็บ เพื่อความปลอดภัย
    foreach ($users as $key => $user) {
        unset($users[$key]['Password']);
    }

    echo json_encode($users);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>