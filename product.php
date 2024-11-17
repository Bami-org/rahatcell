<?php require_once "includes/conn.php";

$sql = $db->query("SELECT product.*,units.name as unit, category.name as category,
sub_category.name as sub_category FROM product
LEFT JOIN units ON product.unit_id = units.id
LEFT JOIN category ON product.category_id = category.id
LEFT JOIN sub_category ON product.sub_category_id = sub_category.id");
$row = $sql->fetch_assoc();


if (isset($_POST["update"])) {
    $update_sql = $db->update(
        "product",
        [
            "amount" => $db->clean_input($_POST["amount"]),
            "unit_id" => $db->clean_input($_POST["unit_id"]),
            "category_id" => $db->clean_input($_POST["category_id"]),
            "sub_category_id" => $db->clean_input($_POST["sub_category_id"]),
            "dollar_buy_price" => $db->clean_input($_POST["dollar_buy_price"]),
            "dollar_sale_price" => $db->clean_input($_POST["dollar_sale_price"]),
            "toman_buy_price" => $db->clean_input($_POST["toman_buy_price"]),
            "toman_sale_price" => $db->clean_input($_POST["toman_sale_price"]),
            "lyra_buy_price" => $db->clean_input($_POST["lyra_buy_price"]),
            "lyra_sale_price" => $db->clean_input($_POST["lyra_sale_price"]),
            "euro_buy_price" => $db->clean_input($_POST["euro_buy_price"]),
            "euro_sale_price" => $db->clean_input($_POST["euro_sale_price"]),
            "afghani_buy_price" => $db->clean_input($_POST["afghani_buy_price"]),
            "afghani_sale_price" => $db->clean_input($_POST["afghani_sale_price"]),
            "description" => $db->clean_input($_POST["description"]),
        ],
        "id=" . $db->clean_input($_POST["product_id"])
    );
    if ($update_sql) {
        $db->route("product?opr=success");
    } else {
        $db->show_err();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>محصولات</title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li><span
                    class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item"><a href="product">محصولات</a></li><span
                    class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item">لیست</li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2>لیست محصولات</h2>
                <a href="add_product" class="btn btn-primary bt-ico">جدید <span class="ico">add</span></a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm text-center">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th>مقدار</th>
                                <th>یونیت</th>
                                <th>دسته بندی</th>
                                <th>زیر دسته</th>
                                <th>خرید دالر</th>
                                <th>فروش دالر</th>
                                <th>خرید تومن</th>
                                <th>فروش تومن</th>
                                <th>خرید لیر</th>
                                <th>فروش لیر</th>
                                <th>خرید یورو</th>
                                <th>فروش یورو</th>
                                 <th>خرید افغانی</th>
                                <th>فروش افغانی</th>
                                <th>توضیحات</th>
                                <th>تاریخ</th>
                                <th style="width: 6%;">عملکرد</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($sql->num_rows > 0) {
                                $n = 1;
                                do { ?>
                            <tr>
                                <td><?= $n++ ?></td>
                                <td><?= $row["amount"] ?></td>
                                <td><?= $row["unit"] ?></td>
                                <td><?= $row["category"] ?></td>
                                <td><?= $row["sub_category"] ?></td>
                                <td><?= number_format($row["dollar_buy_price"]??0) ?></td>
                                <td><?= number_format($row["dollar_sale_price"]??0) ?></td>
                                <td><?= number_format($row["toman_buy_price"]??0) ?></td>
                                <td><?= number_format($row["toman_sale_price"]??0) ?></td>
                                <td><?= number_format($row["lyra_buy_price"]??0) ?></td>
                                <td><?= number_format($row["lyra_sale_price"]??0) ?></td>
                                <td><?= $row["euro_buy_price"] ?></td>
                                <td><?= $row["euro_sale_price"] ?></td>
                                <td><?= number_format($row["afghani_buy_price"]??0) ?></td>
                                <td><?= number_format($row["afghani_sale_price"]??0) ?></td>
                                <td><?= $row["description"] ?></td>
                                <td><?= $db->convertFullDate($row["created"],$setting["date_type"]) ?></td>
                                <td class="text-center p-0 no-print">
                                    <div class="btn-group p-2 py-0" dir="ltr">
                                        <button class="btn btn-danger btn-sm pb-0 pt-2"
                                            onclick="showQ('<?= $row['id'] ?>')"><span
                                                class="ico">delete</span></button>
                                        <button class="btn btn-success btn-sm pb-0 pt-2"
                                            onclick="getInfo('<?= $row['id'] ?>')"><span
                                                class="ico">edit</span></button>
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
    <div id="edit-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="post" class="modal-content">
                <div class="modal-header">
                    <h3>ویرایش محصول</h3>
                    <button class="btn btn-danger" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="product-id">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="amount">مقدار:</label>
                                <input type="text" id="amount" name="amount" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="unit_id">یونیت:</label>
                                <select id="unit_id" name="unit_id" class="form-control">
                                    <option selected disabled>انتخاب</option>
                                    <?php
                                    $u_sql = $db->query("SELECT id,name FROM units");
                                    if ($u_sql->num_rows > 0) {
                                        $u_row = $u_sql->fetch_assoc();
                                        do { ?>
                                    <option value="<?= $u_row["id"] ?>"><?= $u_row["name"] ?></option>
                                    <?php } while ($u_row = $u_sql->fetch_assoc());
                                    } else { ?>
                                    <option selected disabled>هنوز ثبت نشده</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="category_id">دسته بندی:</label>
                                <select id="category_id" name="category_id" class="form-control"
                                    onchange="getSubCategory(this.value)">
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
                                <label for="sub_category_id">زیر دسته:</label>
                                <select id="sub_category_id" name="sub_category_id" class="form-control">
                                    <option selected disabled>انتخاب</option>
                                    <?php
                                    $s_sql = $db->query("SELECT id,name FROM sub_category");
                                    if ($s_sql->num_rows > 0) {
                                        $s_row = $s_sql->fetch_assoc();
                                        do { ?>
                                    <option value="<?= $s_row["id"] ?>"><?= $s_row["name"] ?></option>
                                    <?php } while ($s_row = $s_sql->fetch_assoc());
                                    } else { ?>
                                    <option selected disabled>هنوز ثبت نشده</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="dollar_buy_price"> خرید به دالر:</label>
                                <input type="text" id="dollar_buy_price" name="dollar_buy_price" class="form-control"
                                    value="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="dollar_sale_price"> فروش به دالر:</label>
                                <input type="text" id="dollar_sale_price" name="dollar_sale_price" class="form-control"
                                    value="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="toman_buy_price"> خرید به تومن:</label>
                                <input type="text" id="toman_buy_price" name="toman_buy_price" class="form-control"
                                    value="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="toman_sale_price"> فروش به تومن:</label>
                                <input type="text" id="toman_sale_price" name="toman_sale_price" class="form-control"
                                    value="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="lyra_buy_price"> خرید به لیر:</label>
                                <input type="text" id="lyra_buy_price" name="lyra_buy_price" class="form-control"
                                    value="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="lyra_sale_price"> فروش به لیر:</label>
                                <input type="text" id="lyra_sale_price" name="lyra_sale_price" class="form-control"
                                    value="0">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="euro_buy_price"> خرید به یورو:</label>
                                <input type="text" id="euro_buy_price" name="euro_buy_price" class="form-control"
                                    value="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="euro_sale_price"> فروش به یورو:</label>
                                <input type="text" id="euro_sale_price" name="euro_sale_price" class="form-control"
                                    value="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="afghani_buy_price"> خرید به افغانی:</label>
                                <input type="text" id="afghani_buy_price" name="afghani_buy_price" class="form-control"
                                    value="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="afghani_sale_price"> فروش به افغانی:</label>
                                <input type="text" id="afghani_sale_price" name="afghani_sale_price" class="form-control"
                                    value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="description">توضیحات:</label>
                                <input type="text" id="description" name="description" class="form-control">
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button class="btn btn-success" type="submit" name="update">ذخیره
                        تغییرات</button>
                    <button class="btn btn-warning" data-dismiss="modal">
                        انصراف</button>
                </div>
            </form>
        </div>
    </div>
    <!-- // edit modal -->



    <?php require_once "includes/footer.php" ?>

    <script>
    // for delete
    function showQ(id) {
        delQ("product_id=" + id)
    }

    // for update
    function getInfo(id) {
        $("#edit-modal #product-id").val(id);
        $.ajax({
            type: "get",
            url: "ajax/get_info",
            data: {
                product_id: id
            },
            success: function(response) {
                var res = JSON.parse(response);
                $("#edit-modal #amount").val(res["amount"]);
                $("#edit-modal #unit_id").val(res["unit_id"]);
                $("#edit-modal #category_id").val(res["category_id"]);
                $("#edit-modal #sub_category_id").val(res["sub_category_id"]);
                $("#edit-modal #dollar_buy_price").val(res["dollar_buy_price"]);
                $("#edit-modal #dollar_sale_price").val(res["dollar_sale_price"]);
                $("#edit-modal #toman_buy_price").val(res["toman_buy_price"]);
                $("#edit-modal #toman_sale_price").val(res["toman_sale_price"]);
                $("#edit-modal #lyra_buy_price").val(res["lyra_buy_price"]);
                $("#edit-modal #lyra_sale_price").val(res["lyra_sale_price"]);
                $("#edit-modal #euro_buy_price").val(res["euro_buy_price"]);
                $("#edit-modal #euro_sale_price").val(res["euro_sale_price"]);
                $("#edit-modal #afghani_buy_price").val(res["afghani_buy_price"]);
                $("#edit-modal #afghani_sale_price").val(res["afghani_sale_price"]);
                $("#edit-modal #description").val(res["description"]);
                $("#edit-modal").modal('show');
            }
        });
    }

    function getSubCategory(id) {
        $.ajax({
            type: "get",
            url: "ajax/get_category",
            data: {
                category_id: id
            },
            success: function(response) {
                $("form #sub_category_id").html(response);
            }
        });
    }
    </script>
</body>

</html>