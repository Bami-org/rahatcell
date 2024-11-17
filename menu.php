<?php
if ((isset($_SESSION["username"]) && isset($_SESSION["password"]))) {
    if ($_SESSION["user_type"] === "user") {
        $db->route("orders");
    }
} else {
    $db->route("index?login=false");
}
?>
<style>
.search-bar .btn,
.search-bar .form-control {
    height: 34px !important;
}
</style>
<nav class="top-nav no-print">
    <div class="d-flex align-items-center">
        <span class="ico ml-3" id="menu-btn">menu</span>
        <h5 class="d-none d-md-block font-weight-bold font-bt">
            RAHAT CELL
        </h5>
    </div>
    <span class="text-white h6 mt-2 dt">امروز:
        <?= jdate('l j p o') ?> و <?= date("Y - M - d") ?>
    </span>
    <div class="d-flex align-items-center">

        <p id="time" class="text-light text-center mt-3 d-none d-sm-block" style="width: 150px;"></p>
        <span class="ico screen-btn p-1 h4 mt-2 ml-2" data-toggle="tooltip" title="سایز بزرگ"
            style="cursor: pointer;">fullscreen</span>
        <a href="javascript:void(0)" class="logout-btn text-dark bg-white logout-btn" data-toggle="tooltip"
            title="خروج از سیستم"><span class="ico">logout</span></a>
    </div>
</nav>
<div class="side-bar no-print">
    <div class="user-profile border-bottom border-secondary">
        <a href="dashboard" class="d-flex align-items-center justify-content-center" data-toggle="tooltip"
            title="دیدن پروفایل">
            <img src="assets/img/logo.png">
            <span class="mr-3">
                راحت سل
            </span>
        </a>
    </div>
    <li><a href="dashboard"><span class="ico">dashboard</span> داشبورد</a></li>
    <li><a href="currency"><span class="ico">paid</span> ارز ها</a></li>
    <li class="drop"><a href="javascript:void(0)" class="drop1"><span class="ico">group</span>مشتریان <span
                class="ico arr arr1">chevron_right</span></a>
        <ul class="sub sub1">
            <li><a href="add_customer"><span class="ico">arrow_back</span> ثبت مشتریان</a></li>
            <li><a href="customer"><span class="ico">arrow_back</span> لیست مشتریان</a></li>
        </ul>
    </li>
    <li><a href="balance"><span class="ico">account_balance</span> بیلانس</a></li>
    <li><a href="payment"><span class="ico">payment</span> پرداخت ها</a></li>
    <li class="drop"><a href="javascript:void(0)" class="drop2"><span class="ico">category</span>دسته بندی ها <span
                class="ico arr arr2">chevron_right</span></a>
        <ul class="sub sub2">
            <li><a href="category"><span class="ico">arrow_back</span> دسته بندی</a></li>
            <li><a href="sub_category"><span class="ico">arrow_back</span> زیر دسته</a></li>
        </ul>
    </li>
    <li><a href="units"><span class="ico">list</span> یونیت ها</a></li>
    <li class="drop"><a href="javascript:void(0)" class="drop4"><span class="ico">shop</span>محصولات <span
                class="ico arr arr4">chevron_right</span></a>
        <ul class="sub sub4">
            <li><a href="add_product"><span class="ico">arrow_back</span> ثبت محصولات</a></li>
            <li><a href="product"><span class="ico">arrow_back</span> لیست محصولات</a></li>
            <li><a href="assign_packages"><span class="ico">arrow_back</span> اتصال محصولات</a></li>
        </ul>
    </li>
    <li><a href="orders"><span class="ico">list_alt</span> سفارشات</a></li>
    <!-- <li class="drop"><a href="javascript:void(0)" class="drop3"><span class="ico">list_alt</span> سفارشات<span class="ico arr arr3">chevron_right</span></a>
        <ul class="sub sub3">
            <li><a href="orders"><span class="ico">arrow_back</span>همه</a></li>
            <li><a href="orders?type=Pending"><span class="ico">schedule</span> در انتظار</a></li>
            <li><a href="orders?type=Success"><span class="ico">check_circle</span> اجرا شده</a></li>
            <li><a href="orders?type=Rejected"><span class="ico">highlight_off</span> رد شده</a></li>
        </ul>
    </li> -->
    <li><a href="transaction"><span class="ico">swap_vert</span> تراکنش ها</a></li>
    <!-- <li><a href="financial_manage"><span class="ico">bar_chart</span>مدیریت مالی</a></li> -->
    <li><a href="bank"><span class="ico">house</span>معلومات بانک</a></li>
    <li><a href="reports"><span class="ico">bar_chart</span>گزارشات</a></li>
     <li class="drop"><a href="javascript:void(0)" class="drop3"><span class="ico">settings</span> تنظیمات<span
                class="ico arr arr3">chevron_right</span></a>
        <ul class="sub sub3">
            <li><a href="setting"><span class="ico">arrow_back</span> تنظیمات سیستم</a></li>
            <li><a href="ads"><span class="ico">arrow_back</span> تبلیغات</a></li>
            <li><a href="announcement"><span class="ico">notifications</span>اطلاعیه ها</a></li>
        </ul>
    </li>
    <li><a href="bank"><span class="ico">house</span>معلومات بانک</a></li>
</div>
<div class="modal fade logout-modal" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2>خروج از سیستم</h2>
            </div>
            <div class="modal-body d-flex justify-content-between align-items-center px-4">
                <p>آیا میخواهید از سیستم خارج شوید؟</p>
                <img src="assets/img/logout.png" class="img-thumbnail rounded-circle" width="100">
            </div>
            <div class="modal-footer justify-content-start">
                <a href="logout?logout=yes" class="btn btn-danger">بله</a>
                <button class="btn btn-success" data-dismiss="modal">نخیر</button>
            </div>
        </div>
    </div>
</div>

<!-- <div class="splash bg-white">
    <div class="spinner-border text-dark" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div> -->

<script>
setInterval(() => {
    var d = new Date();
    var h = d.getHours();
    var m = d.getMinutes();
    var s = d.getSeconds();
    var am = "قبل از ظهر";
    if (h > 12) {
        h = h - 12;
        am = "بعد از ظهر";
    } else {
        am = "قبل از ظهر";
    }
    if (m < 10)
        m = "0" + m
    if (s < 10)
        s = "0" + s
    document.getElementById("time").innerHTML = h + ":" + m + ":" + s + " " + `<small>${am}</small>`
}, 1000);
</script>