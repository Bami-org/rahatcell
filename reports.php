<?php require_once "includes/conn.php";

if (isset($_POST["search"])) {
    $fromDate = $db->clean_input($_POST["fromDate"]);
    $toDate = $db->clean_input($_POST["toDate"]);
    $orders_sql = $db->query("SELECT
SUM(product.dollar_sale_price) as dollar,
  SUM(product.toman_sale_price) as toman,
    SUM(product.lyra_sale_price) as lyra,
    SUM(product.euro_sale_price) as euro,
    orders.updated as `date`,
sub_category.name AS category,
CONCAT(SUM(product.amount),' ',units.name) as product
FROM
product
LEFT JOIN orders ON orders.product_id = product.id
LEFT JOIN sub_category ON product.sub_category_id = sub_category.id
LEFT JOIN units ON product.unit_id = units.id
WHERE
orders.status = 'Success' AND DATE(orders.updated) BETWEEN '$fromDate' AND '$toDate'
GROUP BY product.id");

    // ===========================
    $total_sales = mysqli_fetch_assoc($db->query("SELECT 
SUM(product.toman_sale_price) as total_toman,
SUM(product.dollar_sale_price) as total_dollar,
SUM(product.lyra_sale_price) as total_lyra,
SUM(product.euro_sale_price) as total_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND DATE(orders.updated) BETWEEN '$fromDate' AND '$toDate'"));

    $total_benefit = mysqli_fetch_assoc($db->query("SELECT 
(SUM(product.toman_sale_price) - SUM(product.toman_buy_price)) as benefit_toman,
(SUM(product.dollar_sale_price) - SUM(product.dollar_buy_price)) as benefit_dollar,
(SUM(product.lyra_sale_price) - SUM(product.lyra_buy_price)) as benefit_lyra,
(SUM(product.euro_sale_price) - SUM(product.euro_buy_price)) as benefit_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND DATE(orders.updated) BETWEEN '$fromDate' AND '$toDate'"));
} else {
    $orders_sql = $db->query("SELECT
    SUM(product.dollar_sale_price) as dollar,
      SUM(product.toman_sale_price) as toman,
        SUM(product.lyra_sale_price) as lyra,
        SUM(product.euro_sale_price) as euro,
        orders.updated as `date`,
    sub_category.name AS category,
    CONCAT(SUM(product.amount),' ',units.name) as product
    FROM
    product
    LEFT JOIN orders ON orders.product_id = product.id
    LEFT JOIN sub_category ON product.sub_category_id = sub_category.id
    LEFT JOIN units ON product.unit_id = units.id
    WHERE
    orders.status = 'Success'
    GROUP BY product.id");

    // ==================================

    $total_sales = mysqli_fetch_assoc($db->query("SELECT 
SUM(product.toman_sale_price) as total_toman,
SUM(product.dollar_sale_price) as total_dollar,
SUM(product.lyra_sale_price) as total_lyra,
SUM(product.euro_sale_price) as total_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success'"));

    $total_benefit = mysqli_fetch_assoc($db->query("SELECT 
(SUM(product.toman_sale_price) - SUM(product.toman_buy_price)) as benefit_toman,
(SUM(product.dollar_sale_price) - SUM(product.dollar_buy_price)) as benefit_dollar,
(SUM(product.lyra_sale_price) - SUM(product.lyra_buy_price)) as benefit_lyra,
(SUM(product.euro_sale_price) - SUM(product.euro_buy_price)) as benefit_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success'"));
}

if ($orders_sql->num_rows > 0) {
    $orders_row = $orders_sql->fetch_assoc();
}



$daily_total_sales = mysqli_fetch_assoc($db->query("SELECT 
SUM(product.toman_sale_price) as total_toman,
SUM(product.dollar_sale_price) as total_dollar,
SUM(product.lyra_sale_price) as total_lyra,
SUM(product.euro_sale_price) as total_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND DAY(orders.updated) = DAY(NOW()) AND MONTH(orders.updated) = MONTH(NOW()) AND YEAR(orders.updated) = YEAR(NOW())"));

$weekly_total_sales = mysqli_fetch_assoc($db->query("SELECT 
SUM(product.toman_sale_price) as total_toman,
SUM(product.dollar_sale_price) as total_dollar,
SUM(product.lyra_sale_price) as total_lyra,
SUM(product.euro_sale_price) as total_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND WEEK(orders.updated) = WEEK(NOW()) AND MONTH(orders.updated) = MONTH(NOW()) AND YEAR(orders.updated) = YEAR(NOW())"));


$monthly_total_sales = mysqli_fetch_assoc($db->query("SELECT 
SUM(product.toman_sale_price) as total_toman,
SUM(product.dollar_sale_price) as total_dollar,
SUM(product.lyra_sale_price) as total_lyra,
SUM(product.euro_sale_price) as total_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND MONTH(orders.updated) = MONTH(NOW()) AND YEAR(orders.updated) = YEAR(NOW())"));

$yearly_total_sales = mysqli_fetch_assoc($db->query("SELECT 
SUM(product.toman_sale_price) as total_toman,
SUM(product.dollar_sale_price) as total_dollar,
SUM(product.lyra_sale_price) as total_lyra,
SUM(product.euro_sale_price) as total_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND YEAR(orders.updated) = YEAR(NOW())"));


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>گزارشات</title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h2>گزارشات</h2>
            </div>
            <div class="card-body">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3>گزارشات فروشات</h3>
                        <form class="form-inline needs-validation" method="post" novalidate>
                            <label for="fromDate">از تاریخ:</label>
                            <input type="date" class="form-control mx-2"
                                value="<?= isset($_POST["fromDate"])? $_POST["fromDate"] : date("Y-m-d") ?>"
                                min="2000-01-01" name="fromDate" id="fromDate" required>
                            <label for="toDate">تا تاریخ:</label>
                            <input type="date" class="form-control mx-2"
                                value="<?= isset($_POST["toDate"])? $_POST["toDate"]: date("Y-m-d") ?>" max="2050-12-31"
                                name="toDate" id="toDate" required>
                            <button type="submit" name="search" class="btn btn-primary bt-ico">جستجو <span
                                    class="ico">search</span></button>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>محصول</th>
                                    <th>دسته بندی</th>
                                    <th>پول تومن</th>
                                    <th>پول دالر</th>
                                    <th>پول لیر</th>
                                    <th>پول یورو</th>
                                    <th>تاریخ فروش</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $n = 1;
                                if ($orders_sql->num_rows > 0) {
                                    do { ?>
                                <tr>
                                    <td><?= $n++ ?></td>
                                    <td><?= $orders_row["product"] ?></td>
                                    <td><?= $orders_row["category"] ?></td>
                                    <td><?= number_format($orders_row["toman"] ?? 0) ?></td>
                                    <td><?= $orders_row["dollar"] ?></td>
                                    <td><?= $orders_row["lyra"] ?></td>
                                    <td><?= $orders_row["euro"] ?></td>
                                    <td><?= $db->convertFullDate($orders_row["date"],$setting["date_type"]) ?></td>
                                </tr>
                                <?php } while ($orders_row = $orders_sql->fetch_assoc());
                                } ?>
                            </tbody>
                        </table>
                        </div>
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr class="bg-success text-light">
                                    <th colspan="4" class="h4 py-2">مجموعه عایدات</th>
                                </tr>
                                <tr>
                                    <th>مجموعه تومن</th>
                                    <th>مجموعه دالر</th>
                                    <th>مجموعه لیر</th>
                                    <th>مجموعه یورو</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="h4 py-2">
                                        <?= number_format($total_benefit["benefit_toman"] ?? 0) ?>
                                    </td>
                                    <td class="h4 py-2">
                                        <?= $total_benefit["benefit_dollar"] ?></td>
                                    <td class="h4 py-2">
                                        <?= $total_benefit["benefit_lyra"] ?></td>
                                    <td class="h4 py-2">
                                        <?= $total_benefit["benefit_euro"] ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr class="bg-secondary text-light">
                                    <th colspan="4" class="h4 py-2">مجموعه فروشات</th>
                                </tr>
                                <tr>
                                    <th class="bg-info text-white">مجموعه تومن</th>
                                    <th class="bg-primary text-white">مجموعه دالر</th>
                                    <th class="bg-warning text-white">مجموعه لیر</th>
                                    <th class="bg-danger text-white">مجموعه یورو</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="table-info h5 py-2">
                                        <?= number_format($total_sales["total_toman"] ?? 0) ?>
                                    </td>
                                    <td class="table-primary h5 py-2">
                                        <?= $total_sales["total_dollar"] ?></td>
                                    <td class="table-warning h5 py-2">
                                        <?= $total_sales["total_lyra"] ?></td>
                                    <td class="table-danger h5 py-2">
                                        <?= $total_sales["total_euro"] ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <hr>
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr class="bg-primary text-light">
                                    <th colspan="4" class="h4 py-2">مجموعه فروشات امروز</th>
                                </tr>
                                <tr>
                                    <th class="bg-primary text-white">مجموعه تومن</th>
                                    <th class="bg-primary text-white">مجموعه دالر</th>
                                    <th class="bg-primary text-white">مجموعه لیر</th>
                                    <th class="bg-primary text-white">مجموعه یورو</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="table-primary h5 py-2">
                                        <?= number_format($daily_total_sales["total_toman"] ?? 0) ?>
                                    </td>
                                    <td class="table-primary h5 py-2">
                                        <?= $daily_total_sales["total_dollar"] ?></td>
                                    <td class="table-primary h5 py-2">
                                        <?= $daily_total_sales["total_lyra"] ?></td>
                                    <td class="table-primary h5 py-2">
                                        <?= $daily_total_sales["total_euro"] ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr class="bg-info text-light">
                                    <th colspan="4" class="h4 py-2">مجموعه فروشات این هفته</th>
                                </tr>
                                <tr>
                                    <th class="bg-info text-white">مجموعه تومن</th>
                                    <th class="bg-info text-white">مجموعه دالر</th>
                                    <th class="bg-info text-white">مجموعه لیر</th>
                                    <th class="bg-info text-white">مجموعه یورو</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="table-info h5 py-2">
                                        <?= number_format($weekly_total_sales["total_toman"] ?? 0) ?>
                                    </td>
                                    <td class="table-info h5 py-2">
                                        <?=$weekly_total_sales["total_dollar"] ?></td>
                                    <td class="table-info h5 py-2">
                                        <?= $weekly_total_sales["total_lyra"]?>
                                    <td class="table-info h5 py-2">
                                        <?= $weekly_total_sales["total_euro"] ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr class="bg-success text-light">
                                    <th colspan="4" class="h4 py-2">مجموعه فروشات این ماه</th>
                                </tr>
                                <tr>
                                    <th class="bg-success text-white">مجموعه تومن</th>
                                    <th class="bg-success text-white">مجموعه دالر</th>
                                    <th class="bg-success text-white">مجموعه لیر</th>
                                    <th class="bg-success text-white">مجموعه یورو</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="table-success h5 py-2">
                                        <?= number_format($monthly_total_sales["total_toman"] ?? 0) ?>
                                    </td>
                                    <td class="table-success h5 py-2">
                                        <?= $monthly_total_sales["total_dollar"] ?></td>
                                    <td class="table-success h5 py-2">
                                        <?= $monthly_total_sales["total_lyra"] ?></td>
                                    <td class="table-success h5 py-2">
                                        <?= $monthly_total_sales["total_euro"] ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr class="bg-warning text-dark">
                                    <th colspan="4" class="h4 py-2">مجموعه فروشات امسال </th>
                                </tr>
                                <tr>
                                    <th class="bg-warning text-dark">مجموعه تومن</th>
                                    <th class="bg-warning text-dark">مجموعه دالر</th>
                                    <th class="bg-warning text-dark">مجموعه لیر</th>
                                    <th class="bg-warning text-dark">مجموعه یورو</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="table-warning h5 py-2">
                                        <?= number_format($yearly_total_sales["total_toman"] ?? 0) ?>
                                    </td>
                                    <td class="table-warning h5 py-2">
                                        <?= $yearly_total_sales["total_dollar"]  ?></td>
                                    <td class="table-warning h5 py-2">
                                        <?= $yearly_total_sales["total_lyra"] ?></td>
                                    <td class="table-warning h5 py-2">
                                        <?= $yearly_total_sales["total_euro"] ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once "includes/footer.php" ?>
</body>

</html>