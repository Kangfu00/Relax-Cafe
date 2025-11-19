<?php
// 1. เชื่อมต่อฐานข้อมูล
require_once 'db.php'; 

// ตรวจสอบว่ามีการส่งข้อมูลแบบ POST มาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // 2. รับค่าจากฟอร์ม
        $menu_name = $_POST['menu_name'];
        $category_id = $_POST['category_id'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $image_url = ""; // ค่าเริ่มต้นรูปภาพเป็นค่าว่าง

        // 3. จัดการอัปโหลดรูปภาพ (ถ้ามีไฟล์ส่งมา)
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
            
            // กำหนดโฟลเดอร์ปลายทาง (ต้องสร้างโฟลเดอร์ uploads ไว้ในโปรเจกต์ด้วย)
            $target_dir = "uploads/";
            
            // ตรวจสอบว่ามีโฟลเดอร์ไหม ถ้าไม่มีให้สร้าง
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // ดึงนามสกุลไฟล์ (jpg, png, etc.)
            $file_extension = strtolower(pathinfo($_FILES["image_file"]["name"], PATHINFO_EXTENSION));
            
            // ตรวจสอบว่าเป็นไฟล์รูปภาพจริงหรือไม่
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                // ตั้งชื่อไฟล์ใหม่เพื่อป้องกันชื่อซ้ำ (ใช้เวลาปัจจุบัน + เลขสุ่ม)
                $new_filename = uniqid("menu_", true) . "." . $file_extension;
                $target_file = $target_dir . $new_filename;

                // ย้ายไฟล์จาก Temp ไปยังโฟลเดอร์ uploads
                if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
                    $image_url = $target_file; // เก็บ path รูปภาพลงตัวแปร
                }
            }
        }

        // 4. เขียนคำสั่ง SQL เพื่อบันทึกข้อมูล
        $sql = "INSERT INTO Menu (Menu_name, Category_id, Price, Description, Image_url, Status) 
                VALUES (:name, :category, :price, :desc, :image, :status)";
        
        $stmt = $conn->prepare($sql);
        
        // ผูกตัวแปรกับ SQL (Bind Parameters)
        $stmt->bindParam(':name', $menu_name);
        $stmt->bindParam(':category', $category_id);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':desc', $description);
        $stmt->bindParam(':image', $image_url);
        $stmt->bindParam(':status', $status);

        // 5. สั่งรันคำสั่ง SQL
        if ($stmt->execute()) {
            // บันทึกสำเร็จ -> แจ้งเตือนและกลับไปหน้า menu.html
            echo "<script>
                alert('บันทึกข้อมูลเมนูเรียบร้อยแล้ว');
                window.location.href = 'menu.html';
            </script>";
        } else {
            // บันทึกไม่สำเร็จ
            echo "<script>
                alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                window.history.back();
            </script>";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    // ถ้าเข้าหน้านี้โดยไม่ได้กด Submit ให้เด้งกลับไป
    header("Location: Add_menu.html");
    exit();
}
?>