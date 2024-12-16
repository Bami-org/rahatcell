<?php
require_once "includes/conn.php";
$sql = null;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = $db->query("select * from api_credentials where id=$id");
    if ($sql->num_rows == 1) {
        $sql = $sql->fetch_all(
            MYSQLI_ASSOC
        );

    } else {
        $sql = null;
    }
}
if (isset($_POST['edit'])) {
    $sql = $db->update(
        "api_credentials",
        [
            "dealer_code" => $db->clean_input($_POST["dealer_code"]),
            "username" => $db->clean_input($_POST["username"]),
            "password" => $db->clean_input($_POST["password"]),
            "base_url" => $db->clean_input($_POST["base_url"]),
            "my_loan" => $db->clean_input($_POST["my_loan"]),
            "my_money" => $db->clean_input($_POST["my_money"]),
        ],
        "id=" . $db->clean_input($_GET["id"])
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
    <title>آبدیت API</title>
</head>

<body>
    <?php require_once "menu.php"; ?>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h2>تغییرات API</h2>
            </div>
            <div class="card-body">
                <!-- Input fields -->
                <?php if ($sql != null) { ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="dealer_code" class="form-label">Dealer Code</label>
                            <input type="text" class="form-control" id="dealer_code" name="dealer_code"
                                placeholder="Enter Dealer Code" value="<?php echo $sql[0]['dealer_code']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username"
                                placeholder="Enter Username" value="<?php echo $sql[0]['username']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Enter Password" value="<?php echo $sql[0]['password']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="base_url" class="form-label">Base URL</label>
                            <input type="url" class="form-control" id="base_url" name="base_url"
                                placeholder="Enter Base URL" value="<?php echo $sql[0]['base_url']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="my_loan" class="form-label">My Loan</label>
                            <input type="text" class="form-control" id="my_loan" name="my_loan"
                                placeholder="Enter Loan Amount" value="<?php echo $sql[0]['my_loan']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="my_money" class="form-label">My Money</label>
                            <input type="text" class="form-control" id="my_money" name="my_money"
                                placeholder="Enter Money Amount" value="<?php echo $sql[0]['my_money']; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary" name="edit">Submit</button>
                    </form>
                <?php } ?>

            </div>
        </div>
    </div>


    <?php require_once "includes/footer.php"; ?>
</body>

</html>