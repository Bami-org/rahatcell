<?php
require_once "includes/conn.php";

if (isset($_GET["currency_id"])) {
    $sql = $db->delete("currency", "id=" . $_GET["currency_id"]);
    if ($sql) {
        $db->route("currency?opr=success");
    } else {
        $db->show_err();
    }
}

if (isset($_GET["customer_id"])) {
    $sql = $db->delete("customer", "parent_id=" . $_GET["customer_id"]);
    $sql = $db->delete("customer", "id=" . $_GET["customer_id"]);
    if ($sql) {
        $db->route("customer?opr=success");
    } else {
        $db->show_err();
    }
}

if (isset($_GET["category_id"])) {
    $sql = $db->delete("category", "id=" . $_GET["category_id"]);
    if ($sql) {
        $db->route("category?opr=success");
    } else {
        $db->show_err();
    }
}

if (isset($_GET["sub_category_id"])) {
    $photo = mysqli_fetch_assoc($db->query("SELECT photo FROM sub_category WHERE id=" . $_GET["sub_category_id"]));
    $sql = $db->delete("sub_category", "id=" . $_GET["sub_category_id"]);
    if ($sql) {
        unlink("uploads/category/" . $photo["photo"]);
        $db->route("sub_category?opr=success");
    } else {
        $db->show_err();
    }
}

if (isset($_GET["unit_id"])) {
    $sql = $db->delete("units", "id=" . $_GET["unit_id"]);
    if ($sql) {
        $db->route("units?opr=success");
    } else {
        $db->show_err();
    }
}
if (isset($_GET["bank_id"])) {
    $sql = $db->delete("bank", "id=" . $_GET["bank_id"]);
    if ($sql) {
        $db->route("bank?opr=success");
    } else {
        $db->show_err();
    }
}
if (isset($_GET["balance_id"])) {
    $sql = $db->delete("balance", "id=" . $_GET["balance_id"]);
    if ($sql) {
        $db->route("balance?opr=success");
    } else {
        $db->show_err();
    }
}

if (isset($_GET["product_id"])) {
    $sql = $db->delete("product", "id=" . $_GET["product_id"]);
    if ($sql) {
        $db->route("product?opr=success");
    } else {
        $db->show_err();
    }
}

if (isset($_GET["order_id"])) {
    $sql = $db->delete("orders", "id=" . $_GET["order_id"]);
    if ($sql) {
        $db->route("orders?opr=success");
    } else {
        $db->show_err();
    }
}


if (isset($_GET["payment_id"])) {
    $sql = $db->delete("payment", "id=" . $_GET["payment_id"]);
    if ($sql) {
        $db->route("payment?opr=success");
    } else {
        $db->show_err();
    }
}

if (isset($_GET["transaction_id"])) {
    $sql = $db->delete("transactions", "id=" . $_GET["transaction_id"]);
    if ($sql) {
        $db->route("transaction?opr=success");
    } else {
        $db->show_err();
    }
}

if (isset($_GET["announcement_id"])) {
    $sql = $db->delete("announcement", "id=" . $_GET["announcement_id"]);
    if ($sql) {
        $db->route("announcement?opr=success");
    } else {
        $db->show_err();
    }
}

if (isset($_GET["ad_id"])) {
    $sql = $db->delete("ads", "id=" . $_GET["ad_id"]);
    if ($sql) {
        $db->route("ads?opr=success");
    } else {
        $db->show_err();
    }
}
