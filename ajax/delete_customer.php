<?php
require_once "../includes/conn.php";

if (isset($_POST["customer_id"])) {
    $customer_id = $_POST["customer_id"];
    $sql = "DELETE FROM customer WHERE id = $customer_id";

    if ($db->query($sql) === TRUE) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $db->error]);
    }
}
?>