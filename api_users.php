<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'db.php';

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
        // --- ส่วนที่แก้: แปลงชื่อ Role เป็น ID ---
        // คุณต้องเช็คใน Database ตาราง roles ว่า ID อะไรคืออะไร
        // สมมติว่า: 1=Admin, 2=Staff, 3=Customer (ถ้าไม่ใช่ ให้แก้เลขตรงนี้นะครับ)
        $role_id = 1; // ค่าเริ่มต้นเป็น Customer
        if ($data['role'] === 'admin') {
            $role_id = 3;
        } elseif ($data['role'] === 'staff' || $data['role'] === 'manager') {
            $role_id = 2;
        } elseif ($data['role'] === 'customer') {
            $role_id = 1;
        }

        // แฮชรหัสผ่าน (ถ้าต้องการ)
        $password = $data['password']; 

        // แก้ชื่อคอลัมน์จาก role -> role_id
        $sql = "INSERT INTO users (username, password, full_name, role_id, phone, email, address, created_at) 
                VALUES (:usr, :pwd, :fname, :rid, :phone, :email, :addr, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':usr' => $data['username'],
            ':pwd' => $password,
            ':fname' => $data['fullname'],
            ':rid' => $role_id, // ส่งเป็น ID แทน
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
    // --- ส่วนที่แก้: ใช้ JOIN เพื่อดึงชื่อ Role มาแสดง ---
    // เพราะในตาราง users มีแค่ role_id เราต้องไปเอาชื่อจากตาราง roles
    $sql = "SELECT u.user_id, u.username, u.full_name, u.phone, u.email, u.address, u.created_at,
                   r.role_name as role 
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.role_id
            ORDER BY u.user_id DESC";
            
    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>