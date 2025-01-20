<?php
require_once "includes/conn.php";

if (isset($_POST['add'])) {
    $sql = $db->insert(
        "api_credentials",
        [
            "dealer_code" => $db->clean_input($_POST["dealer_code"]),
            "username" => $db->clean_input($_POST["username"]),
            "password" => $db->clean_input($_POST["password"]),
            "base_url" => $db->clean_input($_POST["base_url"]),
            "my_loan" => $db->clean_input($_POST["my_loan"]),
            "my_money" => $db->clean_input($_POST["my_money"]),
        ],

    );
    if ($sql) {
        $db->route("lone");
    } else {
        $db->show_err();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php"; ?>
    <title>اضافه کردن API</title>
</head>

<body>
    <?php require_once "menu.php"; ?>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h2>اضافه کردن API</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="dealer_code" class="form-label">کد فروشنده</label>
                        <input type="text" class="form-control" id="dealer_code" name="dealer_code"
                            placeholder="کد فروشنده را وارد کنید">
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">نام کاربری</label>
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="نام کاربری را وارد کنید">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">رمز عبور</label>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="رمز عبور را وارد کنید">
                    </div>
                    <div class="mb-3">
                        <label for="base_url" class="form-label">آدرس پایه</label>
                        <input type="url" class="form-control" id="base_url" name="base_url"
                            placeholder="آدرس پایه را وارد کنید">
                    </div>
                    <div class="mb-3">
                        <label for="my_loan" class="form-label">وام من</label>
                        <input type="text" class="form-control" id="my_loan" name="my_loan"
                            placeholder="مقدار وام را وارد کنید">
                    </div>
                    <div class="mb-3">
                        <label for="my_money" class="form-label">پول من</label>
                        <input type="text" class="form-control" id="my_money" name="my_money"
                            placeholder="مقدار پول را وارد کنید">
                    </div>
                    <button type="submit" class="btn btn-primary" name="add">ارسال</button>
                </form>


            </div>
        </div>
    </div>


    <?php require_once "includes/footer.php"; ?>
</body>

</html>