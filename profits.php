<?php require_once "includes/conn.php";

if (isset($_POST["search"])) {
    $fromDate = $db->clean_input($_POST["fromDate"]);
    $toDate = $db->clean_input($_POST["toDate"]);

    $total_benefit = mysqli_fetch_assoc($db->query("SELECT 
(SUM(product.toman_sale_price) - SUM(product.toman_buy_price)) as benefit_toman,
(SUM(product.dollar_sale_price) - SUM(product.dollar_buy_price)) as benefit_dollar,
(SUM(product.lyra_sale_price) - SUM(product.lyra_buy_price)) as benefit_lyra,
(SUM(product.euro_sale_price) - SUM(product.euro_buy_price)) as benefit_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND DATE(orders.updated) BETWEEN '$fromDate' AND '$toDate'"));
} else {
    $total_benefit = mysqli_fetch_assoc($db->query("SELECT 
(SUM(product.toman_sale_price) - SUM(product.toman_buy_price)) as benefit_toman,
(SUM(product.dollar_sale_price) - SUM(product.dollar_buy_price)) as benefit_dollar,
(SUM(product.lyra_sale_price) - SUM(product.lyra_buy_price)) as benefit_lyra,
(SUM(product.euro_sale_price) - SUM(product.euro_buy_price)) as benefit_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success'"));
}

$daily_total_benefit = mysqli_fetch_assoc($db->query("SELECT 
(SUM(product.toman_sale_price) - SUM(product.toman_buy_price)) as benefit_toman,
(SUM(product.dollar_sale_price) - SUM(product.dollar_buy_price)) as benefit_dollar,
(SUM(product.lyra_sale_price) - SUM(product.lyra_buy_price)) as benefit_lyra,
(SUM(product.euro_sale_price) - SUM(product.euro_buy_price)) as benefit_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND DAY(orders.updated) = DAY(NOW()) AND MONTH(orders.updated) = MONTH(NOW()) AND YEAR(orders.updated) = YEAR(NOW())"));

$weekly_total_benefit = mysqli_fetch_assoc($db->query("SELECT 
(SUM(product.toman_sale_price) - SUM(product.toman_buy_price)) as benefit_toman,
(SUM(product.dollar_sale_price) - SUM(product.dollar_buy_price)) as benefit_dollar,
(SUM(product.lyra_sale_price) - SUM(product.lyra_buy_price)) as benefit_lyra,
(SUM(product.euro_sale_price) - SUM(product.euro_buy_price)) as benefit_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND WEEK(orders.updated) = WEEK(NOW()) AND MONTH(orders.updated) = MONTH(NOW()) AND YEAR(orders.updated) = YEAR(NOW())"));

$monthly_total_benefit = mysqli_fetch_assoc($db->query("SELECT 
(SUM(product.toman_sale_price) - SUM(product.toman_buy_price)) as benefit_toman,
(SUM(product.dollar_sale_price) - SUM(product.dollar_buy_price)) as benefit_dollar,
(SUM(product.lyra_sale_price) - SUM(product.lyra_buy_price)) as benefit_lyra,
(SUM(product.euro_sale_price) - SUM(product.euro_buy_price)) as benefit_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND MONTH(orders.updated) = MONTH(NOW()) AND YEAR(orders.updated) = YEAR(NOW())"));

$yearly_total_benefit = mysqli_fetch_assoc($db->query("SELECT 
(SUM(product.toman_sale_price) - SUM(product.toman_buy_price)) as benefit_toman,
(SUM(product.dollar_sale_price) - SUM(product.dollar_buy_price)) as benefit_dollar,
(SUM(product.lyra_sale_price) - SUM(product.lyra_buy_price)) as benefit_lyra,
(SUM(product.euro_sale_price) - SUM(product.euro_buy_price)) as benefit_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND YEAR(orders.updated) = YEAR(NOW())"));

if (isset($_POST["get_profit"])) {
    $total_benefit = [
        "benefit_toman" => 0,
        "benefit_dollar" => 0,
        "benefit_lyra" => 0,
        "benefit_euro" => 0
    ];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>سودها</title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h2>سودها</h2>
            </div>

            <div class="card-body">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr class="bg-success text-light">
                            <th colspan="4" class="h4 py-2">مجموع سودها</th>
                        </tr>
                        <tr>
                            <th>سود به تومان</th>
                            <th>سود به دلار</th>
                            <th>سود به لیر</th>
                            <th>سود به یورو</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="h4 py-2">
                                <?= number_format($total_benefit["benefit_toman"] ?? 0) ?>
                            </td>
                            <td class="h4 py-2">
                                <?= $total_benefit["benefit_dollar"] ?>
                            </td>
                            <td class="h4 py-2">
                                <?= $total_benefit["benefit_lyra"] ?>
                            </td>
                            <td class="h4 py-2">
                                <?= $total_benefit["benefit_euro"] ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <hr>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr class="bg-primary text-light">
                            <th colspan="4" class="h4 py-2">سودهای امروز</th>
                        </tr>
                        <tr>
                            <th>سود به تومان</th>
                            <th>سود به دلار</th>
                            <th>سود به لیر</th>
                            <th>سود به یورو</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="table-primary h5 py-2">
                                <?= number_format($daily_total_benefit["benefit_toman"] ?? 0) ?>
                            </td>
                            <td class="table-primary h5 py-2">
                                <?= $daily_total_benefit["benefit_dollar"] ?>
                            </td>
                            <td class="table-primary h5 py-2">
                                <?= $daily_total_benefit["benefit_lyra"] ?>
                            </td>
                            <td class="table-primary h5 py-2">
                                <?= $daily_total_benefit["benefit_euro"] ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr class="bg-info text-light">
                            <th colspan="4" class="h4 py-2">سودهای این هفته</th>
                        </tr>
                        <tr>
                            <th>سود به تومان</th>
                            <th>سود به دلار</th>
                            <th>سود به لیر</th>
                            <th>سود به یورو</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="table-info h5 py-2">
                                <?= number_format($weekly_total_benefit["benefit_toman"] ?? 0) ?>
                            </td>
                            <td class="table-info h5 py-2">
                                <?= $weekly_total_benefit["benefit_dollar"] ?>
                            </td>
                            <td class="table-info h5 py-2">
                                <?= $weekly_total_benefit["benefit_lyra"] ?>
                            <td class="table-info h5 py-2">
                                <?= $weekly_total_benefit["benefit_euro"] ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr class="bg-success text-light">
                            <th colspan="4" class="h4 py-2">سودهای این ماه</th>
                        </tr>
                        <tr>
                            <th>سود به تومان</th>
                            <th>سود به دلار</th>
                            <th>سود به لیر</th>
                            <th>سود به یورو</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="table-success h5 py-2">
                                <?= number_format($monthly_total_benefit["benefit_toman"] ?? 0) ?>
                            </td>
                            <td class="table-success h5 py-2">
                                <?= $monthly_total_benefit["benefit_dollar"] ?>
                            </td>
                            <td class="table-success h5 py-2">
                                <?= $monthly_total_benefit["benefit_lyra"] ?>
                            </td>
                            <td class="table-success h5 py-2">
                                <?= $monthly_total_benefit["benefit_euro"] ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered text-center">
                    <thead>
                        <tr class="bg-warning text-dark">
                            <th colspan="4" class="h4 py-2">سودهای امسال</th>
                        </tr>
                        <tr>
                            <th>سود به تومان</th>
                            <th>سود به دلار</th>
                            <th>سود به لیر</th>
                            <th>سود به یورو</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="table-warning h5 py-2">
                                <?= number_format($yearly_total_benefit["benefit_toman"] ?? 0) ?>
                            </td>
                            <td class="table-warning h5 py-2">
                                <?= $yearly_total_benefit["benefit_dollar"] ?>
                            </td>
                            <td class="table-warning h5 py-2">
                                <?= $yearly_total_benefit["benefit_lyra"] ?>
                            </td>
                            <td class="table-warning h5 py-2">
                                <?= $yearly_total_benefit["benefit_euro"] ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <hr>
                <div class="text-center">
                    <form method="post">
                        <button type="submit" name="get_profit" class="btn btn-danger h4 py-2">دریافت سود
                            کل</button>
                    </form>
                </div>
            </div>


        </div>
    </div>
    <?php require_once "includes/footer.php" ?>
</body>

</html>