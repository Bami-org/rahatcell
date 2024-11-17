<?php

require_once "../includes/conn.php";


    $fromDate = $_POST["fromDate"];
    $toDate = $_POST["toDate"];
    $sql = $con->query("DELETE FROM orders WHERE DATE(created) BETWEEN '$fromDate' AND '$toDate'");
    if ($sql) {
        exit(json_encode([
            "status" => true,
            "message" => "سفارشات انتخاب شده موفقانه حذف شد"
        ]));
    } else {
        exit(json_encode([
            "status" => false,
            "message" => " :خطا هنگام حذف سفارشات" . $db->show_err()
        ]));
    }

    