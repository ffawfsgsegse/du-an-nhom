<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kênh Người Bán - 3DPrintHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #212529; }
        .sidebar .nav-link { color: #adb5bd; padding: 12px 20px; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { color: #fff; background-color: #0d6efd; }
        .stat-card { border-left: 4px solid #0d6efd; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- SIDEBAR GIAN HÀNG -->
        <div class="col-md-3 col-lg-2 px-0 sidebar collapse d-md-block" id="sidebarMenu">
            <div class="p-3 text-white text-center border-bottom border-secondary">
                <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-store me-2"></i>Vendor Portal</h5>
                <small class="text-muted">Gian hàng: Xưởng In 3D Sài Gòn</small>
            </div>
            <ul class="nav flex-column mt-3">
                <li class="nav-item"><a class="nav-link active" href="#"><i class="fa-solid fa-chart-line me-2"></i>Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-box me-2"></i>Quản lý Sản phẩm</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-receipt me-2"></i>Đơn đặt in 3D</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-sliders me-2"></i>Cài đặt Giá vật liệu</a></li>
                <li class="nav-item"><a class="nav-link text-danger mt-4" href="#"><i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất</a></li>
            </ul>
        </div>

        <!-- NỘI DUNG CHÍNH (MAIN CONTENT) -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <h2 class="h3 fw-bold mb-4">Dashboard Gian Hàng</h2>

            <!-- THỐNG KÊ NHANH -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card stat-card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted small">DOANH THU THÁNG NÀY</h6>
                            <h3 class="fw-bold text-success mb-0">12.450.000đ</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card shadow-sm" style="border-left-color: #ffc107;">
                        <div class="card-body">
                            <h6 class="text-muted small">ĐƠN HÀNG ĐANG IN 3D</h6>
                            <h3 class="fw-bold text-warning mb-0">8 Đơn hàng</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card shadow-sm" style="border-left-color: #17a2b8;">
                        <div class="card-body">
                            <h6 class="text-muted small">FILE ĐẶT IN MỚI CHỜ DUYỆT</h6>
                            <h3 class="fw-bold text-info mb-0">3 File .STL</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BẢNG ĐƠN HÀNG CẦN XỬ LÝ -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">Đơn Hàng Đặt In 3D Mới Nhất</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Loại file</th>
                                    <th>Vật liệu / Tỉ lệ in</th>
                                    <th>Tạm tính</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>#ORD-9082</strong></td>
                                    <td>Nguyen Van A</td>
                                    <td><span class="badge bg-secondary">robot_arm.stl</span></td>
                                    <td>Nhựa PLA / 20% Infill</td>
                                    <td class="text-danger fw-bold">320.000đ</td>
                                    <td><span class="badge bg-warning text-dark">Đang duyệt file</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary"><i class="fa-solid fa-eye me-1"></i>Xem 3D & Duyệt</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>#ORD-9081</strong></td>
                                    <td>Tran Thi B</td>
                                    <td><span class="badge bg-secondary">avatar_resin.obj</span></td>
                                    <td>Resin Xám / 100% Infill</td>
                                    <td class="text-danger fw-bold">550.000đ</td>
                                    <td><span class="badge bg-info text-dark">Đang in 3D</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-success"><i class="fa-solid fa-check me-1"></i>Hoàn thành in</button>
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