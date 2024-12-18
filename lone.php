<?php
require_once "includes/conn.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Ensure $id is an integer for security

    // Start a transaction manually using mysqli methods
    $db->query("START TRANSACTION");

    try {
        // Step 1: Delete rows in assigned_product_package referencing external_packages
        $deleteAssigned = $db->query("DELETE FROM assigned_product_package WHERE package_id IN (
            SELECT id FROM external_packages WHERE api_credentials_id = $id
        )");

        if (!$deleteAssigned) {
            throw new Exception("Failed to delete rows from assigned_product_package.");
        }

        // Step 2: Delete rows in external_packages referencing api_credentials
        $deletePackages = $db->query("DELETE FROM external_packages WHERE api_credentials_id = $id");

        if (!$deletePackages) {
            throw new Exception("Failed to delete rows from external_packages.");
        }

        // Step 3: Delete the main row in api_credentials
        $deleteApi = $db->query("DELETE FROM api_credentials WHERE id = $id");

        if (!$deleteApi) {
            throw new Exception("Failed to delete row from api_credentials.");
        }

        // Commit the transaction
        $db->query("COMMIT");

        // Redirect to a confirmation or listing page
        header("Location: lone.php?message=deleted");
        exit;
    } catch (Exception $e) {
        // Roll back the transaction in case of an error
        $db->query("ROLLBACK");

        // Display or log the error
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}


