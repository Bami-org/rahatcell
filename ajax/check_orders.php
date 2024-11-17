<?php
require_once "../includes/conn.php";

$sql = $db->query("SELECT `status` FROM orders WHERE `status`='Pending'");
if ($sql->num_rows > 0) {
    echo json_encode(["result" => 1]);
} else {
    echo json_encode(["result" => 0]);
}
