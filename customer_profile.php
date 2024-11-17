<?php require_once "includes/conn.php";

$id;
if (isset($_GET["id"])) {
    $id = $_GET["id"];
} else {
    $db->route("customer");
}

if (isset($_POST["update"])) {
    $sql = $db->update(
        "customer",
        [
            "name" => $db->clean_input($_POST["name"]),
            "phone" => $db->clean_input($_POST["phone"]),
            "address" => $db->clean_input($_POST["address"]),
            "username" => $db->clean_input($_POST["username"]),
            "password" => $db->clean_input($_POST["password"]),
            "pin_code" => $db->clean_input($_POST["pin_code"]),
            "status" => $db->clean_input($_POST["status"])
        ],
        "id=" . $db->clean_input($_POST["customer_id"])
    );
    if ($sql) {
        if ($sql) {
            $db->route("customer_profile?id=$id&update=success");
        } else {
            $db->show_err();
        }
    }
}

$info = mysqli_fetch_assoc($db->query("SELECT * FROM customer WHERE id =$id"));
$balance = mysqli_fetch_assoc($db->query("SELECT SUM(balance) as total FROM balance WHERE customer_id =$id"));
$payment = mysqli_fetch_assoc($db->query("SELECT SUM(pay_amount) as total FROM payment WHERE customer_id =$id"));
$sub_customer_sql = $db->query("SELECT currency.name as c_name,currency.id as c_id,balance.balance as balance, customer.* FROM customer LEFT JOIN currency ON customer.currency_id=currency.id LEFT JOIN balance ON balance.customer_id = customer.id WHERE customer.parent_id=$id ORDER BY customer.id DESC");
$row = $sub_customer_sql->fetch_assoc();
$currency = mysqli_fetch_assoc($db->query("SELECT currency_id FROM customer WHERE id=$id"));
switch ($currency["currency_id"]) {
    case 1:
        $customer_sales = mysqli_fetch_assoc($db->query("SELECT 
SUM(product.toman_sale_price) as amount
FROM orders
LEFT JOIN product ON orders.product_id = product.id
LEFT JOIN customer ON orders.customer_id = customer.id
WHERE orders.status='Success' AND (orders.customer_id = $id OR customer.parent_id=$id)"));
        break;
    case 2:
        $customer_sales = mysqli_fetch_assoc($db->query("SELECT 
SUM(product.dollar_sale_price) as amount
FROM orders
LEFT JOIN product ON orders.product_id = product.id
LEFT JOIN customer ON orders.customer_id = customer.id
WHERE orders.status='Success' AND (orders.customer_id = $id OR customer.parent_id=$id)"));
        break;
    case 3:
        $customer_sales = mysqli_fetch_assoc($db->query("SELECT 
SUM(product.lyra_sale_price) as amount
FROM orders
LEFT JOIN product ON orders.product_id = product.id
LEFT JOIN customer ON orders.customer_id = customer.id
WHERE orders.status='Success' AND (orders.customer_id = $id OR customer.parent_id=$id)"));
        break;
    case 4:
        $customer_sales = mysqli_fetch_assoc($db->query("SELECT 
SUM(product.euro_sale_price) as amount
FROM orders
LEFT JOIN product ON orders.product_id = product.id
LEFT JOIN customer ON orders.customer_id = customer.id
WHERE orders.status='Success' AND (orders.customer_id = $id OR customer.parent_id=$id)"));
        break;
    default:
        $customer_sales = mysqli_fetch_assoc($db->query("SELECT 
    SUM(product.toman_sale_price) as amount
    FROM orders
    LEFT JOIN product ON orders.product_id = product.id
    LEFT JOIN customer ON orders.customer_id = customer.id
    WHERE orders.status='Success' AND (orders.customer_id = $id OR customer.parent_id=$id)"));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>پروفایل مشتری</title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li><span
                    class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item"><a href="customer">مشتریان</a></li><span
                    class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item">پروفایل</li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h2>پروفایل مشتری</h2>
                <button onclick="history.back()" class="btn btn-info bt-ico">برگشت <span
                        class="ico">arrow_back</span></button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <span class="ico" style="font-size: 100px;">account_circle</span>
                            <h4><?= $info["name"] ?></h4>
                            <h5><?= $info["phone"] ?></h5>
                            <h5><?= $info["address"] ?></h5>
                            <table class="table-bordered">
                                <thead>
                                    <tr>
                                        <th class="py-1">بیلانس</th>
                                        <th class="py-1">پرداخت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="py-2 text-success font-weight-bold">
                                            <?= $balance["total"] ?>
                                        </td>
                                        <td class="py-2 text-danger font-weight-bold">
                                            <?= $payment["total"] ?></td>
                                    </tr>
                                    <tr title="<?php echo $info["parent_id"] == 0 ? "مجموع فروشات " . $info["name"] . " و مشتریان زیر دستش." : "مجموع فروشات " . $info["name"] ?>"
                                        data-toggle="tooltip">
                                        <td class="py-2">فروشات</td>
                                        <td class="h5 my-0 py-2"><?= $customer_sales["amount"] ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if ($sub_customer_sql->num_rows > 0) { ?>
                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-header">
                                <h3>مشتریان</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm table-striped text-center">
                                        <thead>
                                            <tr>
                                                <th style="width: 5%;">#</th>
                                                <th>اسم</th>
                                                <th>شماره</th>
                                                <th>آدرس</th>
                                                <th>بیلانس</th>
                                                <th>ارز</th>
                                                <th>نام کاربری</th>
                                                <th>تاریخ ثبت</th>
                                                <th style="width: 12%;">عملکرد</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                if ($sub_customer_sql->num_rows > 0) {
                                                    $n = 1;
                                                    do { ?>
                                            <tr class="<?= $row["status"] == "Deactive" ? "table-danger" : "" ?>">
                                                <td><?= $n++ ?></td>
                                                <td><?= $row["name"] ?></td>
                                                <td><?= $row["phone"] ?></td>
                                                <td><?= $row["address"] ?></td>
                                                <td><?= $row["c_id"]==1? number_format($row["balance"]??0) : $row["balance"] ?></td>
                                                <td><?= $row["c_name"] ?></td>
                                                <td><?= $row["username"] ?></td>
                                                <td><?= $db->convertFullDate($row["created"], $setting["date_type"]) ?>
                                                </td>
                                                <td class="text-center p-0 no-print">
                                                    <div class="btn-group" dir="ltr">
                                                        <button class="btn btn-danger btn-sm pb-0 pt-2"
                                                            onclick="showQ('<?= $row['id'] ?>')"><span
                                                                class="ico h6">delete</span></button>
                                                        <a href="customer_profile?id=<?= $row["id"] ?>"
                                                            class="btn btn-info btn-sm pb-0 pt-2"><span
                                                                class="ico h6">person</span></a>
                                                        <button class="btn btn-success btn-sm pb-0 pt-2"
                                                            onclick="getInfo('<?= $row['id'] ?>')"><span
                                                                class="ico h6">edit</span></button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php } while ($row = $sub_customer_sql->fetch_assoc());
                                                } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="address">آدرس:</label>
                                <input type="text" id="address" name="address" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="username">نام کاربری:</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                        </div>
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
                    </div>
                    <div class="row">
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