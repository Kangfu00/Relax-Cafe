<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once 'db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. ลบเมนู
if ($action == 'delete' && isset($_GET['id'])) {
    try {
        // (Optional: ควรลบไฟล์รูปออกจาก folder uploads ด้วย ถ้าต้องการระบบที่สมบูรณ์)
        $stmt = $conn->prepare("DELETE FROM Menu WHERE Menu_id = ?");
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
        $menu_name = $_POST['menu_name'];
        $category_id = $_POST['category_id'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $image_url = ""; // ค่าเริ่มต้น

        // จัดการอัปโหลดไฟล์
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true); // สร้าง folder ถ้ายังไม่มี

            $file_extension = strtolower(pathinfo($_FILES["image_file"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid("menu_", true) . "." . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            }
        }

        $sql = "INSERT INTO Menu (Menu_name, Category_id, Price, Description, Image_url, Status) 
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
    // เรียงตาม ID ล่าสุด
    $stmt = $conn->query("SELECT * FROM Menu ORDER BY Menu_id DESC");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>