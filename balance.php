<?php require_once "includes/conn.php";

$cDate = date('Y-m-d h:i:s');

if (isset($_POST["add"])) {
    if (!isset($_POST["customer_id"])) {
        $_SESSION["cs_err"] = "لطفا مشتری را انتخاب کنید!";
    } else {
        unset($_SESSION["cs_err"]);
        $check_balance = $db->query("SELECT * FROM balance WHERE customer_id =" . $db->clean_input($_POST["customer_id"]));
        $balance = $db->clean_input($_POST["balance"]);
        $description = $db->clean_input($_POST["description"]);
        if ($check_balance->num_rows > 0) {
            $sql = $db->query("UPDATE balance SET balance = balance + $balance, `description`= '$description', updated = '$cDate'  WHERE customer_id=" . $db->clean_input($_POST["customer_id"]));
        } else {
            $sql = $db->insert("balance", [
                "customer_id" => $db->clean_input($_POST["customer_id"]),
                "balance" => $db->clean_input($_POST["balance"]),
                "description" => $db->clean_input($_POST["description"]),
            ]);
        }
        if ($sql) {
            $db->insert("transactions", [
                "customer_id" => $db->clean_input($_POST["customer_id"]),
                "amount" => $db->clean_input($_POST["balance"]),
                "tr_type" => "Receipt",
                "category" => "balance",
                "description" => $db->clean_input($_POST["description"]),
            ]);
            $db->route("balance?opr=success");
        } else {
            $db->show_err();
        }
    }
}

if (isset($_POST["update"])) {
    
     $balance = $db->clean_input($_POST["balance"]);
    $tr_type = $db->clean_input($_POST["tr_type"]);
    if ($tr_type == 'Payment') {
        $sql = $db->query("UPDATE balance SET balance = balance - $balance WHERE id=" . $db->clean_input($_POST["balance_id"]));
    } else {
        $sql = $db->query("UPDATE balance SET balance = balance + $balance WHERE id=" . $db->clean_input($_POST["balance_id"]));
    }
    if ($sql) {
        $db->insert("transactions", [
            "customer_id" => $db->clean_input($_POST["customer_id"]),
            "amount" => $db->clean_input($_POST["balance"]),
            "tr_type" => $tr_type,
            "category"=> 'balance',
            "description" => $db->clean_input($_POST["description"]),
        ]);
        $db->route("balance?opr=success");
    } else {
        $db->show_err();
    }
    
    // $sql = $db->update(
    //     "balance",
    //     [
    //         "customer_id" => $db->clean_input($_POST["customer_id"]),
    //         "balance" => $db->clean_input($_POST["balance"]),
    //         "description" => $db->clean_input($_POST["description"]),
    //         "updated" => $cDate
    //     ],
    //     "id=" . $db->clean_input($_POST["balance_id"])
    // );
    // if ($sql) {
    //     $db->insert("transactions", [
    //         "customer_id" => $db->clean_input($_POST["customer_id"]),
    //         "amount" => $db->clean_input($_POST["balance"]),
    //         "tr_type" => $db->clean_input($_POST["tr_type"]),
    //         "category" => "balance",
    //         "description" => $db->clean_input($_POST["description"]),
    //     ]);
    //     $db->route("balance?opr=success");
    // } else {
    //     $db->show_err();
    // }
}

// check for today balance added

