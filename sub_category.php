<?php require_once "includes/conn.php";
if (isset($_POST["add"])) {
    if (!isset($_POST["up_category"])) {
        $_SESSION["ct_err"] = "لطفا دسته بندی را انتخاب کنید!";
    } else {
        unset($_SESSION["ct_err"]);
        if (isset($_FILES["photo"])) {
            $photo = $_FILES["photo"]["name"];
            move_uploaded_file($_FILES["photo"]["tmp_name"], "uploads/category/" . $photo);
            $sql = $db->insert("sub_category", [
                "name" => $db->clean_input($_POST["name"]),
                "up_category" => $db->clean_input($_POST["up_category"]),
                "photo" => $photo
            ]);
            if ($sql) {
                $db->route("sub_category?opr=success");
            } else {
                $db->show_err();
            }
        } else {
            $sql = $db->insert("sub_category", [
                "name" => $db->clean_input($_POST["name"]),
                "up_category" => $db->clean_input($_POST["up_category"])
            ]);
            if ($sql) {
                $db->route("sub_category?opr=success");
            } else {
                $db->show_err();
            }
        }
    }
}


// change product photo
if (isset($_POST["change-photo"])) {
    $photo = $_FILES["category-photo"]["name"];
    unlink($_POST["old-photo"]);
    move_uploaded_file($_FILES["category-photo"]["tmp_name"], "uploads/category/" . $photo);
    if ($db->update("sub_category", ["photo" => $photo], "id=" . $db->clean_input($_POST["productId"]))) {
        $db->route("sub_category?opr=uploadPhoto");
    } else {
        $db->show_err();
    }
}

if (isset($_POST["update"])) {
    $sql = $db->update(
        "sub_category",
        [
            "name" => $db->clean_input($_POST["name"]),
            "up_category" => $db->clean_input($_POST["up_category"]),
            "updated" => date("Y-m-d h:i:s")
        ],
        "id=" . $db->clean_input($_POST["sub_category_id"])
    );
    if ($sql) {
        $db->route("sub_category?opr=success");
    } else {
        $db->show_err();
    }
}

