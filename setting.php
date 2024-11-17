<?php require_once "includes/conn.php";

if (isset($_POST["update_server"])) {
    $server = $db->clean_input($_POST["server"]);
    $sql = $db->update("api_server_settings", [
        "setting_value" => $server
    ], "id=1");

    if ($sql) {
        unset($_SESSION["msg"]);
        $db->route("setting?opr=success");
    } else {
        $db->show_err();
    }
}

// Fetch the current server setting
$server_setting_sql = $db->query("SELECT setting_value FROM api_server_settings WHERE id = 1");
if ($server_setting_sql->num_rows > 0) {
    $server_setting_row = $server_setting_sql->fetch_assoc();
    $current_server = $server_setting_row["setting_value"];
} else {
    $current_server = 'internal'; // Default value
}

if (isset($_POST["update_api_credentials"])) {
    // Update existing credentials
    if (isset($_POST['credentials'])) {
        foreach ($_POST['credentials'] as $credential) {
            $id = $db->clean_input($credential['id']);
            $dealer_code = $db->clean_input($credential['dealer_code']);
            $username = $db->clean_input($credential['username']);
            $password = $db->clean_input($credential['password']);
            $base_url = $db->clean_input($credential['base_url']);
            
            $sql = $db->update("api_credentials", [
                "dealer_code" => $dealer_code,
                "username" => $username,
                "password" => $password,
                "base_url" => $base_url
            ], "id=$id");

            if (!$sql) {
                $db->show_err();
                break;
            }
        }
    }

    // Insert new credentials
    if (isset($_POST['new_credentials'])) {
        foreach ($_POST['new_credentials'] as $credential) {
            $dealer_code = $db->clean_input($credential['dealer_code']);
            $username = $db->clean_input($credential['username']);
            $password = $db->clean_input($credential['password']);
            $base_url = $db->clean_input($credential['base_url']);
            
            $sql = $db->insert("api_credentials", [
                "dealer_code" => $dealer_code,
                "username" => $username,
                "password" => $password,
                "base_url" => $base_url
            ]);

            if (!$sql) {
                $db->show_err();
                break;
            }
        }
    }

    $db->route("setting?opr=success");
}

$api_credentials_sql = $db->query("SELECT * FROM api_credentials");

if ($api_credentials_sql->num_rows > 0) {
    $api_credentials_rows = $api_credentials_sql->fetch_all(MYSQLI_ASSOC);
} else {
    $api_credentials_rows = [[
        "dealer_code" => "",
        "username" => "",
        "password" => ""
    ]];
}

if (isset($_POST["update_admin"])) {
    $username = $db->clean_input($_POST["username"]);
    $old_password = $db->clean_input($_POST["old_password"]);
    $new_password = $db->clean_input($_POST["new_password"]);
    $check_data = $db->query("SELECT * FROM user WHERE username = '$username' AND password = '" . md5($old_password) . "'");
    if ($check_data->num_rows > 0) {
        $sql = $db->update(
            "user",
            [
                "username" => $username,
                "password" => md5($new_password),
            ],
            "id=1"
        );
        if ($sql) {
            unset($_SESSION["msg"]);
            $db->route("setting?opr=success");
        } else {
            $db->show_err();
        }
    } else {
        $_SESSION["msg"] = "نام کاربری یا گذرواژه اشتباه است!";
        $db->route("setting?not_update");
    }
}

$user_sql = $db->query("SELECT * FROM user WHERE user_type = 'user'");
if ($user_sql->num_rows > 0) {
    $user_row = $user_sql->fetch_assoc();
}

if (isset($_POST["update_user"])) {
    $sql = $db->update("user", [
        "username" => $db->clean_input($_POST["username"]),
        "password" => $db->clean_input($_POST["password"])
    ], "user_type='user'");
    if ($sql) {
        $db->route("setting?opr=success");
    } else {
        $db->show_err();
    }
}

// update support phone number
if (isset($_POST["updatePhone"])) {
    $sql = $db->update("setting", [
        "phone" => $_POST["phone"]
    ], "id=1");
    if ($sql) {
        $db->route("setting?opr=success");
    } else {
        $db->show_err();
    }
}

