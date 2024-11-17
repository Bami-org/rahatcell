<?php
require_once "../includes/conn.php";
if ($_POST["username"]) {
    $username = $db->clean_input($_POST["username"]);
    $sql = $db->query("SELECT username FROM customer WHERE username='$username'");
    if ($sql->num_rows > 0) {
        echo json_encode([
            "result" => true,
            "message" => "نام کاربری $username از قبل وجود دارد"
        ]);
    } else {
        echo json_encode([
            "result" => false
        ]);
    }
}
