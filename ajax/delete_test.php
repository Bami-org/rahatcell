<?php
require_once "../includes/conn.php";

if (isset($_GET["test_id"])) {
    $db->delete("ajax_test", "id=" . $_GET["test_id"]);
}