$phone = $db->query("SELECT phone FROM setting")->fetch_assoc()["phone"]?? "";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <title>تنظیمات</title>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li><span class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item">تنظیمات</li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <div class="row">
            <div class="col-md-4">
                <form method="post" class="card needs-validation" novalidate>
                    <div class="card-header">
                        <h3>تغیر اطلاعات ادمین</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION["msg"]) && !empty($_SESSION["msg"])) { ?>
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <?= $_SESSION["msg"] ?>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="username">نام کاربری:</label>
                            <div class="input-group" dir="ltr">
                                <input type="text" value="<?= $_SESSION["username"] ?>" name="username" id="username" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text ico">person</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="old_password">گذرواژه قبلی:</label>
                            <div class="input-group" dir="ltr">
                                <div class="input-group-prepend">
                                    <span class="input-group-text ico eye">visibility_off</span>
                                </div>
                                <input type="password" name="old_password" id="old_password" class="form-control pass" required>
                                <div class="input-group-append">
                                    <span class="input-group-text ico">lock</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new_password">گذرواژه جدید:</label>
                            <div class="input-group" dir="ltr">
                                <div class="input-group-prepend">
                                    <span class="input-group-text ico eye">visibility_off</span>
                                </div>
                                <input type="new_password" name="new_password" id="new_password" class="form-control pass" required>
                                <div class="input-group-append">
                                    <span class="input-group-text ico">lock</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" name="update_admin" class="btn btn-success">ذخیره تغییرات</button>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <form method="post" class="card needs-validation" novalidate>
                    <div class="card-header">
                        <h3>تغیر اطلاعات کاربر</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="username">نام کاربری:</label>
                            <div class="input-group" dir="ltr">
                                <input type="text" name="username" value="<?= $user_row["username"] ?>" id="username" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text ico">person</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">گذرواژه:</label>
                            <div class="input-group" dir="ltr">
                                <div class="input-group-prepend">
                                    <span class="input-group-text ico eye">visibility_off</span>
                                </div>
                                <input type="password" name="password" id="password" class="form-control pass" required>
                                <div class="input-group-append">
                                    <span class="input-group-text ico">lock</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" name="update_user" class="btn btn-success">ذخیره تغییرات</button>
                    </div>
                </form>

            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h3>گرفتن بکاپ</h3>
                    </div>
                    <div class="card-body">
                        <a href="get_backup" class="btn btn-primary bt-ico">ذخیره فایل <span class="ico">download</span>
                        </a>
                    </div>
                </div>
                <hr>
                <form method="post" class="card">
                    <div class="card-header">
                        <h4>نوع تاریخ و تم سیستم</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="date_type">نوع تاریخ:</label>
                            <select name="date_type" id="date_type" class="form-control" onchange="setDateType(this.value)">
                                <option selected disabled>انتخاب</option>
                                <option value="miladi">میلادی</option>
                                <option value="shamsi">هجری شمسی</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="theme">تم:</label>
                            <select name="theme" id="theme" class="form-control" onchange="setTheme(this.value)">
                                <option selected disabled>انتخاب</option>
                                <option value="light">روشن</option>
                                <option value="dark">تاریک</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                <form method="post" class="card needs-validation" novalidate>
                    <div class="card-header">
                        <h3>تغیر اطلاعات API</h3>
                    </div>
                    <div class="card-body" id="api-credentials-container">
                        <div class="form-group">
                            <label for="api_credential_dropdown">انتخاب کاربر API:</label>
                            <select id="api_credential_dropdown" name="selected_api_credential" class="form-control" onchange="showApiCredentialForm(this.value)">
                                <option value="" disabled selected>انتخاب</option>
                                <?php foreach ($api_credentials_rows as $index => $credential) { ?>
                                    <option value="<?= $credential['id'] ?>">
                                        <?= htmlspecialchars($credential["dealer_code"]) ?> (<?= htmlspecialchars($credential["username"]) ?>)
                                    </option>
                                <?php } ?>
                                <option value="new">اضافه کردن تنظیمات جدید API</option>
                            </select>
                        </div>
            
                        <!-- Existing API Credentials Form (hidden initially) -->
                        <?php foreach ($api_credentials_rows as $index => $credential) { ?>
                            <div id="api_credential_form_<?= $credential['id'] ?>" class="api-credential-form" style="display:none;">
                                <div class="form-group">
                                    <label for="base_url_<?= $index ?>">لینک کاربر:</label>
                                    <input type="text" value="<?= htmlspecialchars($credential["base_url"]) ?>" name="credentials[<?= $index ?>][base_url]" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="dealer_code_<?= $index ?>">کد فروشنده:</label>
                                    <input type="text" value="<?= htmlspecialchars($credential["dealer_code"]) ?>" name="credentials[<?= $index ?>][dealer_code]" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="username_<?= $index ?>">نام کاربری:</label>
                                    <input type="text" value="<?= htmlspecialchars($credential["username"]) ?>" name="credentials[<?= $index ?>][username]" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="password_<?= $index ?>">گذرواژه:</label>
                                    <input type="password" value="<?= htmlspecialchars($credential["password"]) ?>" name="credentials[<?= $index ?>][password]" class="form-control" required>
                                </div>
                                <input type="hidden" name="credentials[<?= $index ?>][id]" value="<?= $credential['id'] ?>">
                            </div>
                        <?php } ?>
            
                        <!-- Form for Adding New API Credentials -->
                        <div id="new_api_credential_form" class="api-credential-form" style="display:none;">
                            <div class="form-group">
                                <label for="new_base_url">لینک کاربر جدید:</label>
                                <input type="text" name="new_credentials[0][base_url]" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="new_dealer_code">کد فروشنده جدید:</label>
                                <input type="text" name="new_credentials[0][dealer_code]" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="new_username">نام کاربری جدید:</label>
                                <input type="text" name="new_credentials[0][username]" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">گذرواژه جدید:</label>
                                <input type="password" name="new_credentials[0][password]" class="form-control" required>
                            </div>
                        </div>
                    </div>
            
                    <div class="card-footer">
                        <button type="submit" name="update_api_credentials" class="btn btn-success">ذخیره تغییرات</button>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <form method="post" class="card needs-validation" novalidate>
                    <div class="card-header">
                        <h3>تنظیمات سرور</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION["msg"]) && !empty($_SESSION["msg"])) { ?>
                            <div class="alert alert-success alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <?= $_SESSION["msg"] ?>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="server">نوع سرور:</label>
                            <select name="server" id="server" class="form-control" required>
                                <option value="internal" <?= ($current_server === 'internal') ? 'selected' : '' ?>>سرور داخلی</option>
                                <option value="external" <?= ($current_server === 'external') ? 'selected' : '' ?>>سرور API</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" name="update_server" class="btn btn-success">ذخیره تغییرات</button>
                    </div>
                </form>
            </div>
            <div class="col-md-3">
                 <form method="post" class="card needs-validation" novalidate>
                    <div class="card-header">
                        <h3>پشتیبانی برنامه</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="phone">شماره پشتیبانی:</label>
                            <input type="text" dir="ltr" class="form-control text-center" name="phone" value="<?= $phone ?>" placeholder="شماره را و ارد کنید" required>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" type="submit" name="updatePhone">ثبت کردن</button>
                    </div>
                </form>
            </div>
            </div>
            
        </div>
        <?php require_once "includes/footer.php" ?>

        <script>
            function setDateType(type) {
                $.ajax({
                    type: "POST",
                    url: "ajax/set_date_type",
                    data: {
                        date_type: type
                    },
                    success: function(response) {
                        location.href = "setting?opr=success";
                    }
                });
            }

            function setTheme(theme) {
                $.ajax({
                    type: "POST",
                    url: "ajax/set_theme",
                    data: {
                        theme: theme
                    },
                    success: function(response) {
                        location.href = "setting?opr=success";
                    }
                });
            }
        </script>
        <script>
            function showApiCredentialForm(value) {
                // Hide all credential forms
                document.querySelectorAll('.api-credential-form').forEach(form => form.style.display = 'none');
        
                if (value === 'new') {
                    document.getElementById('new_api_credential_form').style.display = 'block';
                } else {
                    document.getElementById('api_credential_form_' + value).style.display = 'block';
                }
            }
        </script>

</body>

</html>