$(window).on("load", function() {
    setTimeout(() => {
        $(".splash").fadeOut()
    }, 50);

    var elem = document.documentElement;
    $(".screen-btn").on("click", function() {
        if ($(this).html() == "fullscreen") {
            elem.requestFullscreen();
            $(this).html("fit_screen")
        } else {
            $(this).html("fullscreen")
            document.exitFullscreen()
        }
    });
});

$("#menu-btn").click(function(e) {
    if ($(this).html() == "menu") {
        $(this).html("arrow_back");
        $(".container-fluid,#menu-btn,.side-bar,.user-profile").addClass("act")
    } else {
        $(this).html("menu");
        $(".container-fluid,#menu-btn,.side-bar,.user-profile").removeClass("act")
    }
});

$(".side-bar .drop1").click(function() {
    $(".side-bar .sub1").slideToggle();
    $(".side-bar li a .arr1").toggleClass("act")
});
$(".side-bar .drop2").click(function() {
    $(".side-bar .sub2").slideToggle();
    $(".side-bar li a .arr2").toggleClass("act")
});
$(".side-bar .drop3").click(function() {
    $(".side-bar .sub3").slideToggle();
    $(".side-bar li a .arr3").toggleClass("act")
});
$(".side-bar .drop4").click(function() {
    $(".side-bar .sub4").slideToggle();
    $(".side-bar li a .arr4").toggleClass("act")
});
$(".side-bar .drop5").click(function() {
    $(".side-bar .sub5").slideToggle();
    $(".side-bar li a .arr5").toggleClass("act")
});
$(".side-bar .drop6").click(function() {
    $(".side-bar .sub6").slideToggle();
    $(".side-bar li a .arr6").toggleClass("act")
});


$("form .eye").click(function(e) {
    if ($(this).html() == "visibility") {
        $(this).html("visibility_off");
        $("form .pass").attr("type", "text")
    } else {
        $(this).html("visibility");
        $("form .pass").attr("type", "password")
    }

});

$(".logout-btn").click(function() {
    $(".logout-modal").modal({ keyboard: true })
});

// tootlip
$('[data-toggle="tooltip"]').tooltip();
$('[data-toggle="popover"]').popover();



var d = document.querySelectorAll(".date");
d.forEach(el => {
    $(el).persianDatepicker();
    $(el).attr("autocomplete", "off");
});

var input = document.querySelectorAll("input");
input.forEach(e => {
    $(e).attr("autocomplete", "off");
});
// search filter
$(".search").on("keyup", function() {
    if ($(this).val().length > 0) {
        $(".dataTables_info,.pagination").hide();
    } else {
        $(".dataTables_info,.pagination").show();
    }
    var value = $(this).val().toLowerCase();
    $(".table tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
});

// select 2
// $("form #up_category").select2({
//     placeholder: "انتخاب",
//     // allowClear: true
//     // theme: "classic"
//     // width: 'resolve'
// });



// delete question alert
function delQ(param) {
    Swal.fire({
        title: 'حذف کردن',
        text: 'آیا میخواهید این معلومات را حذف کنید؟',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله',
        cancelButtonText: 'نخیر'
    }).then((result) => {
        if (result.isConfirmed) {
            location.href = "delete?" + param
        }
    });
}



// bootstrap form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();


$(".table-striped:not(.order-table)").DataTable({
    // ordering: false,
    pageLength: 8,
    "language": {
        "sLengthMenu": "نمایش _MENU_ معلومات",
        "sZeroRecords": "هنوز ثبت نشده",
        "sInfoEmpty": "نمایش 0 از 0 معلومات",
        "sSearch": "جستجو: ",
        "sInfo": "نمایش _START_ تا _END_ از _TOTAL_",
        "infoFiltered": "از _MAX_",
        "oPaginate": {
            "sPrevious": "قبلی",
            "sNext": "بعدی"
        }
    }
});
// // $(".dataTables_length").hide()