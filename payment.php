<?php require_once "includes/conn.php";

if (isset($_POST["add"])) {
    if (!isset($_POST["customer_id"])) {
        $_SESSION["py_err"] = "لطفا مشتری را انتخاب کنید!";
    } else {
        unset($_SESSION["py_err"]);
        $sql = $db->insert(
            "payment",
            [
                "customer_id" => $db->clean_input($_POST["customer_id"]),
                "pay_amount" => $db->clean_input($_POST["pay_amount"]),
                "bank_id" => $db->clean_input($_POST["bank_id"]),
                "description" => $db->clean_input($_POST["description"]),
            ]
        );

        if ($sql) {
            $db->insert("transactions", [
                "customer_id" => $db->clean_input($_POST["customer_id"]),
                "amount" => $db->clean_input($_POST["pay_amount"]),
                "tr_type" => "Payment",
                "description" => $db->clean_input($_POST["description"]),
            ]);
            $db->route("payment?opr=success");
        } else {
            $db->show_err();
        }
    }
}

if (isset($_POST["update"])) {
    $sql = $db->update(
        "payment",
        [
            "customer_id" => $db->clean_input($_POST["customer_id"]),
            "pay_amount" => $db->clean_input($_POST["pay_amount"]),
            "description" => $db->clean_input($_POST["description"]),
            "updated" => date("Y-m-d h:i:s")
        ],
        "id=" . $db->clean_input($_POST["payment_id"])
    );
    if ($sql) {
        $db->route("payment?opr=success");
    } else {
        $db->show_err();
    }
}

