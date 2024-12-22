<?php require_once "includes/conn.php";

// Fetch the last profit date
$last_profit_date_result = $db->query("SELECT MAX(profit_date) as last_profit_date FROM profits WHERE profit_type = 'total'");
$last_profit_date_row = mysqli_fetch_assoc($last_profit_date_result);
$last_profit_date = $last_profit_date_row['last_profit_date'] ?? '2000-01-01';

// Fetch bank list
$bank_sql = $db->query("SELECT * FROM bank ORDER BY name ASC");
$banks = $bank_sql->fetch_all(MYSQLI_ASSOC);

if (isset($_POST["search"])) {
    $fromDate = $db->clean_input($_POST["fromDate"]);
    $toDate = $db->clean_input($_POST["toDate"]);

    $total_benefit = mysqli_fetch_assoc($db->query("SELECT 
GREATEST(SUM(product.toman_sale_price) - SUM(product.toman_buy_price), 0) as benefit_toman,
GREATEST(SUM(product.dollar_sale_price) - SUM(product.dollar_buy_price), 0) as benefit_dollar,
GREATEST(SUM(product.lyra_sale_price) - SUM(product.lyra_buy_price), 0) as benefit_lyra,
GREATEST(SUM(product.euro_sale_price) - SUM(product.euro_buy_price), 0) as benefit_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND DATE(orders.updated) BETWEEN '$fromDate' AND '$toDate'"));
} else {
    $total_benefit = mysqli_fetch_assoc($db->query("SELECT 
GREATEST(SUM(product.toman_sale_price) - SUM(product.toman_buy_price), 0) as benefit_toman,
GREATEST(SUM(product.dollar_sale_price) - SUM(product.dollar_buy_price), 0) as benefit_dollar,
GREATEST(SUM(product.lyra_sale_price) - SUM(product.lyra_buy_price), 0) as benefit_lyra,
GREATEST(SUM(product.euro_sale_price) - SUM(product.euro_buy_price), 0) as benefit_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND DATE(orders.updated) > '$last_profit_date'"));
}

$daily_total_benefit = mysqli_fetch_assoc($db->query("SELECT 
GREATEST(SUM(product.toman_sale_price) - SUM(product.toman_buy_price), 0) as benefit_toman,
GREATEST(SUM(product.dollar_sale_price) - SUM(product.dollar_buy_price), 0) as benefit_dollar,
GREATEST(SUM(product.lyra_sale_price) - SUM(product.lyra_buy_price), 0) as benefit_lyra,
GREATEST(SUM(product.euro_sale_price) - SUM(product.euro_buy_price), 0) as benefit_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND DAY(orders.updated) = DAY(NOW()) AND MONTH(orders.updated) = MONTH(NOW()) AND YEAR(orders.updated) = YEAR(NOW())"));

$weekly_total_benefit = mysqli_fetch_assoc($db->query("SELECT 
GREATEST(SUM(product.toman_sale_price) - SUM(product.toman_buy_price), 0) as benefit_toman,
GREATEST(SUM(product.dollar_sale_price) - SUM(product.dollar_buy_price), 0) as benefit_dollar,
GREATEST(SUM(product.lyra_sale_price) - SUM(product.lyra_buy_price), 0) as benefit_lyra,
GREATEST(SUM(product.euro_sale_price) - SUM(product.euro_buy_price), 0) as benefit_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND WEEK(orders.updated) = WEEK(NOW()) AND MONTH(orders.updated) = MONTH(NOW()) AND YEAR(orders.updated) = YEAR(NOW())"));

$monthly_total_benefit = mysqli_fetch_assoc($db->query("SELECT 
GREATEST(SUM(product.toman_sale_price) - SUM(product.toman_buy_price), 0) as benefit_toman,
GREATEST(SUM(product.dollar_sale_price) - SUM(product.dollar_buy_price), 0) as benefit_dollar,
GREATEST(SUM(product.lyra_sale_price) - SUM(product.lyra_buy_price), 0) as benefit_lyra,
GREATEST(SUM(product.euro_sale_price) - SUM(product.euro_buy_price), 0) as benefit_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND MONTH(orders.updated) = MONTH(NOW()) AND YEAR(orders.updated) = YEAR(NOW())"));

$yearly_total_benefit = mysqli_fetch_assoc($db->query("SELECT 
GREATEST(SUM(product.toman_sale_price) - SUM(product.toman_buy_price), 0) as benefit_toman,
GREATEST(SUM(product.dollar_sale_price) - SUM(product.dollar_buy_price), 0) as benefit_dollar,
GREATEST(SUM(product.lyra_sale_price) - SUM(product.lyra_buy_price), 0) as benefit_lyra,
GREATEST(SUM(product.euro_sale_price) - SUM(product.euro_buy_price), 0) as benefit_euro
FROM orders
LEFT JOIN product ON orders.product_id=product.id
WHERE orders.status='Success' AND DATE(orders.updated) > '$last_profit_date' AND YEAR(orders.updated) = YEAR(NOW())"));

$profit_message = "";
if (isset($_POST["get_profit"])) {
    $bank_id = $db->real_escape_string($_POST['bank_id']);
    if ($yearly_total_benefit["benefit_toman"] == 0 && $yearly_total_benefit["benefit_dollar"] == 0 && $yearly_total_benefit["benefit_lyra"] == 0 && $yearly_total_benefit["benefit_euro"] == 0) {
        $profit_message = "<div class='alert alert-danger text-center'>مفاد شما صفر است!</div>";
    } else {
        // Insert the profit data into the profits table
        $db->query("INSERT INTO profits (profit_toman, profit_dollar, profit_lyra, profit_euro, profit_date, profit_type, status) VALUES 
        ({$yearly_total_benefit['benefit_toman']}, {$yearly_total_benefit['benefit_dollar']}, {$yearly_total_benefit['benefit_lyra']}, {$yearly_total_benefit['benefit_euro']}, NOW(), 'total', 'completed')");

        // Update the admin_balance table
        $db->query("INSERT INTO admin_balance (balance, description, created, updated, bank_id, profit) VALUES 
        ({$yearly_total_benefit['benefit_toman']}, 'Yearly profit in Toman', NOW(), NOW(), '$bank_id', {$yearly_total_benefit['benefit_toman']}),
        ({$yearly_total_benefit['benefit_dollar']}, 'Yearly profit in Dollar', NOW(), NOW(), '$bank_id', {$yearly_total_benefit['benefit_dollar']}),
        ({$yearly_total_benefit['benefit_lyra']}, 'Yearly profit in Lyra', NOW(), NOW(), '$bank_id', {$yearly_total_benefit['benefit_lyra']}),
        ({$yearly_total_benefit['benefit_euro']}, 'Yearly profit in Euro', NOW(), NOW(), '$bank_id', {$yearly_total_benefit['benefit_euro']})");

        $profit_message = "<div class='alert alert-success text-center'>شما مفاد را دریافت نمودید!</div>";

        // Reset the total benefit to 0
        $yearly_total_benefit = [
            "benefit_toman" => 0,
            "benefit_dollar" => 0,
            "benefit_lyra" => 0,
            "benefit_euro" => 0
        ];
    }
}

// Fetch the history of checkouts
$profits_history = $db->query("SELECT * FROM profits ORDER BY profit_date DESC");

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
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3>سودها</h3>
                        <form class="form-inline needs-validation" method="post" novalidate>
                            <div class="form-group">
                                <label for="bank_id">بانک:</label>
                                <select class="form-control" name="bank_id" required>
                                    <option value="">انتخاب بانک</option>
                                    <?php foreach ($banks as $bank) { ?>
                                        <option value="<?= $bank['id']; ?>"><?= $bank['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <button type="submit" name="get_profit" class="btn btn-primary bt-ico">دریافت سود <span
                                    class="ico">search</span></button>
                        </form>
                    </div>
                    <div class="card-body">
                        <?= $profit_message ?>
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

                        <hr>
                        <h3 class="text-center">تاریخچه دریافت سودها</h3>
                        <div class="table-responsive">
                            <table class="table table-bordered text-center">
                                <thead>
                                    <tr class="bg-secondary text-light">
                                        <th>تاریخ</th>
                                        <th>سود به تومان</th>
                                        <th>سود به دلار</th>
                                        <th>سود به لیر</th>
                                        <th>سود به یورو</th>
                                        <th>نوع سود</th>
                                        <th>وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $profits_history->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?= $row["profit_date"] ?></td>
                                            <td><?= number_format($row["profit_toman"]) ?></td>
                                            <td><?= $row["profit_dollar"] ?></td>
                                            <td><?= $row["profit_lyra"] ?></td>
                                            <td><?= $row["profit_euro"] ?></td>
                                            <td><?= $row["profit_type"] ?></td>
                                            <td><?= $row["status"] ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once "includes/footer.php" ?>
</body>

</html>