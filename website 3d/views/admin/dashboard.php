<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Trị Hệ Thống - 3DPrintHub Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .admin-sidebar { min-height: 100vh; background-color: #1e293b; }
        .admin-sidebar .nav-link { color: #94a3b8; padding: 12px 20px; font-weight: 500; }
        .admin-sidebar .nav-link.active, .admin-sidebar .nav-link:hover { color: #ffffff; background-color: #0f172a; border-left: 4px solid #0d6efd; }
        .stat-card-admin { border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .icon-box { width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- 1. SIDEBAR QUẢN TRỊ ADMIN -->
        <div class="col-md-3 col-lg-2 px-0 admin-sidebar collapse d-md-block" id="adminSidebar">
            <div class="p-3 text-white text-center border-bottom border-secondary">
                <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-shield-halved me-2"></i>Admin Portal</h5>
                <small class="text-muted">Hệ thống Sàn In 3D</small>
            </div>
            <ul class="nav flex-column mt-3">
                <li class="nav-item">
                    <a class="nav-link active" href="#"><i class="fa-solid fa-chart-pie me-2"></i>Tổng quan sàn</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center" href="#">
                        <span><i class="fa-solid fa-store me-2"></i>Duyệt Gian hàng</span>
                        <span class="badge bg-warning text-dark rounded-pill">2</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fa-solid fa-users me-2"></i>Quản lý Người dùng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fa-solid fa-boxes-stacked me-2"></i>Danh mục toàn sàn</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fa-solid fa-sack-dollar me-2"></i>Hoa hồng & Doanh thu</a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="#"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</a>
                </li>
            </ul>
        </div>

        <!-- 2. NỘI DUNG CHÍNH (MAIN CONTENT) -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            
            <!-- HEADER TOP -->
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <h2 class="h3 fw-bold mb-0">Báo Cáo & Thống Kê Toàn Sàn</h2>
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-medium"><i class="fa-solid fa-user-gear me-1"></i> Admin System</span>
                </div>
            </div>

            <!-- THỐNG KÊ 4 KHỐI THÔNG SỐ TOÀN SÀN -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card-admin p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted fw-bold">TỔNG DOANH THU SÀN</small>
                                <h4 class="fw-bold text-dark mt-1 mb-0">145.800.000đ</h4>
                            </div>
                            <div class="icon-box bg-primary-subtle text-primary fs-4">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card-admin p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted fw-bold">HOA HỒNG THU VỀ (5%)</small>
                                <h4 class="fw-bold text-success mt-1 mb-0">7.290.000đ</h4>
                            </div>
                            <div class="icon-box bg-success-subtle text-success fs-4">
                                <i class="fa-solid fa-piggy-bank"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card-admin p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted fw-bold">TỔNG VENDOR ACTIVE</small>
                                <h4 class="fw-bold text-info mt-1 mb-0">18 Gian hàng</h4>
                            </div>
                            <div class="icon-box bg-info-subtle text-info fs-4">
                                <i class="fa-solid fa-shop"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stat-card-admin p-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted fw-bold">HỒ SƠ MỞ SHOP CHỜ DUYỆT</small>
                                <h4 class="fw-bold text-warning mt-1 mb-0">2 Đơn xin</h4>
                            </div>
                            <div class="icon-box bg-warning-subtle text-warning fs-4">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BẢNG 1: DUYỆT HỒ SƠ ĐĂNG KÝ VENDOR MỚI -->
            <div class="card stat-card-admin bg-white mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-file-signature text-warning me-2"></i>Duyệt Hồ Sơ Đăng Ký Mở Gian Hàng</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>STT</th>
                                    <th>Tên Gian Hàng</th>
                                    <th>Chủ Sở Hữu</th>
                                    <th>Số Điện Thoại</th>
                                    <th>Địa Chỉ Xưởng</th>
                                    <th>Trạng Thái</th>
                                    <th>Thao Tác Quản Trị</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td><strong>Xưởng In 3D Cần Thơ</strong></td>
                                    <td>Lê Văn C</td>
                                    <td>0912345678</td>
                                    <td>Ninh Kiều, Cần Thơ</td>
                                    <td><span class="badge bg-warning text-dark">Chờ kiểm duyệt</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-success me-1"><i class="fa-solid fa-check me-1"></i>Duyệt Shop</button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-xmark me-1"></i>Từ chối</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td><strong>Lab 3D Đà Nẵng</strong></td>
                                    <td>Phạm Thị D</td>
                                    <td>0988776655</td>
                                    <td>Hải Châu, Đà Nẵng</td>
                                    <td><span class="badge bg-warning text-dark">Chờ kiểm duyệt</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-success me-1"><i class="fa-solid fa-check me-1"></i>Duyệt Shop</button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-xmark me-1"></i>Từ chối</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- BẢNG 2: QUẢN LÝ TÀI KHOẢN TOÀN HỆ THỐNG -->
            <div class="card stat-card-admin bg-white">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-users-gear text-primary me-2"></i>Danh Sách Tài Khoản Toàn Sàn</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Họ và Tên</th>
                                    <th>Email</th>
                                    <th>Vai trò (Role)</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#USR-01</td>
                                    <td>Nguyễn Văn A</td>
                                    <td>nguyenvana@gmail.com</td>
                                    <td><span class="badge bg-secondary">Customer</span></td>
                                    <td><span class="badge bg-success">Hoạt động</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-lock"></i> Khóa</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#USR-02</td>
                                    <td>Xưởng In 3D Sài Gòn</td>
                                    <td>vendor_sg@gmail.com</td>
                                    <td><span class="badge bg-primary">Vendor</span></td>
                                    <td><span class="badge bg-success">Hoạt động</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-lock"></i> Khóa</button>
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