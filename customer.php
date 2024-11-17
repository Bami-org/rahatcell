<?php require_once "includes/conn.php";

if (isset($_POST["update"])) {
    $sql = $db->update(
        "customer",
        [
            "name" => $db->clean_input($_POST["name"]),
            "phone" => $db->clean_input($_POST["phone"]),
            "address" => $db->clean_input($_POST["address"]),
            "currency_id" => $db->clean_input($_POST["currency_id"]),
            "username" => $db->clean_input($_POST["username"]),
            "password" => $db->clean_input($_POST["password"]),
            "pin_code" => $db->clean_input($_POST["pin_code"]),
            "status" => $db->clean_input($_POST["status"])
        ],
        "id=" . $db->clean_input($_POST["customer_id"])
    );
    if ($sql) {
        if ($sql) {
            $db->route("customer?opr=success");
        } else {
            $db->show_err();
        }
    }
}


$sql = $db->query("SELECT currency.name as c_name,balance.balance as balance,currency.id as c_id, customer.* FROM customer LEFT JOIN currency ON customer.currency_id=currency.id LEFT JOIN balance ON balance.customer_id = customer.id WHERE customer.parent_id=0 ORDER BY customer.id DESC");
$row = $sql->fetch_assoc();

$balance_sql = $db->query("SELECT
SUM(balance.balance) as balance,currency.name as currency,COUNT(customer.id) as cs_count FROM balance
LEFT JOIN customer ON balance.customer_id = customer.id
LEFT JOIN currency ON customer.currency_id = currency.id
WHERE customer.parent_id=0
GROUP BY customer.currency_id
");


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>مشتریان</title>
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
                <li class="mx-0 list-inline-item">لیست</li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2>لیست مشتریان</h2>
                <div class="btn-group" dir="ltr">
                    <button class="btn btn-info bt-ico" onclick="print()">پرینت <span class="ico">print</span></button>
                    <a href="add_customer" class="btn btn-primary bt-ico">جدید <span class="ico">add</span></a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th>اسم مشتری</th>
                                <th>شماره تماس</th>
                                <th>آدرس</th>
                                <th>بلانس</th>
                                <th>ارز</th>
                                <th>نام کاربری</th>
                                <th>تاریخ ثبت</th>
                                <th style="width: 9%;">عملکرد</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($sql->num_rows > 0) {
                                $n = 1;
                                do { ?>
                            <tr class="<?= $row["status"] == "Deactive" ? "table-danger" : "" ?>">
                                <td><?= $n++ ?></td>
                                <td><?= $row["name"] ?></td>
                                <td><?= $row["phone"] ?></td>
                                <td><?= $row["address"] ?></td>
                                <td><?= $row["c_id"] == 1? number_format($row["balance"]??0): $row["balance"] ?></td>
                                <td><?= $row["c_name"] ?></td>
                                <td><?= $row["username"] ?></td>
                                <td><?= $db->convertFullDate($row["created"],$setting["date_type"]) ?></td>
                                <td class="text-center p-0 no-print">
                                    <div class="btn-group" dir="ltr">
                                        <button class="btn btn-danger btn-sm pb-0 pt-2"
                                            onclick="showQ('<?= $row['id'] ?>')"><span
                                                class="ico h6">delete</span></button>
                                        <a href="customer_profile?id=<?= $row["id"] ?>"
                                            class="btn btn-info btn-sm pb-0 pt-2"><span class="ico h6">person</span></a>
                                        <button class="btn btn-success btn-sm pb-0 pt-2"
                                            onclick="getInfo('<?= $row['id'] ?>')"><span
                                                class="ico h6">edit</span></button>
                                    </div>
                                </td>
                            </tr>
                            <?php } while ($row = $sql->fetch_assoc());
                            }  ?>
                        </tbody>
                    </table>
                    <hr>
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>مجموع بیلانس</th>
                                <th>نوع ارز</th>
                                <th>تعداد مشتریان اصلی دارای بیلانس</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($balance_sql->num_rows > 0) {
                                while ($balance_row = $balance_sql->fetch_assoc()) { ?>
                            <tr>
                                <td class="py-2 font-weight-bold"><?= number_format($balance_row["balance"]) ?></td>
                                <td class="py-2"><?= $balance_row["currency"] ?></td>
                                <td class="py-2"><?= $balance_row["cs_count"] ?></td>
                            </tr>
                            <?php }
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <!-- edit modal -->
    <div id="edit-modal" class="modal fade" data-backdrop="static">
        <div class="modal-dialog modal-xl">
            <form method="POST" class="modal-content needs-validation" novalidate>
                <div class="modal-header">
                    <h2>ویرایش معلومات مشتری</h2>
                    <button class="btn btn-danger" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="customer_id" name="customer_id">
                    <div class="row">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">اسم:</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="phone">شماره تماس:</label>
                                <input type="text" id="phone" name="phone" class="form-control" required>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="address">آدرس:</label>
                                <input type="text" id="address" name="address" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="currency_id">ارز:</label>
                                <select id="currency_id" name="currency_id" class="form-control">
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="username">نام کاربری:</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="password">پسورد:</label>
                                <input type="text" id="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="pin_code">پین کود:</label>
                                <input type="text" id="pin_code" name="pin_code" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
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
                <div class="modal-footer justify-content-start">
                    <button class="btn btn-primary" type="submit" name="update">ذخیره تغییرات</button>
                    <button class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                </div>
            </form>
        </div>
    </div>
    <!-- // edit modal -->

    <?php require_once "includes/footer.php" ?>
    <script>
    // for delete
    function showQ(id) {
        delQ("customer_id=" + id)
    }

    // for edit
    function getInfo(id) {
        $("#edit-modal #customer_id").val(id);
        $.ajax({
            type: "get",
            url: "ajax/get_info",
            data: {
                customer_id: id
            },
            success: function(response) {
                var res = JSON.parse(response);
                $("#edit-modal #name").val(res["name"]);
                $("#edit-modal #phone").val(res["phone"]);
                $("#edit-modal #address").val(res["address"]);
                $("#edit-modal #currency_id").val(res["currency_id"]);
                $("#edit-modal #username").val(res["username"]);
                $("#edit-modal #password").val(res["password"]);
                $("#edit-modal #pin_code").val(res["pin_code"]);
                $("#edit-modal #status").val(res["status"]);
                $("#edit-modal").modal('show')
            }
        });
    }
    </script>
</body>

</html>