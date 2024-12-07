<?php require_once "includes/conn.php";


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
                <h2>قرض</h2>
            </div>
            <div class="card-body">
                <div class="card">
                    <div class="card-header"></div>
                    <h3>دریافت قرض</h3>
                </div>
                <div class="card-body">
                    <form action="get_loan.php" method="post">
                        <div class="form-group">
                            <label for="loan_amount">مقدار قرض:</label>
                            <input type="number" class="form-control" id="loan_amount" name="loan_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="loan_term">Loan Term (months):</label>
                            <input type="number" class="form-control" id="loan_term" name="loan_term" required>
                        </div>
                        <div class="form-group">
                            <label for="bank">بانک:</label>
                            <select class="form-control" id="bank" name="bank" required>
                                <option value="">انتخاب بانک</option>
                                <?php
                                $sql = $db->query("SELECT * FROM bank ORDER BY name ASC");
                                while ($row = $sql->fetch_assoc()) {
                                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header">
                    <h3>Repay Loan</h3>
                </div>
                <div class="card-body">
                    <form action="repay_loan.php" method="post">
                        <div class="form-group">
                            <label for="repay_amount">Repay Amount:</label>
                            <input type="number" class="form-control" id="repay_amount" name="repay_amount" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
    <?php require_once "includes/footer.php" ?>
</body>

</html>