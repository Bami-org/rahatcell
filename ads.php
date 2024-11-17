<?php require_once "includes/conn.php";

if (isset($_POST["add"])) {
    $title = $db->clean_input($_POST["title"]);
    $photo = $_FILES["photo"]["name"];
    move_uploaded_file($_FILES["photo"]["tmp_name"], "uploads/ads/" . $photo);
    $sql = $db->insert(
        "ads",
        [
            "title" => $title,
            "photo" => $photo
        ]
    );
    if ($sql) {
        $db->route("ads?add=success");
    } else {
        $db->show_err();
    }
}

if (isset($_POST["update"])) {
    $sql = $db->update(
        "ads",
        ["title" => $db->clean_input($_POST["title"])],
        "id=" . $db->clean_input($_POST["ad_id"])
    );
    if ($sql) {
        $db->route("ads?update=success");
    } else {
        $db->show_err();
    }
}

if (isset($_POST["update-photo"])) {
    $photo = $_FILES["photo"]["name"];
    $current_photo = mysqli_fetch_assoc($db->query("SELECT photo FROM ads WHERE id=" . $db->clean_input($_POST["ad_id"])));
    move_uploaded_file($_FILES["photo"]["tmp_name"], "uploads/ads/" . $photo);
    unlink("uploads/ads/" . $current_photo["photo"]);
    $sql = $db->update(
        "ads",
        ["photo" => $photo],
        "id=" . $db->clean_input($_POST["ad_id"])
    );
    if ($sql) {
        $db->route("ads?update=success");
    } else {
        $db->show_err();
    }
}

$sql = $db->query("SELECT * FROM ads ORDER BY id");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>تبلیغات</title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li><span
                    class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item"> تبلیغات</li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <div class="card">
            <div class="card-header">
                <h2>تبلیغات</h2>
            </div>
            <div class="card-body">
                <form method="POST" class="card needs-validation" enctype="multipart/form-data" novalidate>
                    <div class="card-header">
                        <h3>ایجاد تبلیغات</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">عنوان:</label>
                                    <input type="text" id="title" class="form-control" name="title" maxlength="30"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="photo"> تصویر (16/9):</label>
                                            <input type="file" accept="image/*" name="photo" onchange="showPhoto(event)"
                                                class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>پیش نمایش:</label><br>
                                            <img src="assets/img/logo.png" height="150"
                                                class="photo border border-secondary">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" type="submit" name="add">ثبت کردن</button>
                    </div>
                </form>
                <hr>
                <div class="card">
                    <div class="card-header">
                        <h3>تبلیغات</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th>عنوان</th>
                                        <th>تصویر</th>
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
                                                <td><img onclick="editPhoto(this.src,<?= $row['id'] ?>)"
                                                        src="uploads/ads/<?= $row["photo"] ?>" class="img-thumbnail"
                                                        width="100">
                                                </td>
                                                <td><?= $db->convertFullDate($row["created"], $setting["date_type"]) ?></td>
                                                <td class="text-center p-0 no-print">
                                                    <div class="btn-group" dir="ltr">
                                                        <button class="btn btn-danger btn-sm pb-0 pt-2"
                                                            onclick="showQ('<?= $row['id'] ?>')"><span
                                                                class="ico h6">delete</span></button>
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
        </div>
        <!-- edit modal -->
        <div id="edit-modal" class="modal fade" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <form method="POST" class="modal-content needs-validation" novalidate>
                    <div class="modal-header">
                        <h2>ویرایش تبلیغات</h2>
                        <button class="btn btn-danger" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="ad_id" name="ad_id">
                        <div class="form-group">
                            <label for="title"> عنوان:</label>
                            <input type="text" id="title" name="title" class="form-control" maxlength="30" required>
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

        <!-- edit photo modal -->
        <div id="edit-photo-modal" class="modal fade" data-backdrop="static">
            <div class="modal-dialog">
                <form method="POST" class="modal-content needs-validation" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h2>ویرایش تصویر</h2>
                        <button class="btn btn-danger" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="ad_id" name="ad_id">
                        <div class="form-group">
                            <label for="photo">انتخاب تصویر:</label>
                            <input type="file" name="photo" onchange="showNewPhoto(event)" accept="image/*"
                                class="form-control">
                        </div>
                        <hr>
                        <img src="" class="img-thumbnail photo">
                    </div>
                    <div class="modal-footer justify-content-start">
                        <button class="btn btn-primary" type="submit" name="update-photo">ذخیره تغییرات</button>
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
                delQ("ad_id=" + id)
            }

            // for update
            function getInfo(id) {
                $("#edit-modal #ad_id").val(id);
                $.ajax({
                    type: "get",
                    url: "ajax/get_info",
                    data: {
                        ad_id: id
                    },
                    success: function (response) {
                        var res = JSON.parse(response);
                        $("#edit-modal #title").val(res["title"]);
                        $("#edit-modal").modal('show');
                    }
                });
            }

            function showPhoto(e) {
                if (e.target.files.length > 0) {
                    var src = URL.createObjectURL(e.target.files[0]);
                    $("form .photo").attr("src", src)
                }
            }

            function editPhoto(src, id) {
                $("#edit-photo-modal").modal('show');
                $("#edit-photo-modal #ad_id").val(id);
                $("#edit-photo-modal .modal-footer").hide();
                $("#edit-photo-modal .photo").attr("src", src)
            }

            function showNewPhoto(e) {
                if (e.target.files.length > 0) {
                    var src = URL.createObjectURL(e.target.files[0]);
                    $("#edit-photo-modal .photo").attr("src", src)
                    $("#edit-photo-modal .modal-footer").slideDown();
                }
            }
        </script>
</body>

</html>