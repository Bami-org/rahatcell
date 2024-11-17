<?php require_once "includes/conn.php";

if (isset($_POST["add"])) {
    $sql = $db->insert(
        "announcement",
        [
            "title" => $db->clean_input($_POST["title"]),
            "content" => $db->clean_input($_POST["content"])
        ]
    );
    $sql ? $db->route("announcement?opr=success") : $db->show_err();
}

if (isset($_POST["update"])) {
    $sql = $db->update(
        "announcement",
        [
            "title" => $db->clean_input($_POST["title"]),
            "content" => $db->clean_input($_POST["content"])
        ],
        "id=" . $db->clean_input($_POST["ann_id"])
    );
    if ($sql) {
        $db->route("announcement?update=success");
    } else {
        $db->show_err();
    }
}

$sql = $db->query("SELECT * FROM announcement ORDER BY id DESC");


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>اطلاعیه</title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li><span class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item">اطلاعیه ها</li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <div class="card">
            <div class="card-header">
                <h2>اطلاعیه</h2>
            </div>
            <div class="card-body">
                <form method="POST" class="card needs-validation" novalidate>
                    <div class="card-header">
                        <h3>اطلاعیه جدید</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="title">عنوان:</label>
                                    <input type="text" id="title" class="form-control" name="title" maxlength="40" required>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="content">متن اطلاعیه:</label>
                                    <input id="content" class="form-control" type="text" maxlength="500" name="content" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" type="submit" name="add">انتشار اطلاعیه</button>
                    </div>
                </form>
                <hr>
                <div class="card">
                    <div class="card-header">
                        <h3>لیست اطلاعیه ها</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th>عنوان</th>
                                        <th>متن اطلاعیه</th>
                                        <th style="width: 15%;">تاریخ</th>
                                        <th style="width: 8%;">عملکرد</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($sql->num_rows > 0) {
                                        $row = $sql->fetch_assoc();
                                        $i = 1;
                                        do { ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= $row["title"] ?></td>
                                                <td><?= $row["content"] ?></td>
                                                <td><?= $db->convertFullDate($row["created"], $setting["date_type"]) ?></td>
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
                </div>
            </div>

        </div>
    </div>


    <!-- edit modal -->
    <div id="edit-modal" class="modal fade" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <form method="POST" class="modal-content needs-validation" novalidate>
                <div class="modal-header">
                    <h2>ویرایش اطلاعیه</h2>
                    <button class="btn btn-danger" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="ann_id" name="ann_id">
                    <div class="form-group">
                        <label for="title"> عنوان:</label>
                        <input type="text" id="title" name="title" class="form-control" maxlength="40" required>
                    </div>
                    <div class="form-group">
                        <label for="content"> متن اطلاعیه:</label>
                        <textarea id="content" name="content" class="form-control" maxlength="500" rows="4"></textarea>
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
            delQ("announcement_id=" + id)
        }

        // for update
        function getInfo(id) {
            $("#edit-modal #ann_id").val(id);
            $.ajax({
                type: "get",
                url: "ajax/get_info",
                data: {
                    ann_id: id
                },
                success: function(response) {
                    var res = JSON.parse(response);
                    $("#edit-modal #title").val(res["title"]);
                    $("#edit-modal #content").html(res["content"]);
                    $("#edit-modal").modal('show');
                }
            });
        }
    </script>
</body>

</html>