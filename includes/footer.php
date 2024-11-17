<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/datatable/jquery.dataTables.min.js"></script>
<script src="assets/datatable/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- <script src="assets/datatable/dataTables.buttons.min.js"></script> -->
<!-- <script src="assets/datatable/buttons.bootstrap4.min.js"></script> -->
<!-- <script src="assets/datatable/buttons.html5.min.js"></script> -->
<!-- <script src="assets/datatable/buttons.print.min.js"></script> -->
<!-- <script src="assets/datatable/jszip.min.js"></script> -->
<!--<script src="assets/datatable/pdfmake.min.js"></script>
        <script src="assets/datatable/vfs_fonts.js"></script> -->
<!--<script src="assets/js/prDatepicker.min.js"></script>-->
<script src="assets/js/sweetalert2.min.js"></script>
<!-- <script src="assets/js/chart.min.js"></script> -->
<script src="assets/js/main.js"></script>

<?php
if (isset($_GET["opr"]) || isset($_GET["add"]) || isset($_GET["update"])) {
?>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-left',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        })
        Toast.fire({
            icon: 'success',
            title: 'موفقانه انجام شد',
        })
    </script>
<?php } ?>
<script>
    var d_input = document.querySelectorAll(".date");
    d_input.forEach(e => {
        if (!e.classList.contains("no-show")) {
            $(e).val("<?= $today ?>")
        }
    })
</script>