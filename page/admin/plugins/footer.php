 <style>
  #footer-background {
    background-image: url('../../dist/img/footer1.jpg');
    background-size: cover;
    background-position: center; 
    background-repeat: no-repeat; 
    color: white; 
    padding: 20px;
}

.main-footer {
    position: relative;
    z-index: 1;
}

 </style>
 
 <footer class="main-footer" id="footer-background">
    <strong>Copyright &copy; 2024. Developed by: Marc Neilsen Omabtang</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0.0
    </div>
</footer>

<?php
//MODALS
include '../../modals/logout_modal.php';

?>
<!-- jQuery -->
<script src="../../plugins/jquery/dist/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- SweetAlert2 -->
<script type="text/javascript" src="../../plugins/sweetalert2/dist/sweetalert2.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- overlayScrollbars -->
<script src="../../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.js"></script>
<!-- Popup Center -->
<script src="../../dist/js/popup_center.js"></script>
<!-- Serialize -->
<script src="../../dist/js/serialize.js"></script>

</body>
</html>