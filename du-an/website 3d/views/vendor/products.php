<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - Vendor Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #212529; }
        .sidebar .nav-link { color: #rgba(255,255,255,.75); padding: 12px 20px; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { color: #fff; background-color: #0d6efd; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Nhúng Sidebar của Vendor -->
        <?php include_once '../layouts/vendor_sidebar.php'; ?>

        <!-- NỘI DUNG CHÍNH -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <h2 class="h3 fw-bold mb-0"><i class="fa-solid fa-box text-primary me-2"></i>Quản Lý Sản Phẩm In 3D</h2>
                <a href="product_add.php" class="btn btn-primary fw-bold">
                    <i class="fa-solid fa-plus me-1"></i> Thêm Sản Phẩm Mới
                </a>
            </div>

            <!-- BẢNG DANH SÁCH SẢN PHẨM -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>STT</th>
                                    <th>Hình ảnh</th>
                                    <th>Tên Sản Phẩm</th>
                                    <th>Danh Mục</th>
                                    <th>Giá Bán</th>
                                    <th>File 3D Preview (.STL)</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Mẫu sản phẩm 1 -->
                                <tr>
                                    <td>1</td>
                                    <td>
                                        <img src="https://via.placeholder.com/60" class="rounded border" alt="SP">
                                    </td>
                                    <td>
                                        <strong class="d-block">Mô hình IronMan Chibi STL</strong>
                                        <small class="text-muted">ID: #PRD-01</small>
                                    </td>
                                    <td><span class="badge bg-info text-dark">Mô hình / Figure</span></td>
                                    <td class="text-danger fw-bold">150.000đ</td>
                                    <td>
                                        <span class="badge bg-secondary"><i class="fa-solid fa-cube me-1"></i>ironman_chibi.stl</span>
                                    </td>
                                    <td>
                                        <a href="product_edit.php?id=1" class="btn btn-sm btn-outline-warning me-1" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen-to-square"></i> Sửa
                                        </a>
                                        <a href="product_delete.php?id=1" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');" title="Xóa">
                                            <i class="fa-solid fa-trash"></i> Xóa
                                        </a>
                                    </td>
                                </tr>
                                <!-- Mẫu sản phẩm 2 -->
                                <tr>
                                    <td>2</td>
                                    <td>
                                        <img src="https://via.placeholder.com/60" class="rounded border" alt="SP">
                                    </td>
                                    <td>
                                        <strong class="d-block">Giá đỡ điện thoại khớp cơ khí</strong>
                                        <small class="text-muted">ID: #PRD-02</small>
                                    </td>
                                    <td><span class="badge bg-info text-dark">Phụ kiện & Cơ khí</span></td>
                                    <td class="text-danger fw-bold">85.000đ</td>
                                    <td>
                                        <span class="badge bg-secondary"><i class="fa-solid fa-cube me-1"></i>phone_holder.stl</span>
                                    </td>
                                    <td>
                                        <a href="product_edit.php?id=2" class="btn btn-sm btn-outline-warning me-1">
                                            <i class="fa-solid fa-pen-to-square"></i> Sửa
                                        </a>
                                        <a href="product_delete.php?id=2" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                                            <i class="fa-solid fa-trash"></i> Xóa
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>