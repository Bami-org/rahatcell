<?php
require_once "includes/conn.php";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_bank'])) {
        $name = $db->real_escape_string($_POST['name']);
        $description = $db->real_escape_string($_POST['description']);
        $db->query("INSERT INTO bank (name, description, created) VALUES ('$name', '$description', NOW())");
    } elseif (isset($_POST['delete_bank'])) {
        $bank_id = $db->real_escape_string($_POST['bank_id']);
        $db->query("DELETE FROM bank WHERE id = '$bank_id'");
    } elseif (isset($_POST['edit_bank'])) {
        $bank_id = $db->real_escape_string($_POST['bank_id']);
        $name = $db->real_escape_string($_POST['name']);
        $description = $db->real_escape_string($_POST['description']);
        $db->query("UPDATE bank SET name = '$name', description = '$description' WHERE id = '$bank_id'");
    }
}

// Fetch bank details with total money
$query = "
SELECT 
    bank.id,
    bank.name,
    bank.description,
    bank.created,
    SUM(admin_balance.balance) AS total_money
FROM 
    bank
LEFT JOIN 
    admin_balance ON bank.id = admin_balance.bank_id
GROUP BY 
    bank.id, bank.name, bank.description, bank.created;
";

$result = $db->query($query);
$banks = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <?php require_once "includes/header.php" ?>

    <title>Bank Details</title>
    <link rel="stylesheet" href="path/to/your/css/styles.css">

</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <div class="card_body">
            <h1>جزییات بانک ها</h1>
            <button class="btn btn-success mb-3" onclick="showAddBankModal()">اضافه کردن بانک</button>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>نام</th>
                            <th>جزییات</th>
                            <th>ساخته شده</th>
                            <th>مجموع پول</th>
                            <th>عملکرد</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($banks as $bank): ?>
                            <tr>
                                <td><?= htmlspecialchars($bank['id']) ?></td>
                                <td><?= htmlspecialchars($bank['name']) ?></td>
                                <td><?= htmlspecialchars($bank['description']) ?></td>
                                <td><?= htmlspecialchars($bank['created']) ?></td>
                                <td><?= $bank['total_money'] !== null ? number_format($bank['total_money'], 2) : '0.00' ?>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;" onsubmit="return confirmDelete()">
                                        <input type="hidden" name="bank_id" value="<?= htmlspecialchars($bank['id']) ?>">
                                        <button type="submit" name="delete_bank" class="btn btn-danger">حذف</button>
                                    </form>
                                    <button class="btn btn-primary"
                                        onclick="showEditBankModal(<?= htmlspecialchars($bank['id']) ?>, '<?= htmlspecialchars($bank['name']) ?>', '<?= htmlspecialchars($bank['description']) ?>')">ویرایش</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>


            <!-- Add Bank Modal -->
            <div class="modal" id="addBankModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">اضافه کردن بانک</h4>
                        </div>
                        <div class="modal-body">
                            <form method="post">
                                <div class="form-group">
                                    <label for="name">نام:</label>
                                    <input type="text" name="name" id="name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="description">جزییات:</label>
                                    <textarea name="description" id="description" class="form-control"
                                        required></textarea>
                                </div>
                                <button type="submit" name="add_bank" class="btn btn-success">Add Bank</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Bank Modal -->
            <div class="modal" id="editBankModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">ویرایش بانک</h4>
                        </div>
                        <div class="modal-body">
                            <form method="post" id="editBankForm">
                                <input type="hidden" name="bank_id" id="edit_bank_id">
                                <div class="form-group">
                                    <label for="edit_name">نام:</label>
                                    <input type="text" name="name" id="edit_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_description">جزییات:</label>
                                    <textarea name="description" id="edit_description" class="form-control"
                                        required></textarea>
                                </div>
                                <button type="submit" name="edit_bank" class="btn btn-primary">ویرایش بانک</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function showAddBankModal() {
            $('#addBankModal').modal('show');
        }

        function showEditBankModal(id, name, description) {
            document.getElementById('edit_bank_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            $('#editBankModal').modal('show');
        }
        function confirmDelete() {
            return confirm('آیا مطمئن هستید که می‌خواهید این بانک را حذف کنید؟');
        }
    </script>
    <?php require_once "includes/footer.php"; ?>

</body>

</html>