if (isset($_GET["today"])) {
    $day = date('d');
    $month = date('m');
    $year = date('Y');
    $sql = $db->query("SELECT balance.*, customer.name as customer,currency.name as currency,customer.parent_id,currency.id as c_id
FROM balance 
LEFT JOIN customer ON balance.customer_id = customer.id
LEFT JOIN currency ON customer.currency_id = currency.id
WHERE (DAY(balance.updated) = '$day' AND MONTH(balance.updated) = '$month' AND YEAR(balance.updated) = '$year') AND customer.parent_id = 0  ORDER BY updated DESC");
} else {
    $sql = $db->query("SELECT balance.*, customer.name as customer,currency.name as currency,currency.id as c_id,customer.parent_id FROM balance
 LEFT JOIN customer ON balance.customer_id = customer.id
 LEFT JOIN currency ON customer.currency_id = currency.id
 WHERE customer.parent_id = 0
 ORDER BY updated DESC");
}

if (isset($_POST["search"])) {
    $fromDate = $db->clean_input($_POST["fromDate"]);
    $toDate = $db->clean_input($_POST["toDate"]);
    $sql = $db->query("SELECT balance.*, customer.name as customer,currency.name as currency,currency.id as c_id FROM balance
    LEFT JOIN customer ON balance.customer_id = customer.id
    LEFT JOIN currency ON customer.currency_id = currency.id
    WHERE customer.parent_id = 0 AND DATE(balance.updated) BETWEEN '$fromDate' AND '$toDate'
    ORDER BY balance.id DESC");
}

$row = $sql->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <link rel="stylesheet" href="assets/css/select2.min.css">
    <title>بیلانس</title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li><span class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item">بیلانس ها</li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <form method="post" class="card needs-validation" onsubmit="handleBtn()">
            <div class="card-header">
                <h2>ثبت بیلانس</h2>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION["cs_err"])) { ?>
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <?= $_SESSION["cs_err"] ?>
                        <button class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php } ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="customer_id">مشتری:</label>
                            <select type="text" id="customer_id" name="customer_id" class="form-control select2" onchange="getCurrency(this.value)">
                                <option selected disabled>انتخاب</option>
                                <?php
                                $c_sql = $db->query("SELECT id,name,username FROM customer ORDER BY name,username");
                                if ($c_sql->num_rows > 0) {
                                    $c_row = $c_sql->fetch_assoc();
                                    do { ?>
                                <option value="<?= $c_row["id"] ?>">
                                    <?= $c_row["username"] . " - " ?><?= $c_row["name"] ?>
                                </option>
                                <?php } while ($c_row = $c_sql->fetch_assoc());
                                } else { ?>
                                <option selected disabled>هنوز ثبت نشده</option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="balance">بیلانس:</label>
                            <input type="text" id="balance" name="balance" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="currency">ارز:</label>
                            <input type="text" id="currency" class="form-control" disabled>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description">توضیحات:</label>
                            <input type="text" id="description" name="description" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" name="add" class="btn btn-primary add-btn">ثبت کردن</button>
                <button type="reset" class="btn btn-danger">انصراف</button>
            </div>
        </form>
        <hr>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>بیلانس ها</h3>
                <form class="form-inline needs-validation" method="post" novalidate>
                    <?php if (isset($_POST["search"])) { ?>
                        <a href="balance" class="btn btn-danger bt-ico m-0 py-1 px-2 ml-2 pb-0" data-toggle="tooltip" title="حالت عادی"><span class="h5 ico p-0 m-0">restore</span></a><?php } ?>
                    <label for="fromDate">از تاریخ:</label>
                    <input type="date" class="form-control mx-2" value="<?= isset($_POST["fromDate"]) ? $_POST["fromDate"] : date("Y-m-d") ?>" min="2000-01-01" name="fromDate" id="fromDate" required>
                    <label for="toDate">تا تاریخ:</label>
                    <input type="date" class="form-control mx-2" value="<?= isset($_POST["toDate"]) ? $_POST["toDate"] : date("Y-m-d") ?>" max="2050-12-31" name="toDate" id="toDate" required>
                    <button type="submit" name="search" class="btn btn-primary bt-ico">جستجو <span class="ico">search</span></button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th>مشتری</th>
                                <th>بیلانس</th>
                                <th>ارز</th>
                                <th>توضیحات</th>
                                <th>تاریخ</th>
                                <th>بروزرسانی</th>
                                <th style="width: 10%;">عملکرد</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($sql->num_rows > 0) {
                                $n = 1;
                                do { ?>
                                    <tr>
                                        <td><?= $n++ ?></td>
                                        <td><?= $row["customer"] ?></td>
                                        <td class="text-<?= $row["balance"] < 0 ? "danger" : "success" ?>"><?= $row["c_id"]==1? number_format($row["balance"]): $row["balance"] ?></td>
                                        <td><?= $row["currency"] ?></td>
                                        <td><?= $row["description"] ?></td>
                                        <td><?= $db->convertFullDate($row["created"],$setting["date_type"]) ?></td>
                                        <td><?= $db->convertFullDate($row["updated"],$setting["date_type"]) ?></td>
                                        <td class="text-center p-0 no-print">
                                            <div class="btn-group" dir="ltr">
                                        <button type="button" class="btn btn-danger btn-sm pb-0 pt-2"
                                            data-toggle="tooltip" title="حذف حساب"
                                            onclick="showQ('<?= $row['id'] ?>')"><span
                                                class="ico h6">delete</span></button>
                                        <button type="button" class="btn btn-success btn-sm pb-0 pt-2"
                                            data-toggle="tooltip" title="اضافه کردن"
                                            onclick="editBalance('<?= $row['id'] ?>','Receipt')"><span
                                                class="ico h6">add</span></button>
                                        <button type="button" class="btn btn-warning btn-sm pb-0 pt-2"
                                            data-toggle="tooltip" title="کم کردن"
                                            onclick="editBalance('<?= $row['id'] ?>','Payment')"><span
                                                class="ico h6">remove</span></button>
                                    </div>
                                </td>
                                        </td>
                                    </tr>
                            <?php } while ($row = $sql->fetch_assoc());
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- edit modal -->
    <div id="edit-modal" class="modal fade" data-backdrop="static">
        <div class="modal-dialog">
            <form method="POST" class="modal-content needs-validation" novalidate>
                <div class="modal-header">
                    <h2 class="edit-title"></h2>
                    <button class="btn btn-danger" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="balance_id" name="balance_id">
                    <input type="hidden" id="customer_id" name="customer_id">
                    <input type="hidden" id="tr_type" name="tr_type">
                    <div class="form-group">
                        <label for="balance">بیلانس:</label>
                        <input type="text" id="balance" name="balance" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="description">توضیحات:</label>
                        <input type="text" id="description" name="description" class="form-control">
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
        // for geting customer currency
        function getCurrency(c_id) {
            $.ajax({
                type: "get",
                url: "ajax/get_currency",
                data: {
                    customer_id: c_id
                },
                success: function(response) {
                    var res = JSON.parse(response);
                    $("form #currency_id").val(res["currency_id"])
                    $("form #currency").val(res["name"])
                }
            });
        }
        // for delete
        function showQ(id) {
            delQ("balance_id=" + id)
        }
        
            //! edit balance

    function editBalance(id, tr_type) {
        $("#edit-modal #balance_id").val(id);
        $("#edit-modal #tr_type").val(tr_type);
        $("#edit-modal .edit-title").html(tr_type == 'Payment' ? 'کم کردن بیلانس' : 'اضافه کردن بیلانس');
        $.ajax({
            type: "get",
            url: "ajax/get_info",
            data: {
                balance_id: id
            },
            success: function(response) {
                var res = JSON.parse(response);
                $("#edit-modal #customer_id").val(res["customer_id"]);
            },
        });
        $("#edit-modal").modal('show');
    }

        
    function handleBtn(){
        setTimeout(()=>{
            $("form .add-btn").attr("disabled","disabled");
        },150)
    }
    </script>
</body>

</html>