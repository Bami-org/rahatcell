<?php
require_once "includes/conn.php";

// Retrieve all customers from the database
$sql = $db->query("SELECT 
    currency.name AS c_name, 
    balance.balance AS balance, 
    currency.id AS c_id, 
    customer.* 
FROM customer 
LEFT JOIN currency ON customer.currency_id = currency.id 
LEFT JOIN balance ON balance.customer_id = customer.id 
ORDER BY customer.id DESC");

// Retrieve total balances and counts grouped by currency
$balance_sql = $db->query("SELECT 
    SUM(balance.balance) AS balance, 
    currency.name AS currency, 
    COUNT(customer.id) AS cs_count 
FROM balance 
LEFT JOIN customer ON balance.customer_id = customer.id 
LEFT JOIN currency ON customer.currency_id = currency.id 
GROUP BY customer.currency_id");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>مشتریان</title>
    <!-- Include Bootstrap CSS -->
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include Bootstrap JS -->

</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- Start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li>
                <span class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item"><a href="customer">مشتریان</a></li>
                <span class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item">لیست</li>
            </ul>
        </div>
        <!-- End of breadcrumb -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2>لیست مشتریان</h2>
                <div class="btn-group" dir="ltr">
                    <button class="btn btn-info bt-ico" onclick="print()">پرینت <span class="ico">print</span></button>
                    <a href="add_customer" class="btn btn-primary bt-ico">جدید <span class="ico">add</span></a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th>اسم مشتری</th>
                                <th>شماره تماس</th>
                                <th>بلانس</th>
                                <th>ارز</th>
                                <th>نام کاربری</th>
                                <th>نوع مشتری</th>
                                <th>تاریخ ثبت</th>
                                <th style="width: 9%;">عملکرد</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($sql->num_rows > 0):
                                $n = 1;
                                while ($row = $sql->fetch_assoc()): ?>
                                    <tr class="<?= $row["status"] == "Deactive" ? "table-danger" : "" ?>">
                                        <td><?= $n++ ?></td>
                                        <td><?= htmlspecialchars($row["name"]) ?></td>
                                        <td><?= htmlspecialchars($row["phone"]) ?></td>
                                        <td><?= $row["c_id"] == 1 ? number_format($row["balance"] ?? 0) : $row["balance"] ?>
                                        </td>
                                        <td><?= htmlspecialchars($row["c_name"]) ?></td>
                                        <td><?= htmlspecialchars($row["username"]) ?></td>
                                        <td><?= htmlspecialchars($row["customer_type"]) ?></td>
                                        <td><?= $db->convertFullDate($row["created"], $setting["date_type"]) ?></td>
                                        <td class="text-center p-0 no-print">
                                            <div class="btn-group" dir="ltr">
                                                <button class="btn btn-danger btn-sm m-1 pb-0 pt-2"
                                                    onclick="showQ('<?= $row['id'] ?>')"><span
                                                        class="ico h6">delete</span></button>
                                                <a href="customer_profile?id=<?= $row["id"] ?>"
                                                    class="btn btn-info m-1 btn-sm pb-0 pt-2"><span
                                                        class="ico h6">person</span></a>
                                                <button class="btn btn-success btn-sm m-1 pb-0 pt-2"
                                                    onclick="getInfo('<?= $row['id'] ?>')"><span
                                                        class="ico h6">edit</span></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile;
                            else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">هیچ مشتری یافت نشد.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <hr>
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>مجموع بیلانس</th>
                                <th>نوع ارز</th>
                                <th>تعداد مشتریان اصلی دارای بیلانس</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($balance_sql->num_rows > 0):
                                while ($balance_row = $balance_sql->fetch_assoc()): ?>
                                    <tr>
                                        <td class="py-2 font-weight-bold"><?= number_format($balance_row["balance"]) ?></td>
                                        <td class="py-2"><?= htmlspecialchars($balance_row["currency"]) ?></td>
                                        <td class="py-2"><?= $balance_row["cs_count"] ?></td>
                                    </tr>
                                <?php endwhile;
                            else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">اطلاعات موجود نیست.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- edit modal -->
    <div id="edit-modal" class="modal fade" data-backdrop="static">
        <div class="modal-dialog modal-xl">
            <form id="edit-form" method="POST" class="modal-content needs-validation" novalidate>
                <div class="modal-header">
                    <h2>ویرایش معلومات مشتری</h2>
                    <button class="btn btn-danger" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="customer_id" name="customer_id">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">اسم:</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="phone">شماره تماس:</label>
                                <input type="text" id="phone" name="phone" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="customer_type">نوع مشتری:</label>
                                <select id="customer_type" name="customer_type" class="form-control" required>
                                    <option selected disabled>انتخاب</option>
                                    <option value="عمده">عمده</option>
                                    <option value="پرچون">پرچون</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="address">آدرس:</label>
                                <input type="text" id="address" name="address" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="currency_id">ارز:</label>
                                <select id="currency_id" name="currency_id" class="form-control">
                                    <option selected disabled>انتخاب</option>
                                    <?php
                                    $c_sql = $db->query("SELECT * FROM currency ORDER BY id");
                                    $c_row = $c_sql->fetch_assoc();
                                    if ($c_sql->num_rows > 0) {
                                        do {
                                            ?>
                                            <option value="<?= $c_row["id"] ?>"><?= $c_row["name"] ?></option>
                                        <?php } while ($c_row = $c_sql->fetch_assoc());
                                    } else { ?>
                                        <option disabled>هنوز ثبت نشده</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="username">نام کاربری:</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="password">پسورد:</label>
                                <input type="text" id="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="pin_code">پین کود:</label>
                                <input type="text" id="pin_code" name="pin_code" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">وضعیت حساب:</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="Active">فعال</option>
                                    <option value="Deactive">غیر فعال</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button class="btn btn-primary" type="submit" name="update">ذخیره تغییرات</button>
                    <button class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                </div>
            </form>
        </div>
    </div>

    <?php require_once "includes/footer.php" ?>
    <script>
        $(document).ready(function () {
            $('#edit-form').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'ajax/update_customer.php', // Update this path if necessary
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        // Handle success response
                        console.log(response);
                        $('#edit-modal').modal('hide');
                        location.reload(); // Reload the page to see the changes
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            });
        });

        // for edit
        function getInfo(id) {
            $.ajax({
                url: 'ajax/get_info.php', // Update this path if necessary
                type: 'GET',
                data: { customer_id: id },
                success: function (response) {
                    var data = JSON.parse(response);
                    // Populate your modal or form fields with the data
                    $('#customer_id').val(id);
                    $('#name').val(data.name);
                    $('#phone').val(data.phone);
                    $('#address').val(data.address);
                    $('#currency_id').val(data.currency_id);
                    $('#username').val(data.username);
                    $('#password').val(data.password);
                    $('#pin_code').val(data.pin_code);
                    $('#status').val(data.status);
                    $('#customer_type').val(data.customer_type);

                    // Show the modal
                    $('#edit-modal').modal('show');
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }

        function showQ(id) {
            Swal.fire({
                title: 'آیا میخواهید این مشتری را حذف کنید؟',
                text: "این عمل را بازگشت نمیتوانید!",
                cancelButtonText: 'لغو',
                icon: 'اخطاریه',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'بلی!'
            }).then((result) => {
                if (result.isConfirmed) {
                    delQ(id);
                }
            });
        }

        function delQ(id) {
            $.ajax({
                url: 'ajax/delete_customer.php', // Update this path if necessary
                type: 'POST',
                data: { customer_id: id },
                success: function (response) {
                    // Handle success response
                    console.log(response);
                    location.reload(); // Reload the page to see the changes
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }
    </script>
</body>

</html>