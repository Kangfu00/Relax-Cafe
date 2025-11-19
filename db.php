<?php
$servername = "fdb1033.awardspace.net";
$username = "4689745_dbaie313";
$password = "M16ak47mk18Ar15";
$dbname = "4689745_dbaie313";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    }
?>