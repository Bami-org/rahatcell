<?php
require_once "includes/conn.php";
$orders = mysqli_fetch_assoc($db->query("SELECT COUNT(*) as total FROM orders"));
$success_orders = mysqli_fetch_assoc($db->query("SELECT COUNT(*) as total FROM orders WHERE `status`='Success'"));
$pending_orders = mysqli_fetch_assoc($db->query("SELECT COUNT(*) as total FROM orders WHERE `status`='Pending'"));
$rejected_orders = mysqli_fetch_assoc($db->query("SELECT COUNT(*) as total FROM orders WHERE `status`='Rejected'"));

$today_orders = mysqli_fetch_assoc($db->query("SELECT COUNT(*) as total FROM orders WHERE YEAR(created) = YEAR(NOW()) AND MONTH(created) = MONTH(NOW()) AND DAY(created) = DAY(NOW())"));
$today_success_orders = mysqli_fetch_assoc($db->query("SELECT COUNT(*) as total FROM orders WHERE `status`='Success' AND YEAR(updated) = YEAR(NOW()) AND MONTH(created) = MONTH(NOW()) AND DAY(created) = DAY(NOW())"));
$today_pending_orders = mysqli_fetch_assoc($db->query("SELECT COUNT(*) as total FROM orders WHERE `status`='Pending' AND YEAR(created) = YEAR(NOW()) AND MONTH(created) = MONTH(NOW()) AND DAY(created) = DAY(NOW())"));
$today_rejected_orders = mysqli_fetch_assoc($db->query("SELECT COUNT(*) as total FROM orders WHERE `status`='Rejected' AND YEAR(updated) = YEAR(NOW()) AND MONTH(created) = MONTH(NOW()) AND DAY(created) = DAY(NOW())"));

$customer_count = mysqli_fetch_assoc($db->query("SELECT COUNT(*) as total FROM customer"));

