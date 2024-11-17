<?php
require_once "includes/conn.php";
if (isset($_GET["logout"])) {
    session_destroy();
    unset($_SESSION);
    // setcookie("username", "", time() - 86400, "/");
    $db->route("index?logout");
}
