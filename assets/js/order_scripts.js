function showQ(id) {
    delQ("order_id=" + id)
}

// for update
function getInfo(id) {
    $("#edit-modal #order_id").val(id);
    $.ajax({
        type: "get",
        url: "ajax/get_info",
        data: {
            order_id: id
        },
        success: function (response) {
            var res = JSON.parse(response);
            $("#edit-modal #status").val(res["status"]);
            $("#edit-modal #customer_id").val(res["customer_id"]);
            $("#edit-modal #product_id").val(res["product_id"]);
            $("#edit-modal").modal('show');
        }
    });
}

$(document).ready(function () {
    var table;

    function loadData(page = 1, limit = 6) {
        table = $(".order-table").DataTable({
            destroy: true,
            pageLength: 6,
            // pageLength: limit,
            // serverSide: true,
            // processing: true,
            // paging: true,
            "language": {
                "loadingRecords": "درحال بارگذاری...",
                "sLengthMenu": "نمایش _MENU_ سفارش",
                "sZeroRecords": "هنوز ثبت نشده",
                "sInfoEmpty": "نمایش 0 از 0 سفارش",
                "sSearch": "جستجو: ",
                "sInfo": "نمایش _START_ تا _END_ از _TOTAL_ سفارش",
                "infoFiltered": "از _MAX_",
                "oPaginate": {
                    "sPrevious": "قبلی",
                    "sNext": "بعدی"
                }
            },
            "ajax": {
                "url": "ajax/get_orders",
                "dataSrc": ""
            },
            "columns": [{
                "data": "num"
            },
            {
                "data": "product"
            },
            {
                "data": "customer"
            },
            {
                "data": "buy"
            },
            {
                "data": "sale"
            },
            {
                "data": "benefit"
            },
            {
                "data": "account_address"
            },
            {
                "data": "status"
            },
            {
                "data": "server"
            },
            {
                "data": "created"
            },
            {
                "data": "id",
                "render": function (data, type, row) {
                    if (row.status1 !== 'Rejected') {
                        return `<div class="btn-group p-0" dir="ltr"><button class="btn btn-danger pb-0 pt-2 edit-btn btn-sm" onclick="showQ( 
                            ${row.id}
                            )"  type="button"><span class="ico h6">delete</span></button><button class="pb-0 pt-2 btn btn-success btn-sm" onclick="getInfo(
                             ${row
                                .id})"  type="button"><span class="ico h6">edit</span></button></div>`
                    } else {
                        return `<div class="btn-group p-0" dir="ltr"><button class="btn btn-danger pb-0 pt-2 edit-btn btn-sm" onclick="showQ( 
                            ${row.id}
                            )"  type="button"><span class="ico h6">delete</span></button>`
                    }

                }
            }
            ]
        });

        new Audio("assets/notif.wav").play();

    }

    loadData();
    setInterval(() => {
        $.ajax({
            type: "get",
            url: "ajax/check_orders",
            success: function (response) {
                console.log(response);
                var res = JSON.parse(response);
                if (res["result"] == 1) {
                    loadData();
                }
            }
        });
    }, 100000);
});


