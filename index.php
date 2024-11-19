<?php
require_once "includes/conn.php";

// Get the requested URI
$request = $_SERVER['REQUEST_URI'];

// Remove query string from the request URI
$request = strtok($request, '?');

// Check if the requested file exists
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $request) && strpos($request, '.') === false) {
    header("Location: $request.php");
    exit();
}

// Check if a session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if not already started
}


if (isset($_SESSION["username"]) && isset($_SESSION["password"])) {
    if ($_SESSION["user_type"] == "admin") {
        $db->route("dashboard");
    }
}

if (isset($_POST["login"])) {
    $username = $db->clean_input($_POST["username"]);
    $password = $db->clean_input($_POST["password"]);
    $user_type = $db->clean_input($_POST["user_type"]);
    $sql = $db->query("SELECT * FROM user WHERE username = '$username' AND password = '" . md5($password) . "' AND user_type = '$user_type'");
    if ($sql->num_rows > 0) {
        $_SESSION["username"] = $username;
        $_SESSION["password"] = $password;
        if ($user_type == "admin") {
            $_SESSION["user_type"] = $user_type;
            $db->route("dashboard?login=success");
        } else {
            $_SESSION["user_type"] = $user_type;
            $db->route("orders?login=success");
        }
    } else {
        $db->route("index?login=error");
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>ورود به سیستم</title>
    <style>
        body {
            height: 100vh;
            direction: ltr !important;
        }

        input {
            direction: rtl;
        }

        .card img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
        }

        .form {
            background: var(--bg1);
        }

        .btn-info {
            position: fixed;
            top: 20px;
            right: 20px;
        }
    </style>
</head>

<body class="d-flex justify-content-center align-items-center px-sm-5 p-md-0 bg-light">
    <div class="col-12 col-sm-3 col-md-4 col-xl-3">
        <form method="POST" class="form card p-4 p-sm-5 needs-validation" novalidate>
            <img src="assets/img/logo.png" class="m-auto p-2 shadow">
            <h2 class="text-center pb-3 pt-4 text-light font-weight-bold text-primary font-bt">خوش آمدید</h2>
            <?php
            if (isset($_GET["login"])) {
                if ($_GET["login"] == "error") {
                    ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button class="close" data-dismiss="alert">&times;</button>
                        نام کاربری یا پسورد اشتباه است
                    </div>
                    <?php
                }
            }
            ?>
            <div class="input-group">
                <input type="text" name="username" class="form-control" placeholder="نام کاربری" required
                    autocomplete="off">
                <div class="input-group-append">
                    <span class="input-group-text ico">person</span>
                </div>
            </div>
            <div class="input-group my-2">
                <div class="input-group-prepend">
                    <span class="input-group-text ico eye">visibility</span>
                </div>
                <input type="password" name="password" class="form-control pass" placeholder="پسورد" required
                    autocomplete="off">
                <div class="input-group-append">
                    <span class="input-group-text ico">lock</span>
                </div>
            </div>
            <div class="form-group">
                <select name="user_type" class="form-control">
                    <option value="admin">ادمین</option>
                    <option value="user">کاربر</option>
                </select>
            </div>
            <!-- <a href="" class="mb-2">فراموشی پسورد؟</a> -->
            <button type="submit" name="login" class="btn btn-primary text-light w-100 pb-2">ورود به سیستم</button>
        </form>
    </div>


    <footer class="position-absolute w-100 d-flex justify-content-between align-items-center px-4"
        style="bottom: 0; left: 0;">
        <div class="row w-100 d-none d-md-flex">
            <div class="col-md-6 text-left">
                <p class="p-0 text-muted">Copyright &copy;2024 Developed by Qasim Sarwari.</p>
            </div>
            <div class="col-md-6 pr-0">
                <p class="p-0 text-muted">0798678624 - 0730238892</p>
            </div>
        </div>
    </footer>

    <?php require_once "includes/footer.php" ?>
</body>

</html>