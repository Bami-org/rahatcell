<?php
require_once "../includes/conn.php";

if (isset($_GET["customer_id"])) {
    $sql = mysqli_fetch_assoc($db->query("SELECT currency.name as c_name,currency.symbol as symbol,currency.id as c_id FROM customer LEFT JOIN currency ON customer.currency_id = currency.id WHERE customer.id =" . $_GET["customer_id"]));
    echo json_encode([
        "name" => $sql["c_name"] . " - " . $sql["symbol"],
        "currency_id" => $sql["c_id"],
    ]);
}
