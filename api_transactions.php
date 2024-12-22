<?php
require_once "includes/conn.php";

// Fetch loan history
$result = [];

if (isset($_GET['dealer_code'])) {
    $dealer_code = $_GET['dealer_code'];

    // Use prepared statements to avoid SQL Injection vulnerabilities
    $result = $db->query("SELECT * FROM api_transactions WHERE dealer_code = $dealer_code");
    if ($result && $result->num_rows > 0) {
        $result = $result->fetch_all(MYSQLI_ASSOC); // Fetch all rows as an associative array
    } else {
        $result = 'No data found'; // If no data found, set this message
    }
}


// Determine the total number of records
$total_records_query = "SELECT COUNT(*) AS total FROM api_transactions";
$total_records_result = $db->query($total_records_query);
$total_records_row = $total_records_result->fetch_assoc();
$total_records = $total_records_row['total'];

// Define the number of records per page
$records_per_page = 6;
$total_pages = ceil($total_records / $records_per_page);

// Get the current page number from the query string
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Fetch the records for the current page
$transactions_query = "SELECT * FROM api_transactions LIMIT $offset, $records_per_page";
$transactions_result = $db->query($transactions_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php"; ?>
    <title>API Management and Loan Operations</title>
</head>

<body>
    <?php require_once "menu.php"; ?>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header justify-content-between d-flex">
                <h2>مدریت کردن شرکت های API</h2>
                <a href="lone">
                    <button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" title="اضافه کردن">
                        <span class="ico h6"> بازگشت </span>
                    </button>
                </a>
            </div>
            <div class="card-body">
                <!-- Display API credentials -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>کد فروشنده</th>
                                <th>نوغ انتقال</th>
                                <th>مقدار وام</th>
                                <th>تاریخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result === 'No data found') {
                                echo '<tr><td colspan="5">No data found</td></tr>';
                            } else {
                                foreach ($result as $api) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($api['dealer_code']); ?></td>

                                        <td>
                                            <?php
                                            switch ($api['transaction_type']) {
                                                case 'add_money':
                                                    echo 'اضافه کردن پول';
                                                    break;
                                                case 'get_loan':
                                                    echo 'دریافت وام';
                                                    break;
                                                case 'repay_loan':
                                                    echo 'بازپرداخت وام';
                                                    break;
                                                default:
                                                    echo htmlspecialchars($api['transaction_type']);
                                            }
                                            ?>
                                        </td>
                                        </td>
                                        <td><?= htmlspecialchars($api['amount']); ?></td>
                                        <td><?= htmlspecialchars($api['created']); ?></td>
                                    </tr>
                                <?php }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                 <!-- Pagination Controls -->
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php if ($current_page > 1) { ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $current_page - 1; ?>">Previous</a></li>
                        <?php } ?>
                
                        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                            <li class="page-item <?= $i == $current_page ? 'active' : ''; ?>"><a class="page-link"
                                    href="?page=<?= $i; ?>"><?= $i; ?></a></li>
                        <?php } ?>
                
                        <?php if ($current_page < $total_pages) { ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $current_page + 1; ?>">Next</a></li>
                        <?php } ?>
                    </ul>
                </nav>

                <!-- Display API Response -->
                <?php if (isset($response) && $response) { ?>
                    <div class="alert alert-info mt-3">
                        <strong>API Response:</strong>
                        <pre><?= print_r($response, true); ?></pre>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <?php require_once "includes/footer.php"; ?>
</body>

</html>