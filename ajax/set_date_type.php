<?php
require_once "../includes/conn.php";
if (isset($_POST["date_type"])) {
    $sql = $db->update("setting", ["date_type" => $db->clean_input($_POST["date_type"])]);
    if ($sql) {
        echo "success";
    } else {
        echo $db->show_err();
    }
}
