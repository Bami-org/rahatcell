<?php require_once "includes/conn.php";

if (isset($_POST["add"])) {
    if (!isset($_POST["currency_id"])) {
        echo "<script>alert('لطفا ارز را انتخاب کنید!')</script>";
    } else {
        $sql = $db->insert(
            "customer",
            [
                "name" => $db->clean_input($_POST["name"]),
                "phone" => $_POST["phone"],
                "address" => $db->clean_input($_POST["address"]),
                "parent_id" => isset($_POST["parent_id"]) ? $db->clean_input($_POST["parent_id"]) : "0",
                "currency_id" => $db->clean_input($_POST["currency_id"]),
                "username" => $db->clean_input($_POST["username"]),
                "password" => $db->clean_input($_POST["password"]),
                "pin_code" => $db->clean_input($_POST["pin_code"]),
                "status" => $db->clean_input($_POST["status"]),
                "customer_type" => $db->clean_input($_POST["customer_type"]),

            ]
        );
        if ($sql) {
            $db->route("customer?opr=success");
        } else {
            $db->show_err();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>ثبت مشتریان</title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li><span
                    class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item"><a href="customer">مشتریان</a></li>
                <span class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item">ثبت</li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <form method="post" class="card needs-validation" novalidate>
            <div class="card-header">
                <h2>ثبت مشتری</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <div class=" d-flex justify-content-between align-items-center">
                                <label for="parent_id">مشتری:</label>
                                <span class="ico" data-toggle="tooltip"
                                    title="در صورتیکه مشتری فرعی باشد مشتری اصلی را انتخاب کنید!">help</span>
                            </div>
                            <select id="parent_id" name="parent_id" class="form-control"
                                onchange="getCurrency(this.value)">
                                <option selected disabled>انتخاب</option>
                                <?php
                                $cs_sql = $db->query("SELECT * FROM customer WHERE parent_id = 0  ORDER BY id");
                                $cs_row = $cs_sql->fetch_assoc();
                                if ($cs_sql->num_rows > 0) {
                                    do {
                                        ?>
                                        <option value="<?= $cs_row["id"] ?>"><?= $cs_row["name"] ?></option>
                                    <?php } while ($cs_row = $cs_sql->fetch_assoc());
                                } else { ?>
                                    <option disabled>هنوز ثبت نشده</option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="name">اسم:</label>
                            <input type="text" id="name" name="name" class="form-control" minlength="4" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="phone">شماره تماس:</label>
                            <input type="text" id="phone" name="phone" class="form-control" minlength="9" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="address">آدرس:</label>
                            <input type="text" id="address" name="address" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="currency_id">ارز:</label>
                            <select id="currency_id" name="currency_id" class="form-control"
                                onchange="setCurrency(this.value)" required>
                                <option selected disabled>انتخاب</option>
                                <?php
                                $c_sql = $db->query("SELECT * FROM currency ORDER BY id");
                                $c_row = $c_sql->fetch_assoc();
                                if ($c_sql->num_rows > 0) {
                                    do {
                                        ?>
                                        <option value="<?= $c_row["id"] ?>"><?= $c_row["name"] ?></option>
                                    <?php } while ($c_row = $c_sql->fetch_assoc());
                                } else { ?>
                                    <option disabled>هنوز ثبت نشده</option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="username">نام کاربری:</label>
                            <input type="text" id="username" name="username" class="form-control"
                                onkeyup="checkUsername(this.value)" minlength="4" required>
                            <div class="invalid-msg"></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="customer_type">نوع مشتری:</label>
                            <select id="customer_type" name="customer_type" class="form-control" required>
                                <option selected disabled>انتخاب</option>
                                <option value="عمده">عمده</option>
                                <option value="پرچون">پرچون</option>
                            </select>
                        </div>
                    </div>


                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="password">پسورد:</label>
                            <input type="text" id="password" name="password" class="form-control" minlength="4"
                                required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="pin_code">پین کود:</label>
                            <input type="text" id="pin_code" name="pin_code" class="form-control" value="1234">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">وضعیت حساب:</label>
                            <select id="status" name="status" class="form-control">
                                <option value="Active">فعال</option>
                                <option value="Deactive">غیر فعال</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" name="add" class="btn btn-primary">ثبت کردن</button>
                <button type="reset" class="btn btn-danger">انصراف</button>
            </div>
        </form>
    </div>
    <?php require_once "includes/footer.php" ?>

    <script>
        function setCurrency(id) {
            $("form #currency_id").val(id);
        }

        function getCurrency(id) {
            $.ajax({
                type: "get",
                url: "ajax/get_currency",
                data: {
                    customer_id: id
                },
                success: function (response) {
                    var res = JSON.parse(response);
                    //$("form #currency").val(res["currency_id"]);
                    $("form #currency_id").val(res["currency_id"]);
                    //$("form #currency").attr("disabled", "disabled");

                }
            });
        }

        function checkUsername(username) {
            $.ajax({
                type: "post",
                url: "ajax/check_username",
                data: {
                    username: username
                },
                success: function (response) {
                    var res = JSON.parse(response);
                    if (res["result"] == true) {
                        $(".invalid-msg").html(res["message"]).addClass("text-danger pt-2");
                    } else {
                        $(".invalid-msg").html('');
                    }
                }
            });
        }
    </script>

</body>

</html>