function makeApiRequest($url, $operation, $data)
{
    $payload = array_merge(["operation" => $operation], $data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Fetch API credentials
$api_credentials_sql = $db->query("SELECT * FROM api_credentials ORDER BY id ASC");
$api_credentials = $api_credentials_sql->fetch_all(MYSQLI_ASSOC);

// Fetch bank list
$bank_sql = $db->query("SELECT * FROM bank ORDER BY name ASC");
$banks = $bank_sql->fetch_all(MYSQLI_ASSOC);

// Handle loan actions
$response = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $url = $_POST['base_url'];
    $dealer_code = $db->real_escape_string($_POST['dealer_code']);
    $bank_id = $db->real_escape_string($_POST['bank_id']);
    $data = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'get_loan') {
        // Retrieve data from the form
        $dealer_code = $db->real_escape_string($_POST['dealer_code']);
        $loan_amount = floatval($_POST['loan_amount']);

        // Start a transaction
        $db->beginTransaction();
        try {
            // Increase `my_loan` and `my_money` for the dealer in `api_credentials`
            $db->query("
            UPDATE api_credentials 
            SET my_loan = my_loan + $loan_amount, 
                my_money = my_money + $loan_amount 
            WHERE dealer_code = '$dealer_code'
        ");

            $db->commit();
            echo "Loan successfully added to your account.";
        } catch (Exception $e) {
            $db->rollback();
            echo "Failed to process the loan: " . $e->getMessage();
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'repay_loan') {
        // Retrieve data from the form
        $dealer_code = $db->real_escape_string($_POST['dealer_code']);
        $repay_amount = floatval($_POST['repay_amount']);

        // Start a transaction
        $db->beginTransaction();
        try {
            // Decrease `my_loan` and `my_money` for the dealer in `api_credentials`
            $db->query("
            UPDATE api_credentials 
            SET my_loan = my_loan - $repay_amount, 
                my_money = my_money - $repay_amount 
            WHERE dealer_code = '$dealer_code'
        ");

            $db->commit();
            echo "Loan successfully repaid.";
        } catch (Exception $e) {
            $db->rollback();
            echo "Failed to process the repayment: " . $e->getMessage();
        }
    }






}

// Fetch loan history
$loan_history_sql = $db->query("SELECT * FROM loans ORDER BY id DESC");
$loan_history = $loan_history_sql->fetch_all(MYSQLI_ASSOC);

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
                <a href="AddAPI">
                    <button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" title="اضافه کردن"><span
                            class="ico h6">add اضافه کردن </span></button>
                </a>

            </div>
            <div class="card-body">
                <!-- Display API credentials -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr></tr>
                            <th>کد فروشنده</th>
                            <th>نام کاربری</th>
                            <th>آدرس پایه</th>
                            <th>قرض من</th>
                            <th>پول من</th>
                            <th>تاریخچه قرض</th>
                            <th>اقدامات</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($api_credentials as $api) { ?>
                                <tr>
                                    <td><?= $api['dealer_code']; ?></td>
                                    <td><?= $api['username']; ?></td>
                                    <td><?= $api['base_url']; ?></td>
                                    <td><?= $api['my_loan']; ?></td>
                                    <td><?= $api['my_money']; ?></td>
                                    <td>
                                        <button class="btn btn-info btn-sm" data-toggle="modal"
                                            data-target="#loanHistoryModal"
                                            data-dealer="<?= htmlspecialchars($api['dealer_code']); ?>">تاریخچه
                                            انتقالات</button>
                                    </td>
                                    <td class="text-center justify-content-center  p-0 no-print">
                                        <!-- Take Loan Button -->
                                        <button class="btn btn-success btn-sm m-1" data-toggle="modal"
                                            data-target="#getLoanModal" data-url="<?= $api['base_url']; ?>"
                                            data-dealer="<?= $api['dealer_code']; ?>">
                                            <span class="ico h6">add</span>
                                        </button>
                                        <!-- Repay Loan Button -->
                                        <button class="btn btn-primary btn-sm" data-toggle="modal"
                                            data-target="#repayLoanModal" data-url="<?= $api['base_url']; ?>"
                                            data-dealer="<?= $api['dealer_code']; ?>">
                                            <span class="ico h6">remove</span>
                                        </button>
                                        <a href="lone.php?id=<?= $api['id']; ?>"
                                            class="btn btn-danger btn-sm pb-0 pt-2"><span class="ico h6">delete</span></a>
                                        <a href="editapi.php?id=<?= $api['id']; ?>"
                                            class="btn btn-success btn-sm pb-0 pt-2"><span class="ico h6">edit</span></a>
                                    </td>

                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Display API Response -->
                <?php if ($response) { ?>
                    <div class="alert alert-info mt-3">
                        <strong>API Response:</strong>
                        <pre><?= print_r($response, true); ?></pre>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Loan History Modal -->
    <div class="modal" id="loanHistoryModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">تاریخچه وام</h4>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>کد فروشنده</th>
                                <th>کد بانک</th>
                                <th>مقدار وام</th>
                                <th>مدت وام</th>
                                <th>تاریخ</th>
                            </tr>
                        </thead>
                        <tbody id="loanHistoryBody">
                            <!-- Loan history data will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals for Loan Operations -->
    <!-- Get Loan Modal -->
    <div class="modal fade" id="getLoanModal" tabindex="-1" role="dialog" aria-labelledby="getLoanModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h5 class="modal-title" id="getLoanModalLabel">گرفتن قرض</h5>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="get_loan">
                        <input type="hidden" name="base_url" id="getLoanBaseUrl">
                        <input type="hidden" name="dealer_code" id="getLoanDealerCode">
                        <div class="form-group">
                            <label for="loan_amount">مقدار:</label>
                            <input type="number" class="form-control" name="loan_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="loan_term">برای چند ماه:</label>
                            <input type="number" class="form-control" name="loan_term" required>
                        </div>
                        <div class="form-group">
                            <label for="bank">به کدام بانک:</label>
                            <select class="form-control" name="bank_id" required>
                                <option value="">انتخاب یک بان</option>
                                <?php foreach ($banks as $bank) { ?>
                                    <option value="<?= $bank['id']; ?>"><?= $bank['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">تایید</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Repay Loan Modal -->
    <div class="modal fade" id="repayLoanModal" tabindex="-1" role="dialog" aria-labelledby="repayLoanModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h5 class="modal-title" id="repayLoanModalLabel">پرداخت قرض</h5>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="repay_loan">
                        <input type="hidden" name="base_url" id="repayLoanBaseUrl">
                        <input type="hidden" name="dealer_code" id="repayLoanDealerCode">
                        <div class="form-group">
                            <label for="repay_amount">مقدار:</label>
                            <input type="number" class="form-control" name="repay_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="bank">از کدام بانک:</label>
                            <select class="form-control" name="bank_id" required>
                                <option value="">انتخاب بانک</option>
                                <?php foreach ($banks as $bank) { ?>
                                    <option value="<?= $bank['id']; ?>"><?= $bank['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">تایید</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-target="#getLoanModal"]').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('getLoanBaseUrl').value = this.dataset.url;
                document.getElementById('getLoanDealerCode').value = this.dataset.dealer;
            });
        });

        document.querySelectorAll('[data-target="#repayLoanModal"]').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('repayLoanBaseUrl').value = this.dataset.url;
                document.getElementById('repayLoanDealerCode').value = this.dataset.dealer;
            });
        });

        $('#loanHistoryModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var dealerCode = button.data('dealer');
            var modal = $(this);
            var loanHistoryBody = modal.find('#loanHistoryBody');

            // Clear previous data
            loanHistoryBody.empty();

            // Fetch loan history for the dealer
            $.ajax({
                url: 'fetch_loan_history.php',
                method: 'GET',
                data: { dealer_code: dealerCode },
                success: function (data) {
                    loanHistoryBody.html(data);
                }
            });
        });
        <!-- Loan History Modal -->
        <div class="modal" id="loanHistoryModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">تاریخچه وام</h4>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>کد فروشنده</th>
                                    <th>کد بانک</th>
                                    <th>مقدار وام</th>
                                    <th>مدت وام</th>
                                    <th>تاریخ</th>
                                </tr>
                            </thead>
                            <tbody id="loanHistoryBody">
                                <!-- Loan history data will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </script>
    <?php require_once "includes/footer.php"; ?>
</body>

</html>