if (isset($_GET["customer_id"])) {
    $id = $_GET["customer_id"];
    $sql = $db->query("SELECT payment.*,customer.name,bank.name as bank FROM payment INNER JOIN customer ON payment.customer_id=customer.id LEFT JOIN bank ON payment.bank_id=bank.id WHERE payment.customer_id=$id  ORDER BY payment.id");
    $row = $sql->fetch_assoc();
} else {
    $sql = $db->query("SELECT payment.*,customer.name,bank.name as bank FROM payment INNER JOIN customer ON payment.customer_id=customer.id LEFT JOIN bank ON payment.bank_id=bank.id ORDER BY payment.id");
    $row = $sql->fetch_assoc();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <link rel="stylesheet" href="assets/css/select2.min.css">
    <title>پرداخت ها</title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li><span
                    class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item">پرداخت ها</li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <form method="post" class="card needs-validation" novalidate>
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2>ثبت پرداخت</h2>
                <?= isset($_GET["customer_id"]) ? '<button class="btn btn-info bt-ico" onclick="history.back()">برگشت <span class="ico">arrow_back</span></button>' : '' ?>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION["py_err"])) { ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <?= $_SESSION["py_err"] ?>
                    <button class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php } ?>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="customer_id">مشتری:</label>
                            <select name="customer_id" id="customer_id" class="form-control select2">
                                <option selected disabled>انتخاب</option>
                                <?php
                                $c_sql = $db->query("SELECT id,name FROM customer  WHERE parent_id=0 ORDER BY id");
                                if ($c_sql->num_rows > 0) {
                                    $c_row = $c_sql->fetch_assoc();
                                    do { ?>
                                <option value="<?= $c_row["id"] ?>"><?= $c_row["name"] ?></option>
                                <?php } while ($c_row = $c_sql->fetch_assoc());
                                } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="pay_amount">مقدار پرداخت:</label>
                            <input type="text" name="pay_amount" id="pay_amount" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="bank_id">بانک:</label>
                            <select id="bank_id" name="bank_id" class="form-control">
                                <option selected disabled>انتخاب</option>
                                <?php
                                $b_sql = $db->query("SELECT id,name FROM bank");
                                if ($b_sql->num_rows > 0) {
                                    $b_row = $b_sql->fetch_assoc();
                                    do { ?>
                                <option value="<?= $b_row["id"] ?>"><?= $b_row["name"] ?></option>
                                <?php } while ($b_row = $b_sql->fetch_assoc());
                                } else { ?>
                                <option selected disabled>هنوز ثبت نشده</option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="description">توضیحات:</label>
                            <input type="text" name="description" id="description" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" name="add" class="btn btn-primary">ثبت کردن</button>
                <button type="reset" class="btn btn-danger">انصراف</button>
            </div>
        </form>
        <hr>
        <div class="card">
            <div class="card-header">
                <h3>پرداخت ها</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>مشتری</th>
                            <th>مقدار</th>
                            <th>بانک</th>
                            <th>توضیحات</th>
                            <th>تاریخ</th>
                            <th>بروزرسانی</th>
                            <th>عملکرد</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($sql->num_rows > 0) {
                            $i = 1;
                            do { ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $row["name"] ?></td>
                            <td><?= number_format($row["pay_amount"] ?? 0) ?></td>
                            <td><?= $row["bank"] ?></td>
                            <td><?= $row["description"] ?></td>
                            <td><?= $row["created"] ?></td>
                            <td><?= $row["updated"] ?></td>
                            <td class="text-center p-0 no-print">
                                <div class="btn-group" dir="ltr">
                                    <button class="btn btn-danger btn-sm pb-0 pt-2"
                                        onclick="showQ('<?= $row['id'] ?>')"><span class="ico h6">delete</span></button>
                                    <button class="btn btn-success btn-sm pb-0 pt-2"
                                        onclick="getInfo('<?= $row['id'] ?>')"><span class="ico h6">edit</span></button>
                                </div>
                            </td>
                        </tr>
                        <?php } while ($row = $sql->fetch_assoc());
                        }  ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- edit modal -->
    <div id="edit-modal" class="modal fade" data-backdrop="static">
        <div class="modal-dialog">
            <form method="POST" class="modal-content needs-validation" novalidate>
                <div class="modal-header">
                    <h2>ویرایش پرداخت</h2>
                    <button class="btn btn-danger" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="payment_id" name="payment_id">
                    <div class="form-group">
                        <label for="customer_id">مشتری:</label>
                        <select type="text" id="customer_id" name="customer_id" class="form-control">
                            <option selected disabled>انتخاب</option>
                            <?php
                            $c_sql = $db->query("SELECT id,name FROM customer");
                            if ($c_sql->num_rows > 0) {
                                $c_row = $c_sql->fetch_assoc();
                                do { ?>
                            <option value="<?= $c_row["id"] ?>"><?= $c_row["name"] ?></option>
                            <?php } while ($c_row = $c_sql->fetch_assoc());
                            } else { ?>
                            <option selected disabled>هنوز ثبت نشده</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pay_amount">مقدار پرداخت:</label>
                        <input type="text" name="pay_amount" id="pay_amount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="bank_id">بانک:</label>
                        <select id="bank_id" name="bank_id" class="form-control">
                            <option selected disabled>انتخاب</option>
                            <?php
                            $b_sql = $db->query("SELECT id,name FROM bank");
                            if ($b_sql->num_rows > 0) {
                                $b_row = $b_sql->fetch_assoc();
                                do { ?>
                            <option value="<?= $b_row["id"] ?>"><?= $b_row["name"] ?></option>
                            <?php } while ($b_row = $b_sql->fetch_assoc());
                            } else { ?>
                            <option selected disabled>هنوز ثبت نشده</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description">توضیحات:</label>
                        <input type="text" name="description" id="description" class="form-control">
                    </div>

                </div>
                <div class="modal-footer justify-content-start">
                    <button class="btn btn-primary" type="submit" name="update">ذخیره تغییرات</button>
                    <button class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                </div>
            </form>
        </div>
    </div>
    <!-- // edit modal -->

    <?php require_once "includes/footer.php" ?>
    <script src="assets/js/select2.full.min.js"></script>
    <script>
    $("form .select2").select2();
    // for delete
    function showQ(id) {
        delQ("payment_id=" + id)
    }

    // for update
    function getInfo(id) {
        $("#edit-modal #payment_id").val(id);
        $.ajax({
            type: "get",
            url: "ajax/get_info",
            data: {
                payment_id: id
            },
            success: function(response) {
                var res = JSON.parse(response);
                $("#edit-modal #customer_id").val(res["customer_id"]);
                $("#edit-modal #pay_amount").val(res["pay_amount"]);
                $("#edit-modal #bank_id").val(res["bank_id"]);
                $("#edit-modal #description").val(res["description"]);
                $("#edit-modal").modal('show');
            }
        });
    }
    </script>
</body>

</html>