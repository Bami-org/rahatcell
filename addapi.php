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
                <!-- Input fields -->

                <form method="POST">
                    <div class="mb-3">
                        <label for="dealer_code" class="form-label">Dealer Code</label>
                        <input type="text" class="form-control" id="dealer_code" name="dealer_code"
                            placeholder="Enter Dealer Code">
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="Enter Username">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Enter Password">
                    </div>
                    <div class="mb-3">
                        <label for="base_url" class="form-label">Base URL</label>
                        <input type="url" class="form-control" id="base_url" name="base_url"
                            placeholder="Enter Base URL">
                    </div>
                    <div class="mb-3">
                        <label for="my_loan" class="form-label">My Loan</label>
                        <input type="text" class="form-control" id="my_loan" name="my_loan"
                            placeholder="Enter Loan Amount">
                    </div>
                    <div class="mb-3">
                        <label for="my_money" class="form-label">My Money</label>
                        <input type="text" class="form-control" id="my_money" name="my_money"
                            placeholder="Enter Money Amount">
                    </div>
                    <button type="submit" class="btn btn-primary" name="add">Submit</button>
                </form>


            </div>
        </div>
    </div>


    <?php require_once "includes/footer.php"; ?>
</body>

</html>