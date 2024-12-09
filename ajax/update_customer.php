<?php

require_once "../includes/conn.php";

if (isset($_POST["customer_id"])) {
    $customer_id = $_POST["customer_id"];
    $name = $_POST["name"];
    $phone = $_POST["phone"];
    $address = $_POST["address"];
    $currency_id = $_POST["currency_id"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $pin_code = $_POST["pin_code"];
    $status = $_POST["status"];
    $customer_type = $_POST["customer_type"];

    $sql = "UPDATE customer SET 
                name = '$name', 
                phone = '$phone', 
                address = '$address', 
                currency_id = '$currency_id', 
                username = '$username', 
                password = '$password', 
                pin_code = '$pin_code', 
                status = '$status', 
                customer_type = '$customer_type' 
            WHERE id = $customer_id";

    if ($db->query($sql) === TRUE) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $db->error]);
    }
}