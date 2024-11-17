<?php require_once "includes/conn.php";
if (isset($_POST["add"])) {
    $sql = $db->insert("units", [
        "name" => $db->clean_input($_POST["name"])
    ]);
    if ($sql) {
        $db->route("units?opr=success");
    } else {
        $db->show_err();
    }
}

if (isset($_POST["update"])) {
    $sql = $db->update(
        "units",
        [
            "name" => $db->clean_input($_POST["name"]),
        ],
        "id=" . $db->clean_input($_POST["unit_id"])
    );
    if ($sql) {
        $db->route("units?opr=success");
    } else {
        $db->show_err();
    }
}


$sql = $db->query("SELECT * FROM units ORDER BY id");
$row = $sql->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>یونیت ها</title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li><span class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item">یونیت ها</li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <form method="post" class="card needs-validation" novalidate>
            <div class="card-header">
                <h2>ایجاد یونیت</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name">اسم یونیت:</label>
                            <input type="text" id="name" name="name" class="form-control" required>
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
                <h3>یونیت ها</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th>اسم یونیت</th>
                                <th>تاریخ ایجاد</th>
                                <th style="width: 8%;">عملکرد</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($sql->num_rows > 0) {
                                $n = 1;
                                do { ?>
                                    <tr>
                                        <td><?= $n++ ?></td>
                                        <td><?= $row["name"] ?></td>
                                        <td><?= $db->convertFullDate($row["created"],$setting["date_type"]) ?></td>
                                        <td class="text-center p-0 no-print">
                                            <div class="btn-group" dir="ltr">
                                                <button class="btn btn-danger btn-sm pb-0 pt-2" onclick="showQ('<?= $row['id'] ?>')"><span class="ico h6">delete</span></button>
                                                <button class="btn btn-success btn-sm pb-0 pt-2" onclick="getInfo('<?= $row['id'] ?>')"><span class="ico h6">edit</span></button>
                                            </div>
                                        </td>
                                    </tr>
                            <?php } while ($row = $sql->fetch_assoc());
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
            </div>
        </div>
    </div>

    <!-- edit modal -->
    <div id="edit-modal" class="modal fade" data-backdrop="static">
        <div class="modal-dialog">
            <form method="POST" class="modal-content needs-validation" novalidate>
                <div class="modal-header">
                    <h2>ویرایش یونیت</h2>
                    <button class="btn btn-danger" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="unit_id" name="unit_id">
                    <div class="form-group">
                        <label for="name"> اسم:</label>
                        <input type="text" id="name" name="name" class="form-control" required>
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
            delQ("unit_id=" + id)
        }

        // for update
        function getInfo(id) {
            $("#edit-modal #unit_id").val(id);
            $.ajax({
                type: "get",
                url: "ajax/get_info",
                data: {
                    unit_id: id
                },
                success: function(response) {
                    var res = JSON.parse(response);
                    $("#edit-modal #name").val(res["name"]);
                    $("#edit-modal").modal('show');
                }
            });
        }
    </script>
</body>

</html>