$sql = $db->query("SELECT sub_category.*, category.name as c_name FROM sub_category LEFT JOIN category ON sub_category.up_category = category.id ORDER BY sub_category.id DESC");
$row = $sql->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>

    <title>دسته بندی ها</title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li><span
                    class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item"><a href="category">دسته بندی ها</a></li><span
                    class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item">زیر دسته ها</li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <form method="post" class="card needs-validation" enctype="multipart/form-data" novalidate>
            <div class="card-header">
                <h2>ایجاد زیر دسته</h2>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION["ct_err"])) { ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <?= $_SESSION["ct_err"] ?>
                    <button class="close" data-dismiss="alert">&times;</button>
                </div>
                <?php } ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="up_category"> انتخاب دسته بندی:</label>
                            <select type="text" id="up_category" name="up_category" class="form-control">
                                <option selected disabled>انتخاب</option>
                                <?php
                                $c_sql = $db->query("SELECT id,name FROM category");
                                if ($c_sql->num_rows > 0) {
                                    $c_row = $c_sql->fetch_assoc();
                                    do { ?>
                                <option value="<?= $c_row["id"] ?>"><?= $c_row["name"] ?></option>
                                <?php } while ($c_row = $c_sql->fetch_assoc());
                                } else { ?>
                                <option selected disabled>هنوز ثبت نشده</option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="name">اسم زیر دسته:</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-group">
                                <label for="photo">تصویر:</label>
                                <input type="file" accept="image/*" id="photo" name="photo" class="form-control"
                                    onchange="setPhoto(event)">
                            </div>
                            <img src="assets/img/logo.png" class="img-thumbnail mr-2 photo" width="60" height="60">
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
                <h3>دسته بندی ها</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th>دسته بندی</th>
                                <th>زیر دسته</th>
                                <th>تصویر</th>
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
                                <td><?= $row["c_name"] ?></td>
                                <td><?= $row["name"] ?></td>
                                <td><img src="uploads/category/<?= $row["photo"] == null ? "product.png" : $row["photo"] ?>"
                                        data-toggle="tooltip" title="کلیک برای ویرایش"
                                        onclick="photoPreview(this.src,'uploads/category/<?= $row['photo'] ?>','<?= $row['id'] ?>')"
                                        class="img-thumbnail" width="50">
                                </td>
                                <td><?= $db->convertFullDate($row["created"],$setting["date_type"]) ?></td>
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
            <div class="card-footer">
            </div>
        </div>
    </div>


    <!-- edit photo modal -->
    <div id="photo-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <form method="post" class="modal-content" enctype="multipart/form-data">
                <div class="modal-header">
                    <h3>ویرایش عکس</h3>
                    <button class="btn btn-danger" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body text-center">
                    <img src="" class="product-img img-thumbnail">
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="productId" id="productId">
                    <div class="row w-100">
                        <div class="col-8 justify-content-end pr-0">
                            <input type="file" class="form-control" name="category-photo" onchange="setNewPhoto(event)">
                            <input type="hidden" name="old-photo" id="old-photo">
                        </div>
                        <div class="col-4 upload-btn" hidden>
                            <button class="btn btn-success" type="submit" name="change-photo">ذخیره
                                تغییرات</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- // edit photo modal -->

    <!-- edit modal -->
    <div id="edit-modal" class="modal fade" data-backdrop="static">
        <div class="modal-dialog">
            <form method="POST" class="modal-content needs-validation" novalidate>
                <div class="modal-header">
                    <h2>ویرایش دسته بندی</h2>
                    <button class="btn btn-danger" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="sub_category_id" name="sub_category_id">
                    <div class="form-group">
                        <label for="up_category">دسته بندی:</label>
                        <select type="text" id="up_category" name="up_category" class="form-control">
                            <option selected disabled>انتخاب</option>
                            <?php
                            $c_sql = $db->query("SELECT id,name FROM category");
                            if ($c_sql->num_rows > 0) {
                                $c_row = $c_sql->fetch_assoc();
                                do { ?>
                            <option value="<?= $c_row["id"] ?>"><?= $c_row["name"] ?></option>
                            <?php } while ($c_row = $c_sql->fetch_assoc());
                            } else { ?>
                            <option selected disabled>هنوز ثبت نشده</option>
                            <?php } ?>
                        </select>
                    </div>
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
        delQ("sub_category_id=" + id)
    }

    // for update
    function getInfo(id) {
        $("#edit-modal #sub_category_id").val(id);
        $.ajax({
            type: "get",
            url: "ajax/get_info",
            data: {
                sub_category_id: id
            },
            success: function(response) {
                var res = JSON.parse(response);
                $("#edit-modal #up_category").val(res["up_category"]);
                $("#edit-modal #name").val(res["name"]);
                $("#edit-modal").modal('show');
            }
        });
    }

    function setPhoto(e) {
        if (e.target.files.length > 0) {
            var src = URL.createObjectURL(e.target.files[0]);
            $("form .photo").attr("src", src);
        }
    }

    function photoPreview(img, oldImg, rowId) {
        $("#photo-modal .product-img").attr("src", img);
        $("#photo-modal .upload-btn").attr("hidden", "hidden");
        $("#photo-modal #old-photo").val(oldImg);
        $("#photo-modal #productId").val(rowId);
        $("#photo-modal").modal('show');
    }

    function setNewPhoto(e) {
        if (e.target.files.length > 0) {
            var src = URL.createObjectURL(e.target.files[0]);
            $("#photo-modal .product-img").attr("src", src);
            $("#photo-modal .upload-btn").removeAttr("hidden")
        }
    }
    </script>
</body>

</html>