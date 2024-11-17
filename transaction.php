<?php require_once "includes/conn.php" ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "includes/header.php" ?>
    <link rel="stylesheet" href="assets/datatable/buttons.bootstrap4.min.css">
    <title>تراکنش ها</title>
    <style>
    table tr td:last-child {
        padding: 0 !important;
        margin: 0 !important;
    }
    </style>
</head>

<body>
    <?php require_once "menu.php" ?>
    <div class="container-fluid">
        <!-- start of breadcrumb -->
        <div class="breadcrumb pb-0">
            <ul class="list-inline">
                <li class="mx-0 list-inline-item"><a href="dashboard">داشبورد</a></li><span
                    class="pr-1 text-secondary">/</span>
                <li class="mx-0 list-inline-item">تراکنش ها</li>
            </ul>
        </div>
        <!-- // end of breadcrumb -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2>تراکنش ها</h2>
                <?= isset($_GET["customer_id"]) ? '<button class="btn btn-info bt-ico" onclick="history.back()">برگشت <span class="ico">arrow_back</span></button>' : '' ?>
                <h2 id="sum">0</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover text-center order-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>مشتری</th>
                                <th>مقدار</th>
                                <th>ارز</th>
                                <th>نوع</th>
                                <th>کتگوری</th>
                                <th>توضیحات</th>
                                <th style="width: 15%;">تاریخ</th>
                                <th style="width: 5%;">جمع</th>
                                <th style="width: 5%;">عملکرد</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    <?php require_once "includes/footer.php" ?>
    <script src="assets/datatable/dataTables.buttons.min.js"></script>
    <script src="assets/datatable/buttons.bootstrap4.min.js"></script>
    <script src="assets/datatable/buttons.html5.min.js"></script>
    <!-- <script src="assets/datatable/buttons.print.min.js"></script> -->
    <script src="assets/datatable/jszip.min.js"></script>

    <script>
    // for delete
    function showQ(id) {
        delQ("transaction_id=" + id)
    }


    $(document).ready(function() {
        $(".order-table").DataTable({
            destroy: true,
            dom: 'Bftrip',
            buttons: [{
                extend: 'excel',
                text: `<div class="d-flex align-items-center">فایل اکسیل <span class="ico m-0 p-0 mr-2 h5">download</span></div>`,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            }],
            pageLength: 6,
            "language": {
                "loadingRecords": `<div class="py-1 d-flex align-items-center justify-content-center">درحال بارگذاری <div class="spinner-border spinner-border-sm mr-2 text-dark" role="status"></div></div>`,
                "sLengthMenu": "نمایش _MENU_ سفارش",
                "sZeroRecords": "هنوز ثبت نشده",
                "sInfoEmpty": "نمایش 0 از 0 تراکنش",
                "sSearch": "جستجو: ",
                "sInfo": "نمایش _START_ تا _END_ از _TOTAL_ تراکنش",
                "infoFiltered": "از _MAX_",
                "oPaginate": {
                    "sPrevious": "قبلی",
                    "sNext": "بعدی"
                }
            },
            "ajax": {
                "url": "ajax/get_transactions",
                "dataSrc": ""
            },
            "columns": [{
                    "data": "num"
                },
                {
                    "data": "name"
                },
                {
                    "data": "amount"
                },
                {
                    "data": "currency"
                },
                {
                    "data": "tr_type"
                },
                {
                    "data": "category"
                },
                {
                    "data": "description"
                },
                {
                    "data": "created"
                },
                {
                    "data": "check"
                },
                {
                    "data": "id",
                    "render": function(data, type, row) {
                        return '<div class="btn-group p-0 m-0" dir="ltr"><button class="btn btn-danger pb-0 pt-2 m-0 edit-btn btn-sm" onclick="showQ(' +
                            row
                            .id +
                            ')"  type="button"><span class="ico h6">delete</span></button>'
                    }
                }
            ]
        });
        $(".dt-buttons").removeClass("btn-group");
        $(".dt-buttons .buttons-excel").removeClass("btn-secondary").addClass("btn-success");
    });
    
      var sum = 0;

    function getSum(check) {
        if (check.checked) {
            sum = sum + parseFloat(check.value);
            $("#sum").html(sum).show();
        } else {
            sum = sum - parseFloat(check.value);
            $("#sum").html(sum);
        }
    }
    </script>
</body>

</html>