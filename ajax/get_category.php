<?php
require_once "../includes/conn.php";

if (isset($_GET["category_id"])) {
    $sql = $db->query("SELECT * FROM sub_category WHERE up_category=" . $_GET["category_id"]);
    if ($sql->num_rows > 0) {
        $row = $sql->fetch_assoc();
        do { ?>
<option value="<?= $row["id"] ?>"><?= $row["name"] ?></option>
<?php } while ($row = $sql->fetch_assoc());
    } else { ?>
<option disabled selected>هنوز ثبت نشده</option>
<?php }
}