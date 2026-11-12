<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Danh Mục Toàn Sàn - 3DPrintHub Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .admin-sidebar { min-height: 100vh; background-color: #1e293b; }
        .admin-sidebar .nav-link { color: #94a3b8; padding: 12px 20px; font-weight: 500; transition: all 0.2s; }
        .admin-sidebar .nav-link.active, .admin-sidebar .nav-link:hover { color: #ffffff; background-color: #0f172a; border-left: 4px solid #0d6efd; }
        .stat-card-admin { border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .icon-box { width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- 1. SIDEBAR QUẢN TRỊ ADMIN (Đồng bộ Wireframe 06-08) -->
        <div class="col-md-3 col-lg-2 px-0 admin-sidebar collapse d-md-block" id="adminSidebar">
            <div class="p-3 text-white text-center border-bottom border-secondary">
                <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-shield-halved me-2"></i>Admin Portal</h5>
                <small class="text-muted">Hệ thống Sàn In 3D</small>
            </div>
            <ul class="nav flex-column mt-3">
                <li class="nav-item">
                    <a class="nav-link " href="dashboard.php">
                        <i class="fa-solid fa-chart-pie me-2"></i>Tổng quan & Duyệt Shop
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="categories.php">
                        <i class="fa-solid fa-boxes-stacked me-2"></i>Quản lý Danh mục
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="products.php">
                        <i class="fa-solid fa-cube me-2"></i>Quản lý Sản phẩm 3D
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " href="users.php">
                        <i class="fa-solid fa-users me-2"></i>Quản lý Người dùng
                    </a>
                </li>
                <li class="nav-item mt-4 pt-3 border-top border-secondary">
                    <a class="nav-link text-warning" href="../client/home.php">
                        <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Về trang Client
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="#"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</a>
                </li>
            </ul>
        </div>

        <!-- 2. NỘI DUNG CHÍNH (MAIN CONTENT) -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <!-- HEADER TOP -->
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#adminSidebar">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h2 class="h4 fw-bold mb-0">Quản Lý Danh Mục Toàn Sàn</h2>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary-subtle text-primary px-3 py-2"><i class="fa-solid fa-user-gear me-1"></i> Admin System</span>
                </div>
            </div>

            
            <div class="row g-4">
                <!-- Cột trái: Form thêm danh mục mới (4 cột) -->
                <div class="col-lg-4">
                    <div class="card stat-card-admin bg-white p-4">
                        <h5 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-folder-plus text-primary me-2"></i>Thêm Danh Mục Mới</h5>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Tên danh mục (*)</label>
                                <input type="text" name="name" class="form-control" placeholder="VD: Linh kiện Cơ khí & Robot" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Đường dẫn tĩnh (Slug)</label>
                                <input type="text" name="slug" class="form-control" placeholder="linh-kien-co-khi-robot">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Mô tả danh mục</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Mô tả ngắn gọn về nhóm sản phẩm in 3D..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Trạng thái hiển thị</label>
                                <select name="status" class="form-select">
                                    <option value="1">Hiển thị trên sàn</option>
                                    <option value="0">Tạm ẩn</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Lưu Danh Mục
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Cột phải: Bảng danh sách danh mục (8 cột) -->
                <div class="col-lg-8">
                    <div class="card stat-card-admin bg-white">
                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-list text-primary me-2"></i>Danh Sách Danh Mục Toàn Sàn</h5>
                            <span class="text-muted small">Tổng cộng: 4 danh mục</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên Danh Mục</th>
                                            <th>Slug</th>
                                            <th>Số Sản Phẩm</th>
                                            <th>Trạng Thái</th>
                                            <th class="text-end">Thao Tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#DM-01</td>
                                            <td><strong>Mô hình Cơ khí & Robot</strong></td>
                                            <td><code>mo-hinh-co-khi</code></td>
                                            <td><span class="badge bg-light text-dark border">64 SP</span></td>
                                            <td><span class="badge bg-success">Hiển thị</span></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-pen"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#DM-02</td>
                                            <td><strong>Tượng Figure & Nghệ thuật</strong></td>
                                            <td><code>tuong-figure</code></td>
                                            <td><span class="badge bg-light text-dark border">112 SP</span></td>
                                            <td><span class="badge bg-success">Hiển thị</span></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-pen"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#DM-03</td>
                                            <td><strong>Vỏ hộp Điện tử & IoT</strong></td>
                                            <td><code>vo-hop-dien-tu</code></td>
                                            <td><span class="badge bg-light text-dark border">48 SP</span></td>
                                            <td><span class="badge bg-success">Hiển thị</span></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-pen"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#DM-04</td>
                                            <td><strong>Đồ gia dụng & Trang trí</strong></td>
                                            <td><code>do-gia-dung</code></td>
                                            <td><span class="badge bg-light text-dark border">35 SP</span></td>
                                            <td><span class="badge bg-secondary">Tạm ẩn</span></td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-pen"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>