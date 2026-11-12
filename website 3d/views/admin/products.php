<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý & Kiểm Duyệt Sản Phẩm 3D - 3DPrintHub Admin</title>
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
                    <a class="nav-link " href="categories.php">
                        <i class="fa-solid fa-boxes-stacked me-2"></i>Quản lý Danh mục
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="products.php">
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
                    <h2 class="h4 fw-bold mb-0">Quản Lý & Kiểm Duyệt Sản Phẩm 3D</h2>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary-subtle text-primary px-3 py-2"><i class="fa-solid fa-user-gear me-1"></i> Admin System</span>
                </div>
            </div>

            
            <!-- THANH TÌM KIẾM & LỌC SẢN PHẨM -->
            <div class="card stat-card-admin bg-white p-3 mb-4">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" placeholder="Tìm theo tên mẫu in 3D hoặc mã SP...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm">
                            <option value="">-- Tất cả Xưởng in (Vendor) --</option>
                            <option>Xưởng In TechLab</option>
                            <option>ArtPrint Studio</option>
                            <option>Maker3D Hà Nội</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm">
                            <option value="">-- Lọc theo Vật liệu --</option>
                            <option>Nhựa PLA / PLA+</option>
                            <option>Nhựa ABS / PETG</option>
                            <option>Resin 8K SLA</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm w-100"><i class="fa-solid fa-filter me-1"></i>Lọc dữ liệu</button>
                    </div>
                </div>
            </div>

            <!-- BẢNG QUẢN LÝ & KIỂM DUYỆT SẢN PHẨM TOÀN SÀN -->
            <div class="card stat-card-admin bg-white">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-cube text-primary me-2"></i>Danh Sách Mẫu In 3D Toàn Sàn</h5>
                    <span class="badge bg-primary-subtle text-primary">Tổng: 259 sản phẩm</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã SP</th>
                                    <th>Tên Sản Phẩm</th>
                                    <th>Gian Hàng (Vendor)</th>
                                    <th>Danh Mục</th>
                                    <th>Vật Liệu</th>
                                    <th>Giá Bán</th>
                                    <th>Trạng Thái</th>
                                    <th class="text-end">Kiểm Duyệt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>#SP-101</code></td>
                                    <td><strong>Cánh tay Robot 4 bậc tự do</strong></td>
                                    <td>Xưởng In TechLab</td>
                                    <td>Cơ khí & Robot</td>
                                    <td><span class="badge bg-info-subtle text-info-emphasis">PLA+</span></td>
                                    <td class="fw-bold text-primary">240.000đ</td>
                                    <td><span class="badge bg-success">Đang bán</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-warning me-1" title="Ẩn sản phẩm"><i class="fa-solid fa-eye-slash"></i> Ẩn</button>
                                        <button class="btn btn-sm btn-outline-danger" title="Xóa vi phạm"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><code>#SP-102</code></td>
                                    <td><strong>Tượng mô hình Anime Figure 8K</strong></td>
                                    <td>ArtPrint Studio</td>
                                    <td>Tượng Figure</td>
                                    <td><span class="badge bg-success-subtle text-success-emphasis">Resin 8K</span></td>
                                    <td class="fw-bold text-primary">450.000đ</td>
                                    <td><span class="badge bg-success">Đang bán</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-warning me-1"><i class="fa-solid fa-eye-slash"></i> Ẩn</button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><code>#SP-103</code></td>
                                    <td><strong>Vỏ hộp Raspberry Pi 5 tản nhiệt</strong></td>
                                    <td>Maker3D Hà Nội</td>
                                    <td>Điện tử & IoT</td>
                                    <td><span class="badge bg-warning-subtle text-warning-emphasis">ABS</span></td>
                                    <td class="fw-bold text-primary">125.000đ</td>
                                    <td><span class="badge bg-warning text-dark">Chờ duyệt</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-success me-1" onclick="this.closest('tr').querySelectorAll('.badge')[1].className='badge bg-success';this.closest('tr').querySelectorAll('.badge')[1].textContent='Đang bán'">
                                            <i class="fa-solid fa-check"></i> Duyệt
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-xmark"></i> Gỡ</button>
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