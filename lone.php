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
    $dealer_code = $db->real_escape_string($_POST['dealer_code']);

    if ($action === 'add_money') {
        // Retrieve data from the form
        $add_amount = floatval($_POST['add_amount']);
        $bank_id = $db->real_escape_string($_POST['bank_id']);

        // Start a transaction
        $db->beginTransaction();
        try {
            // Check if the bank has enough balance
            $bank_balance_result = $db->query("SELECT balance FROM admin_balance WHERE bank_id = '$bank_id'");
            if (!$bank_balance_result) {
                throw new Exception("Failed to fetch bank balance: " . $db->error);
            }
            $bank_balance_row = $bank_balance_result->fetch_assoc();
            if (!$bank_balance_row) {
                throw new Exception("Bank not found: " . $bank_id);
            }
            $bank_balance = floatval($bank_balance_row['balance']);

            if ($bank_balance < $add_amount) {
                throw new Exception("Insufficient funds in the bank to add money.");
            }

            // Decrease the balance in the `admin_balance` table
            $update_balance_query = "
            UPDATE admin_balance 
            SET balance = balance - $add_amount 
            WHERE bank_id = '$bank_id'
            ";
            if (!$db->query($update_balance_query)) {
                throw new Exception("Failed to update admin_balance: " . $db->error);
            }

            // Increase `my_money` for the dealer in `api_credentials`
            $update_query = "
            UPDATE api_credentials 
            SET my_money = my_money + $add_amount 
            WHERE dealer_code = '$dealer_code'
            ";
            if (!$db->query($update_query)) {
                throw new Exception("Failed to update api_credentials: " . $db->error);
            }

            // Insert transaction into api_transactions table
            $insert_transaction_query = "
            INSERT INTO api_transactions (dealer_code, bank_id, transaction_type, amount, created)
            VALUES ('$dealer_code', '$bank_id', 'add_money', $add_amount, NOW())
            ";
            if (!$db->query($insert_transaction_query)) {
                throw new Exception("Failed to insert transaction record: " . $db->error);
            }

            $db->commit();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            $db->rollback();
            echo "Failed to add money: " . $e->getMessage();
        }
    }

    if ($action === 'get_loan') {
        // Retrieve data from the form
        $loan_amount = floatval($_POST['loan_amount']);
        $loan_term = intval($_POST['loan_term']); // Assuming loan_term is passed in the form

        // Start a transaction
        $db->beginTransaction();
        try {
            // Fetch current values
            $result = $db->query("SELECT my_loan, my_money FROM api_credentials WHERE dealer_code = '$dealer_code'");
            if (!$result) {
                throw new Exception("Failed to fetch current values: " . $db->error);
            }
            $row = $result->fetch_assoc();
            if (!$row) {
                throw new Exception("Dealer code not found: " . $dealer_code);
            }
            $current_loan = floatval($row['my_loan']);
            $current_money = floatval($row['my_money']);

            // Calculate new values
            $new_loan = $current_loan + $loan_amount;
            $new_money = $current_money + $loan_amount;

            // Update `my_loan` and `my_money` for the dealer in `api_credentials`
            $update_query = "
            UPDATE api_credentials 
            SET my_loan = $new_loan, 
                my_money = $new_money 
            WHERE dealer_code = '$dealer_code'
            ";
            if (!$db->query($update_query)) {
                throw new Exception("Failed to update api_credentials: " . $db->error);
            }

            // Insert transaction into api_transactions table
            $insert_transaction_query = "
            INSERT INTO api_transactions (dealer_code, bank_id, transaction_type, amount, created)
            VALUES ('$dealer_code', NULL, 'get_loan', $loan_amount, NOW())
            ";
            if (!$db->query($insert_transaction_query)) {
                throw new Exception("Failed to insert transaction record: " . $db->error);
            }

            $db->commit();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            $db->rollback();
            echo "Failed to process the loan: " . $e->getMessage();
        }
    }

    if ($action === 'repay_loan') {
        // Retrieve data from the form
        $repay_amount = floatval($_POST['repay_amount']);
        $repay_source = $db->real_escape_string($_POST['repay_source']);
        $bank_id = isset($_POST['bank_id']) ? $db->real_escape_string($_POST['bank_id']) : null;

        // Start a transaction
        $db->beginTransaction();
        try {
            if ($repay_source === 'bank') {
                // Check if the bank has enough balance
                $bank_balance_result = $db->query("SELECT balance FROM admin_balance WHERE bank_id = '$bank_id'");
                if (!$bank_balance_result) {
                    throw new Exception("Failed to fetch bank balance: " . $db->error);
                }
                $bank_balance_row = $bank_balance_result->fetch_assoc();
                if (!$bank_balance_row) {
                    throw new Exception("Bank not found: " . $bank_id);
                }
                $bank_balance = floatval($bank_balance_row['balance']);

                if ($bank_balance < $repay_amount) {
                    throw new Exception("Insufficient funds in the bank to repay the loan.");
                }

                // Decrease the balance in the `admin_balance` table
                $update_balance_query = "
                UPDATE admin_balance 
                SET balance = balance - $repay_amount 
                WHERE bank_id = '$bank_id'
                ";
                if (!$db->query($update_balance_query)) {
                    throw new Exception("Failed to update admin_balance: " . $db->error);
                }

                // Decrease `my_loan` for the dealer in `api_credentials`
                $update_query = "
                UPDATE api_credentials 
                SET my_loan = my_loan - $repay_amount 
                WHERE dealer_code = '$dealer_code'
                ";
                if (!$db->query($update_query)) {
                    throw new Exception("Failed to update api_credentials: " . $db->error);
                }

                // Insert transaction into api_transactions table
                $insert_transaction_query = "
                INSERT INTO api_transactions (dealer_code, bank_id, transaction_type, amount, created)
                VALUES ('$dealer_code', '$bank_id', 'repay_loan', -$repay_amount, NOW())
                ";
                if (!$db->query($insert_transaction_query)) {
                    throw new Exception("Failed to insert transaction record: " . $db->error);
                }
            } else {
                // Fetch current values
                $result = $db->query("SELECT my_loan, my_money FROM api_credentials WHERE dealer_code = '$dealer_code'");
                if (!$result) {
                    throw new Exception("Failed to fetch current values: " . $db->error);
                }
                $row = $result->fetch_assoc();
                if (!$row) {
                    throw new Exception("Dealer code not found: " . $dealer_code);
                }
                $current_loan = floatval($row['my_loan']);
                $current_money = floatval($row['my_money']);

                // Ensure the new values will not be negative
                if ($current_loan < $repay_amount || $current_money < $repay_amount) {
                    throw new Exception("Insufficient funds in the account to repay the loan.");
                }

                // Decrease `my_loan` and `my_money` for the dealer in `api_credentials`
                $update_query = "
                UPDATE api_credentials 
                SET my_loan = my_loan - $repay_amount, 
                    my_money = my_money - $repay_amount 
                WHERE dealer_code = '$dealer_code'
                ";
                if (!$db->query($update_query)) {
                    throw new Exception("Failed to update api_credentials: " . $db->error);
                }

                // Insert transaction into api_transactions table
                $insert_transaction_query = "
                INSERT INTO api_transactions (dealer_code, bank_id, transaction_type, amount, created)
                VALUES ('$dealer_code', NULL, 'repay_loan', -$repay_amount, NOW())
                ";
                if (!$db->query($insert_transaction_query)) {
                    throw new Exception("Failed to insert transaction record: " . $db->error);
                }
            }

            $db->commit();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            $db->rollback();
            echo "Failed to process the repayment: " . $e->getMessage();
        }
    }
}
// Fetch loan history






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
                                            data-dealer="<?= htmlspecialchars($api['dealer_code']); ?>"
                                            onclick="fetchLoanHistory('<?= htmlspecialchars($api['dealer_code']); ?>')">تاریخچه
                                            انتقالات</button>
                                    </td>
                                    <td class="text-center justify-content-center p-0 no-print">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-success btn-sm dropdown-toggle"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                بیشتر
                                            </button>
                                            <div class="dropdown-menu">
                                                <!-- Take Loan Button -->
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#getLoanModal" data-url="<?= $api['base_url']; ?>"
                                                    data-dealer="<?= $api['dealer_code']; ?>">
                                                    <span class="ico h6 btn-primary btn">قرض گرفتن از API</span>
                                                </a>
                                                <!-- Add Money Button -->
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#addMoneyModal" data-url="<?= $api['base_url']; ?>"
                                                    data-dealer="<?= $api['dealer_code']; ?>">
                                                    <span class="ico h6 btn btn-primary">اضافه کردن پول از بانک</span>
                                                </a>
                                                <!-- Repay Loan Button -->
                                                <a class="dropdown-item" href="#" data-toggle="modal"
                                                    data-target="#repayLoanModal" data-url="<?= $api['base_url']; ?>"
                                                    data-dealer="<?= $api['dealer_code']; ?>">
                                                    <span class="ico h6 btn btn-primary">پرداخت قرض</span>
                                                </a>
                                                <!-- Delete Button -->
                                                <a class="dropdown-item" href="lone.php?id=<?= $api['id']; ?>">
                                                    <span class="ico h6 btn btn-danger">حذف</span>
                                                </a>
                                                <!-- Edit Button -->
                                                <a class="dropdown-item" href="editapi.php?id=<?= $api['id']; ?>">
                                                    <span class="ico h6 btn btn-success">ویرایش</span>
                                                </a>
                                            </div>
                                        </div>
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
    <!-- Add Money Modal -->
    <div class="modal fade" id="addMoneyModal" tabindex="-1" role="dialog" aria-labelledby="addMoneyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h5 class="modal-title" id="addMoneyModalLabel">Add Money to Account</h5>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_money">
                        <input type="hidden" name="dealer_code" id="addMoneyDealerCode">
                        <div class="form-group">
                            <label for="add_amount">Amount:</label>
                            <input type="number" class="form-control" name="add_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="bank">From Bank:</label>
                            <select class="form-control" name="bank_id" required>
                                <option value="">Select Bank</option>
                                <?php foreach ($banks as $bank) { ?>
                                    <option value="<?= $bank['id']; ?>"><?= $bank['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Add Money</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                        <h5 class="modal-title" id="getLoanModalLabel">Get Loan</h5>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="get_loan">
                        <input type="hidden" name="dealer_code" id="getLoanDealerCode">
                        <div class="form-group">
                            <label for="loan_amount">Amount:</label>
                            <input type="number" class="form-control" name="loan_amount" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Get Loan</button>
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
                        <h5 class="modal-title" id="repayLoanModalLabel">Repay Loan</h5>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="repay_loan">
                        <input type="hidden" name="dealer_code" id="repayLoanDealerCode">
                        <div class="form-group">
                            <label for="repay_amount">Amount:</label>
                            <input type="number" class="form-control" name="repay_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="repay_source">Repay From:</label>
                            <select class="form-control" name="repay_source" required>
                                <option value="my_money">My Money</option>
                                <option value="bank">Bank</option>
                            </select>
                        </div>
                        <div class="form-group" id="bankSelectGroup" style="display: none;">
                            <label for="bank">From Bank:</label>
                            <select class="form-control" name="bank_id">
                                <option value="">Select Bank</option>
                                <?php foreach ($banks as $bank) { ?>
                                    <option value="<?= $bank['id']; ?>"><?= $bank['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Repay Loan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        document.querySelectorAll('[data-target="#addMoneyModal"]').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('addMoneyDealerCode').value = this.dataset.dealer;
            });
        });

        document.querySelectorAll('[data-target="#getLoanModal"]').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('getLoanDealerCode').value = this.dataset.dealer;
            });
        });

        document.querySelectorAll('[data-target="#repayLoanModal"]').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('repayLoanDealerCode').value = this.dataset.dealer;
            });
        });

        document.querySelector('select[name="repay_source"]').addEventListener('change', function () {
            if (this.value === 'bank') {
                document.getElementById('bankSelectGroup').style.display = 'block';
            } else {
                document.getElementById('bankSelectGroup').style.display = 'none';
            }
        });

        function fetchLoanHistory(dealerCode) {
            console.log('Fetching loan history for dealer:', dealerCode); // Log dealer code
            fetch(`ajax/fetch_transactions.php?dealer_code=${dealerCode}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        console.log('Fetched loan history data:', data.data); // Log fetched data
                        populateLoanHistory(data.data);
                    } else {
                        console.error('Error fetching loan history:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching loan history:', error));
        }

        function populateLoanHistory(transactions) {
            const loanHistoryBody = document.getElementById('loanHistoryBody');
            loanHistoryBody.innerHTML = ''; // Clear existing data

            transactions.forEach(transaction => {
                const row = document.createElement('tr');

                const dealerCodeCell = document.createElement('td');
                dealerCodeCell.textContent = transaction.dealer_code;
                row.appendChild(dealerCodeCell);

                const bankCodeCell = document.createElement('td');
                bankCodeCell.textContent = transaction.bank_id;
                row.appendChild(bankCodeCell);

                const amountCell = document.createElement('td');
                amountCell.textContent = transaction.amount;
                row.appendChild(amountCell);

                const dateCell = document.createElement('td');
                dateCell.textContent = transaction.created;
                row.appendChild(dateCell);

                loanHistoryBody.appendChild(row);
            });
        }
    </script>
    <?php require_once "includes/footer.php"; ?>
</body>

</html>