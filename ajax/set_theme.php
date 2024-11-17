<?php
require_once "../includes/conn.php";
if (isset($_POST["theme"])) {
    $sql = $db->update("setting", ["theme" => $db->clean_input($_POST["theme"])]);
    if ($sql) {
        echo "success";
    } else {
        echo $db->show_err();
    }
}
