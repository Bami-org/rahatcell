<?php require_once "includes/conn.php";
if (isset($_POST["add"])) {
    if (!(isset($_POST["unit_id"]) && isset($_POST["category_id"]) && isset($_POST["sub_category_id"]))) {
        $_SESSION["err_msg"] = "لطفا یونیت، دسته بندی و زیر دسته را انتخاب کنید!";
    } else {
        unset($_SESSION["err_msg"]);
        $sql = $db->insert(
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
            ]
        );
        if ($sql) {
            $db->route("add_product?opr=success");
        } else {
            $db->show_err();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>ثبت محصولات</title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li><span class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item"><a href="product">محصولات</a></li><span class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item">ثبت</li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <form method="post" class="card needs-validation" enctype="multipart/form-data" novalidate>
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2>ثبت محصول</h2>
                <a href="product" class="btn btn-info bt-ico">لیست <span class="ico">list_alt</span></a>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION["err_msg"])) { ?>
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <?= $_SESSION["err_msg"] ?>
                        <button class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php } ?>
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
                            <select id="category_id" name="category_id" class="form-control" onchange="getSubCategory(this.value)">
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
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="dollar_buy_price"> خرید به دالر:</label>
                            <input type="text" id="dollar_buy_price" name="dollar_buy_price" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="dollar_sale_price"> فروش به دالر:</label>
                            <input type="text" id="dollar_sale_price" name="dollar_sale_price" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="toman_buy_price"> خرید به تومن:</label>
                            <input type="text" id="toman_buy_price" name="toman_buy_price" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="toman_sale_price"> فروش به تومن:</label>
                            <input type="text" id="toman_sale_price" name="toman_sale_price" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="lyra_buy_price"> خرید به لیر:</label>
                            <input type="text" id="lyra_buy_price" name="lyra_buy_price" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="lyra_sale_price"> فروش به لیر:</label>
                            <input type="text" id="lyra_sale_price" name="lyra_sale_price" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="euro_buy_price"> خرید به یورو:</label>
                            <input type="text" id="euro_buy_price" name="euro_buy_price" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="euro_sale_price"> فروش به یورو:</label>
                            <input type="text" id="euro_sale_price" name="euro_sale_price" class="form-control" value="0">
                        </div>
                    </div>
                      <div class="col-md-2">
                        <div class="form-group">
                            <label for="afghani_buy_price"> خرید به افغانی:</label>
                            <input type="text" id="afghani_buy_price" name="afghani_buy_price" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="afghani_sale_price"> فروش به افغانی:</label>
                            <input type="text" id="afghani_sale_price" name="afghani_sale_price" class="form-control" value="0">
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
            <div class="card-footer">
                <button type="submit" name="add" class="btn btn-primary">ثبت کردن</button>
                <button type="reset" class="btn btn-danger">انصراف</button>
            </div>
        </form>
    </div>
    <?php require_once "includes/footer.php" ?>

    <script>
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