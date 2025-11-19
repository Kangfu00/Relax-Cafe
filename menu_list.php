<?php
// กำหนดข้อมูลการเชื่อมต่อฐานข้อมูล
$host = 'fdb1033.awardspace.net';
$db   = '4689745_dbaie313'; // เปลี่ยนเป็นชื่อฐานข้อมูลของคุณ
$user = 'M16ak47mk18Ar15';     // เปลี่ยนเป็นชื่อผู้ใช้ฐานข้อมูลของคุณ
$pass = '4689745_dbaie313';     // เปลี่ยนเป็นรหัสผ่านฐานข้อมูลของคุณ
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // 1. เชื่อมต่อฐานข้อมูล
    $pdo = new PDO($dsn, $user, $pass, $options);

    // 2. ดึงข้อมูลเมนูทั้งหมด
    $stmt = $pdo->query('SELECT * FROM Menu');
    $menus = $stmt->fetchAll();

} catch (\PDOException $e) {
    // หากเกิดข้อผิดพลาดในการเชื่อมต่อ/ดึงข้อมูล
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการเมนู</title>
    <link rel="stylesheet" href="css/users.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="container">
        
        <header class="page-header">
            <div class="header-left">
                <h2>จัดการข้อมูลเมนู</h2>
                <p class="subtitle">รายชื่ออาหารและเครื่องดื่มทั้งหมด</p>
            </div>
            <div class="header-right">
                <a href="index.html" class="btn btn-secondary">
                    &larr; กลับหน้าหลัก
                </a>
            </div>
        </header>

        <div class="action-bar">
            <a href="Add_menu.html" class="btn btn-primary">
                + เพิ่มเมนูใหม่
            </a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Menu ID</th>
                        <th>Menu Name</th>
                        <th>Category ID</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Image URL</th>
                        <th>Status</th>
                        <th>Action</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // 3. วนลูปแสดงข้อมูล
                    if (!empty($menus)):
                        foreach ($menus as $menu): 
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($menu['Menu_id']); ?></td>
                            <td><?php echo htmlspecialchars($menu['ชื่อเมนู']); ?></td>
                            <td><?php echo htmlspecialchars($menu['Category_id']); ?></td>
                            <td><?php echo number_format($menu['ราคา'], 2); ?></td>
                            <td><?php echo htmlspecialchars(mb_substr($menu['คำอธิบาย'], 0, 50)) . (mb_strlen($menu['คำอธิบาย']) > 50 ? '...' : ''); ?></td>
                            <td><?php echo htmlspecialchars($menu['Image_url']); ?></td>
                            <td><?php echo htmlspecialchars($menu['สถานะ']); ?></td>
                            <td>
                                <a href="Edit_menu.php?id=<?php echo $menu['Menu_id']; ?>" class="btn-action btn-edit">แก้ไข</a>
                                <a href="Delete_menu.php?id=<?php echo $menu['Menu_id']; ?>" class="btn-action btn-delete" 
                                   onclick="return confirm('คุณต้องการลบเมนู <?php echo htmlspecialchars($menu['ชื่อเมนู']); ?> หรือไม่?');">ลบ</a>
                            </td>
                        </tr>
                    <?php 
                        endforeach; 
                    else:
                    ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">ไม่พบข้อมูลเมนูในระบบ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>