$all_sales = mysqli_fetch_assoc($db->query("SELECT 
SUM(product.toman_sale_price) as toman,
SUM(product.dollar_sale_price) as dollar,
SUM(product.lyra_sale_price) as lyra,
SUM(product.euro_sale_price) as euro
FROM orders 
LEFT JOIN product ON orders.product_id = product.id
LEFT JOIN customer ON orders.customer_id = customer.id
LEFT JOIN currency ON customer.currency_id = currency.id
WHERE orders.status = 'Success'
GROUP BY currency.id
"));


$total_balance_sql = $db->query("SELECT 
SUM(balance.balance) as amount,
currency.name as currency
FROM balance 
LEFT JOIN customer ON balance.customer_id = customer.id
LEFT JOIN currency ON customer.currency_id = currency.id
WHERE customer.parent_id =0
GROUP BY customer.currency_id");

$day = date('d');
$month = date('m');
$today_balance_added_sql = $db->query("SELECT 
SUM(balance.balance) as amount,
currency.name as currency
FROM balance 
LEFT JOIN customer ON balance.customer_id = customer.id
LEFT JOIN currency ON customer.currency_id = currency.id
WHERE (DAY(balance.updated) = '$day' AND MONTH(balance.updated) = '$month') AND customer.parent_id =0
GROUP BY customer.currency_id");


// payments

$pay_sql = $db->query("SELECT SUM(pay_amount) as total,bank.name as bank 
FROM payment
LEFT JOIN bank ON payment.bank_id = bank.id
GROUP BY bank.name
");

$aPass = "adminFullAccessRahatCell";
$canSee = md5($aPass) === md5($_SESSION["password"]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>
        خدمات آنلاین راحت سیل
    </title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <h4>سفارشات</h4>
        <hr class="mt-0 mb-2">
        <div class="row">
            <div class="col-lg-4 col-xl-3 col-sm-6">
                <div class="card shadow-sm">
                    <div class="card-body bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3>همه سفارشات</h3>
                                <h2>
                                    <?= $orders["total"] ?>
                                </h2>
                            </div>
                            <span class="ico display-4">workspaces</span>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between py-2">
                        <span>امروز: <?= $today_orders["total"] ?></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-xl-3 col-sm-6 mt-2 mt-md-0">
                <div class="card shadow-sm">
                    <div class="card-body bg-success text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3>اجرا شده</h3>
                                <h2>
                                    <?= $success_orders["total"] ?>
                                </h2>
                            </div>
                            <span class="ico display-4">check_circle</span>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between py-2">
                        <span>امروز: <?= $today_success_orders["total"] ?></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-xl-3 col-sm-6 mt-2 mt-xl-0">
                <div class="card shadow-sm">
                    <div class="card-body bg-warning text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3>در انتظار</h3>
                                <h2>
                                    <?= $pending_orders["total"] ?>
                                </h2>
                            </div>
                            <span class="ico display-4">schedule</span>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between py-2">
                        <span>امروز: <?= $today_pending_orders["total"] ?></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-xl-3 col-sm-6 mt-2 mt-lg-0">
                <div class="card shadow-sm">
                    <div class="card-body bg-danger text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3>رد شده</h3>
                                <h2>
                                    <?= $rejected_orders["total"] ?>
                                </h2>
                            </div>
                            <span class="ico display-4">highlight_off</span>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between py-2">
                        <span>امروز: <?= $today_rejected_orders["total"] ?></span>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-lg-4 col-xl-3 col-sm-6 mt-lg-0">
                <div class="card shadow-sm">
                    <div class="card-body bg-info text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3>همه مشتریان</h3>
                                <h2>
                                    <?= $customer_count["total"] ?>
                                </h2>
                            </div>
                            <span class="ico display-4">group</span>
                        </div>
                    </div>
                    <a href="customer" class="card-footer d-flex align-items-center justify-content-between py-2">
                        <span>دیدن همه</span>
                        <span class="ico">arrow_back</span>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-xl-3 col-sm-6 my-2 my-lg-0">
                <div class="card shadow-sm">
                    <div class="card-body bg-primary text-white pt-2 pb-0">
                        <div>
                            <h4 class="mt-0 mb-1">همه فروشات</h4>
                            <hr class="m-0 mb-2">
                            <h5>تومن: <?= $all_sales["toman"] ?></h5>
                            <h5>دالر: <?= $all_sales["dollar"] ?></h5>
                            <h5>لیر: <?= $all_sales["lyra"] ?></h5>
                            <h5>یورو: <?= $all_sales["euro"] ?></h5>
                        </div>
                    </div>
                </div>
            </div>
            <?php if($canSee){ ?>
            <div class="col-lg-4 col-xl-3 col-sm-6 my-2 my-lg-0">
                <div class="card shadow-sm">
                    <div class="card-body bg-success text-white pt-2 pb-0">
                        <div>
                            <h5 class="mt-0 mb-1">بیلانس مشتریان</h5>
                            <hr class="m-0 mb-2">
                            <?php
                            if ($total_balance_sql->num_rows > 0) {
                                $total_balance_row = mysqli_fetch_assoc($total_balance_sql);
                                do { ?>
                            <h5><?= $total_balance_row["currency"] ?>:
                                <span class="font-tz"><?= $total_balance_row["amount"] ?></span>
                            </h5>
                            <?php } while ($total_balance_row = mysqli_fetch_assoc($total_balance_sql));
                            } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="col-lg-4 col-xl-3 col-sm-6 mt-lg-0">
                <div class="card shadow-sm">
                    <div class="card-body bg-info text-white pt-2 pb-0">
                        <div>
                            <h5 class="mt-0 mb-1">بیلانس اضافه شده امروز</h5>
                            <hr class="m-0 mb-2">
                            <?php
                            if ($today_balance_added_sql->num_rows > 0) {
                                $today_balance_row = mysqli_fetch_assoc($today_balance_added_sql);
                                do { ?>
                            <h5><?= $today_balance_row["currency"] ?>:
                                <span class="font-tz"><?= $today_balance_row["amount"] ?></span>
                            </h5>
                            <?php } while ($today_balance_row = mysqli_fetch_assoc($today_balance_added_sql));
                            } ?>
                        </div>
                    </div>
                    <a href="balance?today" class="card-footer d-flex align-items-center justify-content-between py-2">
                        <span>دیدن همه</span>
                        <span class="ico">arrow_back</span>
                    </a>
                </div>
            </div>
        </div>
        <hr>
         <div class="col-lg-4 col-xl-3 col-sm-6 mt-lg-0">
                <div class="card shadow-sm">
                    <div class="card-body bg-info text-white pt-2 pb-0">
                        <div>
                            <h5 class="mt-0 mb-1"> پرداخت ها</h5>
                            <hr class="m-0 mb-2">
                            <?php
                            if ($pay_sql->num_rows > 0) {
                                $pay_row = mysqli_fetch_assoc($pay_sql);
                                do { ?>
                            <h5><?= $pay_row["bank"] ?>:
                                <span class="font-tz"><?= $pay_row["total"] ?></span>
                            </h5>
                            <?php } while ($pay_row = mysqli_fetch_assoc($pay_sql));
                            } ?>
                        </div>
                    </div>
                    <a href="payment" class="card-footer d-flex align-items-center justify-content-between py-2">
                        <span>دیدن همه</span>
                        <span class="ico">arrow_back</span>
                    </a>
                </div>
            </div>
    </div>

    <?php require_once "includes/footer.php" ?>

</body>

</html>