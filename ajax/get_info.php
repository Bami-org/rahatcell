<?php

require_once "../includes/conn.php";

if (isset($_GET["currency_id"])) {
    $sql = mysqli_fetch_assoc($db->query("SELECT * FROM currency WHERE id =" . $_GET["currency_id"]));
    echo json_encode([
        "name" => $sql["name"],
        "symbol" => $sql["symbol"],
    ]);
}


if (isset($_GET["category_id"])) {
    $sql = mysqli_fetch_assoc($db->query("SELECT * FROM category WHERE id =" . $_GET["category_id"]));
    echo json_encode([
        "name" => $sql["name"]
    ]);
}

if (isset($_GET["sub_category_id"])) {
    $sql = mysqli_fetch_assoc($db->query("SELECT * FROM sub_category WHERE id =" . $_GET["sub_category_id"]));
    echo json_encode([
        "name" => $sql["name"],
        "up_category" => $sql["up_category"],
    ]);
}

if (isset($_GET["unit_id"])) {
    $sql = mysqli_fetch_assoc($db->query("SELECT * FROM units WHERE id =" . $_GET["unit_id"]));
    echo json_encode([
        "name" => $sql["name"]
    ]);
}

if (isset($_GET["bank_id"])) {
    $sql = mysqli_fetch_assoc($db->query("SELECT * FROM bank WHERE id =" . $_GET["bank_id"]));
    echo json_encode([
        "name" => $sql["name"],
        "description" => $sql["description"]
    ]);
}

if (isset($_GET["customer_id"])) {
    $sql = mysqli_fetch_assoc($db->query("SELECT * FROM customer WHERE id =" . $_GET["customer_id"]));
    echo json_encode([
        "name" => $sql["name"],
        "phone" => $sql["phone"],
        "address" => $sql["address"],
        "currency_id" => $sql["currency_id"],
        "username" => $sql["username"],
        "password" => $sql["password"],
        "pin_code" => $sql["pin_code"],
        "status" => $sql["status"],
    ]);
}

if (isset($_GET["balance_id"])) {
    $sql = mysqli_fetch_assoc($db->query("SELECT balance.*,CONCAT(currency.name,'-',currency.symbol) as currency FROM balance LEFT JOIN customer ON balance.customer_id = customer.id LEFT JOIN currency ON customer.currency_id = currency.id  WHERE balance.id =" . $_GET["balance_id"]));
    echo json_encode([
        "customer_id" => $sql["customer_id"],
        "balance" => $sql["balance"],
        "currency" => $sql["currency"],
        "description" => $sql["description"]
    ]);
}

if (isset($_GET["product_id"])) {
    $sql = mysqli_fetch_assoc($db->query("SELECT * FROM product WHERE id =" . $_GET["product_id"]));
    echo json_encode([
        "amount" => $sql["amount"],
        "unit_id" => $sql["unit_id"],
        "category_id" => $sql["category_id"],
        "sub_category_id" => $sql["sub_category_id"],
        "sub_category_id" => $sql["sub_category_id"],
        "dollar_buy_price" => $sql["dollar_buy_price"],
        "dollar_sale_price" => $sql["dollar_sale_price"],
        "toman_buy_price" => $sql["toman_buy_price"],
        "toman_sale_price" => $sql["toman_sale_price"],
        "lyra_buy_price" => $sql["lyra_buy_price"],
        "lyra_sale_price" => $sql["lyra_sale_price"],
        "euro_buy_price" => $sql["euro_buy_price"],
        "euro_sale_price" => $sql["euro_sale_price"],
        "afghani_buy_price" => $sql["afghani_buy_price"],
        "afghani_sale_price" => $sql["afghani_sale_price"],
        "description" => $sql["description"],
    ]);
}


if (isset($_GET["order_id"])) {
    $sql = mysqli_fetch_assoc($db->query("SELECT * FROM orders WHERE id =" . $_GET["order_id"]));
    echo json_encode([
        "customer_id" => $sql["customer_id"],
        "product_id" => $sql["product_id"],
        "status" => $sql["status"],
    ]);
}


if (isset($_GET["payment_id"])) {
    $sql = mysqli_fetch_assoc($db->query("SELECT * FROM payment WHERE id =" . $_GET["payment_id"]));
    echo json_encode([
        "customer_id" => $sql["customer_id"],
        "pay_amount" => $sql["pay_amount"],
        "bank_id" => $sql["bank_id"],
        "description" => $sql["description"]
    ]);
}

if (isset($_GET["ann_id"])) {
    $sql = mysqli_fetch_assoc($db->query("SELECT * FROM announcement WHERE id =" . $_GET["ann_id"]));
    echo json_encode([
        "title" => $sql["title"],
        "content" => $sql["content"],
    ]);
}
if (isset($_GET["ad_id"])) {
    $sql = mysqli_fetch_assoc($db->query("SELECT * FROM ads WHERE id =" . $_GET["ad_id"]));
    echo json_encode([
        "title" => $sql["title"]
    ]);
}
