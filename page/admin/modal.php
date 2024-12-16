<?php include 'plugins/navbar.php'; ?>
<?php include 'plugins/sidebar/admin_bar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Modal</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="view.php">Home</a></li>
                        <li class="breadcrumb-item active">Modal</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-gray-dark card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><img src="../../dist/img/modal.png" alt="Pagination Icon"
                                    class="nav-icon" style="width: 20px; height: 20px;"> Modal</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                        </div>


                        
                        <div class="card-body">
                            <button type="button" class="btn"
                                style="background-color: #28a745; border-color: #28a745; color: white;"
                                data-bs-toggle="modal" data-bs-target="#smallModal">
                                Open Small Modal
                            </button>
                            <button type="button" class="btn"
                                style="background-color: #28a745; border-color: #28a745; color: white;"
                                data-bs-toggle="modal" data-bs-target="#largeModal">
                                Open Large Modal
                            </button>
                            <button type="button" class="btn"
                                style="background-color: #28a745; border-color: #28a745; color: white;"
                                data-bs-toggle="modal" data-bs-target="#extraLargeModal">
                                Open Extra Large Modal
                            </button>
                        </div>


                        <!-- Small Modal -->
                        <div class="modal fade" id="smallModal" tabindex="-1" aria-labelledby="smallModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header" style="background-color: #343a40; color: white;">
                                        <h5 class="modal-title" id="smallModalLabel">Small Modal</h5>
                                        <button type="button" class="btn-close" style="filter: invert(1);"
                                            data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        This is a small modal.
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Large Modal -->
                        <div class="modal fade" id="largeModal" tabindex="-1" aria-labelledby="largeModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">

                                    <div class="modal-header" style="background-color: #343a40; color: white;">
                                        <h5 class="modal-title" id="largeModalLabel">Large Modal</h5>
                                        <button type="button" class="btn-close" style="filter: invert(1);"
                                            data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        This is a large modal.
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Extra Large Modal -->
                        <div class="modal fade" id="extraLargeModal" tabindex="-1"
                            aria-labelledby="extraLargeModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header" style="background-color: #343a40; color: white;">
                                        <h5 class="modal-title" id="extraLargeModalLabel">Extra Large Modal</h5>
                                        <button type="button" class="btn-close" style="filter: invert(1);"
                                            data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        This is an extra large modal.
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'plugins/footer.php'; ?>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>