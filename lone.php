<?php
require_once "includes/conn.php";

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

    if ($action === 'get_loan') {
        $loan_amount = $_POST['loan_amount'];
        $loan_term = $_POST['loan_term'];
        $data = [
            'loanAmount' => $loan_amount,
            'loanTerm' => $loan_term,
            'bankId' => $bank_id,
        ];

        $response = makeApiRequest($url, $action, $data);

        if ($response && isset($response['success']) && $response['success']) {
            $db->query("UPDATE api_credentials SET my_loan = my_loan + $loan_amount, my_money = my_money + $loan_amount WHERE dealer_code = '$dealer_code'");
            $db->query("INSERT INTO loans (dealer_code, bank_id, loan_amount, loan_term) VALUES ('$dealer_code', '$bank_id', $loan_amount, $loan_term)");
        }
    } elseif ($action === 'repay_loan') {
        $repay_amount = $_POST['repay_amount'];
        $data = [
            'repayAmount' => $repay_amount,
            'bankId' => $bank_id,
        ];

        $response = makeApiRequest($url, $action, $data);

        if ($response && isset($response['success']) && $response['success']) {
            $db->query("UPDATE api_credentials SET my_loan = my_loan - $repay_amount WHERE dealer_code = '$dealer_code'");
        }
    }
}
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
            <div class="card-header">
                <h2>API Management and Loan Operations</h2>
            </div>
            <div class="card-body">
                <!-- Display API credentials -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Dealer Code</th>
                                <th>Username</th>
                                <th>Base URL</th>
                                <th>My Loan</th>
                                <th>My Money</th>
                                <th>Loan History</th>
                                <th>Actions</th>
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
                                        <a href="loan_history.php?dealer_code=<?= $api['dealer_code']; ?>"
                                            class="btn btn-info btn-sm">View History</a>
                                    </td>
                                    <td>
                                        <!-- Take Loan Button -->
                                        <button class="btn btn-success btn-sm" data-toggle="modal"
                                            data-target="#getLoanModal" data-url="<?= $api['base_url']; ?>"
                                            data-dealer="<?= $api['dealer_code']; ?>">
                                            Take Loan
                                        </button>
                                        <!-- Repay Loan Button -->
                                        <button class="btn btn-primary btn-sm" data-toggle="modal"
                                            data-target="#repayLoanModal" data-url="<?= $api['base_url']; ?>"
                                            data-dealer="<?= $api['dealer_code']; ?>">
                                            Repay Loan
                                        </button>
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

    <!-- Modals for Loan Operations -->
    <!-- Get Loan Modal -->
    <div class="modal fade" id="getLoanModal" tabindex="-1" role="dialog" aria-labelledby="getLoanModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="getLoanModalLabel">Take Loan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="get_loan">
                        <input type="hidden" name="base_url" id="getLoanBaseUrl">
                        <input type="hidden" name="dealer_code" id="getLoanDealerCode">
                        <div class="form-group">
                            <label for="loan_amount">Loan Amount:</label>
                            <input type="number" class="form-control" name="loan_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="loan_term">Loan Term (months):</label>
                            <input type="number" class="form-control" name="loan_term" required>
                        </div>
                        <div class="form-group">
                            <label for="bank">Bank:</label>
                            <select class="form-control" name="bank_id" required>
                                <option value="">Select a Bank</option>
                                <?php foreach ($banks as $bank) { ?>
                                    <option value="<?= $bank['id']; ?>"><?= $bank['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Submit</button>
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
                        <h5 class="modal-title" id="repayLoanModalLabel">Repay Loan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="repay_loan">
                        <input type="hidden" name="base_url" id="repayLoanBaseUrl">
                        <input type="hidden" name="dealer_code" id="repayLoanDealerCode">
                        <div class="form-group">
                            <label for="repay_amount">Repay Amount:</label>
                            <input type="number" class="form-control" name="repay_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="bank">Bank:</label>
                            <select class="form-control" name="bank_id" required>
                                <option value="">Select a Bank</option>
                                <?php foreach ($banks as $bank) { ?>
                                    <option value="<?= $bank['id']; ?>"><?= $bank['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
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
    </script>
    <?php require_once "includes/footer.php"; ?>
</body>

</html>