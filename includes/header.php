<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
if ($setting["theme"] == "light") {
?>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body {
            background: #f8f9fa !important;
        }
    </style>
<?php } else { ?>
    <link rel="stylesheet" href="assets/css/bootstrap-dark.min.css">
    <style>
        body {
            background: #343a40 !important;
        }
    </style>
<?php } ?>
<link rel="stylesheet" href="assets/datatable/dataTables.bootstrap4.min.css">
<!-- <link rel="stylesheet" href="assets/datatable/buttons.bootstrap4.min.css"> -->
<!--<link rel="stylesheet" href="assets/css/persianDatepicker.min.css">-->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="assets/css/sweetalert2.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="shortcut icon" href="assets/img/logo.png" type="image/x-icon">
<?php
if (isset($_SESSION["user_type"]) && $_SESSION["user_type"] == "user") { ?>
    <style>
        .container-fluid {
            padding: 15px !important;
        }
    </style>
<?php } ?>