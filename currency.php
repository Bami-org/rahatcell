<?php require_once "includes/conn.php";

if (isset($_POST["add"])) {
    $sql = $db->insert("currency", [
        "name" => $db->clean_input($_POST["name"]),
        "symbol" => $db->clean_input($_POST["symbol"]),
    ]);
    if ($sql) {
        $db->route("currency?opr=success");
    } else {
        $db->show_err();
    }
}

if (isset($_POST["update"])) {
    $sql = $db->update(
        "currency",
        [
            "name" => $db->clean_input($_POST["name"]),
            "symbol" => $db->clean_input($_POST["symbol"])
        ],
        "id=" . $db->clean_input($_POST["currency_id"])
    );
    if ($sql) {
        $db->route("currency?opr=success");
    } else {
        $db->show_err();
    }
}

$sql = $db->query("SELECT * FROM currency ORDER BY id DESC");
$row = $sql->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>ارز ها</title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li><span
                    class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item">ارز ها</li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <form class="card needs-validation" method="post" novalidate hidden>
            <div class="card-header">
                <h2>ثبت ارز جدید</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name">اسم:</label>
                            <input type="text" id="name" class="form-control" name="name" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="symbol">سمبول:</label>
                            <input type="text" id="symbol" class="form-control" name="symbol">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" name="add" class="btn btn-primary">ثبت کردن</button>
                <button type="reset" class="btn btn-danger">انصراف</button>
            </div>
        </form>
        <hr>
        <div class="card">
            <div class="card-header">
                <h3>لیست ارز ها</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th>اسم</th>
                                <th>سمبول</th>
                                <th style="width: 15%;">تاریخ ایجاد</th>
                                <th hidden style="width: 8%;">عملکرد</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($sql->num_rows > 0) {
                                $i = 1;
                                do { ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= $row["name"] ?></td>
                                <td><?= $row["symbol"] ?></td>
                                <td><?= $db->convertFullDate($row["created"],$setting["date_type"]) ?></td>
                                <td hidden  class="text-center p-0 no-print">
                                    <div class="btn-group" dir="ltr">
                                        <!-- <button class="btn btn-danger btn-sm pb-0 pt-2" onclick="showQ('<?= $row['id'] ?>')"><span class="ico h6">delete</span></button> -->
                                        <button class="btn btn-success btn-sm pb-0 pt-2"
                                            onclick="getInfo('<?= $row['id'] ?>')"><span
                                                class="ico h6">edit</span></button>
                                    </div>
                                </td>
                            </tr>
                            <?php } while ($row = $sql->fetch_assoc());
                            } ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- edit modal -->
    <div id="edit-modal" class="modal fade" data-backdrop="static">
        <div class="modal-dialog">
            <form method="POST" class="modal-content needs-validation" novalidate>
                <div class="modal-header">
                    <h2>ویرایش ارز</h2>
                    <button class="btn btn-danger" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="currency_id" name="currency_id">
                    <div class="form-group">
                        <label for="name"> اسم:</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="symbol"> سمبول:</label>
                        <input type="text" id="symbol" name="symbol" class="form-control">
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button class="btn btn-primary" type="submit" name="update">ذخیره تغییرات</button>
                    <button class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                </div>
            </form>
        </div>
    </div>
    <!-- // edit modal -->


    <?php require_once "includes/footer.php" ?>

    <script>
    // for delete
    function showQ(id) {
        delQ("currency_id=" + id)
    }

    // for update
    function getInfo(id) {
        $("#edit-modal #currency_id").val(id);
        $.ajax({
            type: "get",
            url: "ajax/get_info",
            data: {
                currency_id: id
            },
            success: function(response) {
                var res = JSON.parse(response);
                $("#edit-modal #name").val(res["name"]);
                $("#edit-modal #symbol").val(res["symbol"]);
                $("#edit-modal").modal('show');
            }
        });
    }
    </script>
</body>

</html>