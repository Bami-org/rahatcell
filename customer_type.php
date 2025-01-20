<?php require_once "includes/conn.php";

if (isset($_POST["add"])) {

    $sql = $db->insert(
        "customer_type",
        [
            "customer_type" => $db->clean_input($_POST["customer_type"]),
        ]
    );
    if ($sql) {
        $db->route("customer_type?opr=success");
    } else {
        $db->show_err();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>نوع مشتریان</title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li><span
                    class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item"><a href="customer">نوع مشتریان </a></li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <form method="post" class="card needs-validation" novalidate>
            <div class="card-header">
                <h2>ثبت نوع مشتری</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="customer_type">نوع مشتری</label>
                            <input type="text" id="customer_type" name="customer_type" class="form-control"
                                minlength="4" required placeholder="نوع مشتری">
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