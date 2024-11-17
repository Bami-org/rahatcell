<?php require_once "includes/conn.php";


if (isset($_POST["update"])) {
    $customer_id = $db->clean_input($_POST["customer_id"]);
    $status = $db->clean_input($_POST["status"]);
    $detail = $db->clean_input($_POST["detail"]);
    $currency = mysqli_fetch_assoc($db->query("SELECT customer.name as cs_name, customer.currency_id,currency.name as currency FROM customer LEFT JOIN currency ON customer.currency_id = currency.id WHERE customer.id=$customer_id"));
    $cur = $currency["currency"];
    $sql = $db->update(
        "orders",
        [
            "status" => $status,
            "detail" => $detail,
            "updated" => date("Y-m-d h:i:s"),
        ],
        "id=" . $db->clean_input($_POST["order_id"])
    );
    $is_parent = $db->query("SELECT parent_id FROM customer WHERE id=$customer_id");
    $parent_id = $is_parent->fetch_assoc();
    if ($sql) {
        if ($status == "Rejected") {
            switch ($currency["currency_id"]) {
                case 1:
                    $price = mysqli_fetch_assoc($db->query("SELECT product.toman_sale_price as price, CONCAT(product.amount,' ',units.name,' - ',sub_category.name) as product
                    FROM product 
                    LEFT JOIN units ON product.unit_id = units.id
                    LEFT JOIN sub_category ON product.sub_category_id = sub_category.id
                    WHERE product.id =" . $db->clean_input($_POST["product_id"])));
                    $money = $price["price"];
                    $product = $price["product"];
                    $db->query("UPDATE balance SET balance = balance + $money WHERE customer_id =$customer_id");
                    if ($parent_id["parent_id"] > 0) {
                        $customer_n = $currency["cs_name"];
                        $db->query("UPDATE balance SET balance = balance + $money WHERE customer_id =" . $parent_id["parent_id"]);
                        $db->insert(
                            "transactions",
                            [
                                "customer_id" => $parent_id["parent_id"],
                                "amount" => $money,
                                "tr_type" => "Receipt",
                                "description" => "بازگشت $money $cur بابت رد شدن سفارش $product توسط $customer_n",
                            ]
                        );
                    }
                    $db->insert(
                        "transactions",
                        [
                            "customer_id" => $customer_id,
                            "amount" => $money,
                            "tr_type" => "Receipt",
                            "description" => "بازگشت $money $cur بابت رد شدن سفارش $product",
                        ]
                    );
                    break;
                case 2:
                    $price = mysqli_fetch_assoc($db->query("SELECT product.dollar_sale_price as price, CONCAT(product.amount,' ',units.name) as product
                    FROM product LEFT JOIN units ON product.unit_id = units.id
                    WHERE product.id =" . $db->clean_input($_POST["product_id"])));
                    $money = $price["price"];
                    $product = $price["product"];
                    $db->query("UPDATE balance SET balance = balance + $money WHERE customer_id =$customer_id");
                    if ($parent_id["parent_id"] > 0) {
                        $customer_n = $currency["cs_name"];
                        $db->query("UPDATE balance SET balance = balance + $money WHERE customer_id =" . $parent_id["parent_id"]);
                        $db->insert(
                            "transactions",
                            [
                                "customer_id" => $parent_id["parent_id"],
                                "amount" => $money,
                                "tr_type" => "Receipt",
                                "description" => "بازگشت $money $cur بابت رد شدن سفارش $product توسط $customer_n",
                            ]
                        );
                    }
                    $db->insert(
                        "transactions",
                        [
                            "customer_id" => $customer_id,
                            "amount" => $money,
                            "tr_type" => "Receipt",
                            "description" => "بازگشت $money $cur بابت رد شدن سفارش $product",
                        ]
                    );

                    break;
                case 3:
                   $price = mysqli_fetch_assoc($db->query("SELECT product.lyra_sale_price as price, CONCAT(product.amount,' ',units.name) as product
                    FROM product LEFT JOIN units ON product.unit_id = units.id
                    WHERE product.id =" . $db->clean_input($_POST["product_id"])));
                    $money = $price["price"];
                    $product = $price["product"];
                    $db->query("UPDATE balance SET balance = balance + $money WHERE customer_id =$customer_id");
                    if ($parent_id["parent_id"] > 0) {
                        $customer_n = $currency["cs_name"];
                        $db->query("UPDATE balance SET balance = balance + $money WHERE customer_id =" . $parent_id["parent_id"]);
                        $db->insert(
                            "transactions",
                            [
                                "customer_id" => $parent_id["parent_id"],
                                "amount" => $money,
                                "tr_type" => "Receipt",
                                "description" => "بازگشت $money $cur بابت رد شدن سفارش $product توسط $customer_n",
                            ]
                        );
                    }
                    $db->insert(
                        "transactions",
                        [
                            "customer_id" => $customer_id,
                            "amount" => $money,
                            "tr_type" => "Receipt",
                            "description" => "بازگشت $money $cur بابت رد شدن سفارش $product",
                        ]
                    );
                    break;
                case 4:
                    $price = mysqli_fetch_assoc($db->query("SELECT product.euro_sale_price as price, CONCAT(product.amount,' ',units.name) as product
                    FROM product LEFT JOIN units ON product.unit_id = units.id
                    WHERE product.id =" . $db->clean_input($_POST["product_id"])));
                    $money = $price["price"];
                    $product = $price["product"];
                    $db->query("UPDATE balance SET balance = balance + $money WHERE customer_id =$customer_id");
                    if ($parent_id["parent_id"] > 0) {
                        $customer_n = $currency["cs_name"];
                        $db->query("UPDATE balance SET balance = balance + $money WHERE customer_id =" . $parent_id["parent_id"]);
                        $db->insert(
                            "transactions",
                            [
                                "customer_id" => $parent_id["parent_id"],
                                "amount" => $money,
                                "tr_type" => "Receipt",
                                "description" => "بازگشت $money $cur بابت رد شدن سفارش $product توسط $customer_n",
                            ]
                        );
                    }
                    $db->insert(
                        "transactions",
                        [
                            "customer_id" => $customer_id,
                            "amount" => $money,
                            "tr_type" => "Receipt",
                            "description" => "بازگشت $money $cur بابت رد شدن سفارش $product",
                        ]
                    );
                    break;
            }
        }
        $db->route("orders?opr=success");
    } else {
        $db->show_err();
    }
}


if(isset($_POST["delete_orders"])){
    $fromDate = $_POST["fromDate"];
     $toDate = $_POST["toDate"];
    $dSql = $db->query("DELETE FROM orders WHERE DATE(created) NOT BETWEEN '$fromDate' AND '$toDate'");
    if($dSql){
        $db->route("orders?opr=success");
    }else{
        $db->show_err();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>سفارشات</title>
    <style>
    .table tr td:last-child {
        padding: 0 !important;
        text-align: center;
    }
    </style>
</head>

<body>

    <?php
    if ($_SESSION["user_type"] == "admin") {
        require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li><span
                    class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item">سفارشات</li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <?php } else {
        if (!(isset($_SESSION["username"]) && isset($_SESSION["password"]))) {
            $db->route("index?login=false");
        }
        
        ?>
        <div class="container-fluid"><?php } ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>همه سفارشات</h2>
                    <?php if ($_SESSION["user_type"] !== "admin") { ?>
                    <a href="logout?logout" onclick="return confirm('آیا میخواهید خارج شوید؟')"
                        class="btn btn-danger bt-ico">خروج <span class="ico">logout</span></a><?php } ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover order-table">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>محصول</th>
                                    <th>مشتری</th>
                                    <th>خرید</th>
                                    <th>فروش</th>
                                    <th>مفاد</th>
                                    <th>آدرس / شماره</th>
                                    <th>وضعیت</th>
                                    <th>سرور</th>
                                    <th style="width: 15%;">تاریخ</th>
                                    <th style="width: 8%;">عملکرد</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <?php if($_SESSION["user_type"] == "admin"){ ?>
                    <hr>
                    <div class="card">
                        <div class="card-header">
                           <h4>حذف سفارشات بجز بین این دو تاریخ</h4>
                        </div>
                        <form method="post" class="card-body needs-validation" novalidate>
                            <p class="text-danger">محدوده ی را انتخاب کنید که نمیخواهید سفارشات آن حذف شود!</p>
                            <div class="row align-items-end pb-0 mb-0">
                                <div class="col-md-3 pb-0 mb-0">
                                    <div class="form-group pb-0 mb-0">
                                        <label for="fromDate" class="form-label">از تاریخ:</label>
                                        <input type="date" class="form-control" name="fromDate" id="fromDate" required>
                                    </div>
                                </div>
                                <div class="col-md-3 pb-0 mb-0">
                                    <div class="form-group pb-0 mb-0">
                                        <label for="toDate" class="form-label">تا تاریخ:</label>
                                        <input type="date" class="form-control" name="toDate" id="toDate" required>
                                    </div>
                                </div>
                                <div class="col-md-2 pb-0 mb-0">
                                    <button class="btn btn-danger deleteOrders" type="submit" name="delete_orders">حذف
                                        کردن</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>


        <!-- edit modal -->
        <div id="edit-modal" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <form method="POST" class="modal-content needs-validation" novalidate>
                    <div class="modal-header">
                        <h2>ویرایش سفارش </h2>
                        <button class="btn btn-danger" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="order_id" name="order_id">
                        <input type="hidden" id="customer_id" name="customer_id">
                         <input type="hidden" id="product_id" name="product_id">
                        <div class="form-group">
                            <label for="status">وضعیت:</label>
                            <select id="status" name="status" class="form-control">
                                <option selected disabled>انتخاب</option>
                                <option value="Success">اجرا شد</option>
                                <option value="Pending">در انتظار</option>
                                <option value="Rejected">رد شد</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="detail">توضیحات:</label>
                            <input type="text" id="detail" name="detail" class="form-control" value="">
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
        
        <script src="assets/js/order_scripts.js" type="text/javascript"></script>
     
</body>

</html>