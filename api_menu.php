<?php
// api_menu.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล
require_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. ลบเมนู
if ($action == 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM menu WHERE menu_id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// 2. เพิ่มเมนู (รองรับไฟล์รูปภาพ)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // รับค่าจาก Form
        $menu_name = $_POST['menu_name'];
        $category_id = $_POST['category_id'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $image_url = ""; 

        // จัดการอัปโหลดไฟล์
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
            $target_dir = "uploads/";
            // สร้างโฟลเดอร์ uploads ถ้ายังไม่มี
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES["image_file"]["name"], PATHINFO_EXTENSION));
            // ตั้งชื่อไฟล์ใหม่กันซ้ำ
            $new_filename = uniqid("menu_", true) . "." . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            }
        }

        $sql = "INSERT INTO menu (menu_name, category_id, price, description, image_url, status) 
                VALUES (:name, :cat, :price, :desc, :img, :status)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $menu_name,
            ':cat' => $category_id,
            ':price' => $price,
            ':desc' => $description,
            ':img' => $image_url,
            ':status' => $status
        ]);

        echo json_encode(['status' => 'success']);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// 3. ดึงข้อมูลเมนูทั้งหมด (GET)
try {
    $stmt = $conn->query("SELECT * FROM menu ORDER BY menu_id DESC");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>