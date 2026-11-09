<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3DPrintHub - Sàn Thương Mại Điện Tử & Dịch Vụ In 3D Multi-Vendor</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-banner { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white; padding: 60px 0; }
        .product-card { transition: transform 0.2s; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .product-card:hover { transform: translateY(-5px); }
        .badge-vendor { background-color: #eef2ff; color: #4f46e5; border: 1px solid #c7d2fe; }
    </style>
</head>
<body class="bg-light">

    <!-- 1. HEADER / NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-3" href="#"><i class="fa-solid fa-cube me-2"></i>3DPrintHub</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <form class="d-flex mx-auto my-2 my-lg-0 w-50">
                    <input class="form-control me-2" type="search" placeholder="Tìm kiếm mẫu in 3D, tên shop, vật liệu (PLA, Resin)...">
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <a class="btn btn-outline-warning text-white btn-sm" href="custom-print.php">
                            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Báo giá In 3D
                        </a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fa-solid fa-cart-shopping fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-circle-user fs-5 me-1"></i> Tài khoản
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-menu-item dropdown-item" href="#">Đơn hàng của tôi</a></li>
                            <li><a class="dropdown-item" href="#">Trang cá nhân</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-primary fw-bold" href="#"><i class="fa-solid fa-store me-1"></i> Đăng ký mở Gian hàng</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 2. HERO BANNER IN 3D -->
    <div class="hero-banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="display-4 fw-bold mb-3">Dịch Vụ In 3D Theo Yêu Cầu & Mẫu Thiết Kế Mới Nhất</h1>
                    <p class="lead mb-4">Kết nối hàng trăm xưởng in 3D uy tín toàn quốc. Tải file .STL/.OBJ nhận báo giá tự động tức thì!</p>
                    <div class="d-flex gap-3">
                        <a href="#custom-upload" class="btn btn-warning btn-lg fw-bold"><i class="fa-solid fa-upload me-2"></i>Tải file 3D Báo Giá</a>
                        <a href="#products" class="btn btn-outline-light btn-lg">Khám Phá Mẫu In</a>
                    </div>
                </div>
                <div class="col-lg-5 text-center mt-4 mt-lg-0">
                    <img src="https://via.placeholder.com/400x300?text=3D+Printer+Illustration" alt="In 3D" class="img-fluid rounded-3 shadow">
                </div>
            </div>
        </div>
    </div>

    <!-- 3. DANH SÁCH SẢN PHẨM / MẪU 3D NỔI BẬT -->
    <div class="container my-5" id="products">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="fa-solid fa-fire text-danger me-2"></i>Mẫu Thiết Kế 3D Nổi Bật</h3>
            <div class="btn-group">
                <button class="btn btn-outline-secondary btn-sm active">Tất cả</button>
                <button class="btn btn-outline-secondary btn-sm">Nhựa PLA</button>
                <button class="btn btn-outline-secondary btn-sm">Nhựa Resin</button>
                <button class="btn btn-outline-secondary btn-sm">Mô hình / Figure</button>
            </div>
        </div>

        <div class="row g-4">
            <!-- Sản phẩm 1 -->
            <div class="col-md-4 col-lg-3">
                <div class="card h-100 product-card">
                    <img src="https://via.placeholder.com/300x200?text=3D+Model+1" class="card-img-top" alt="Sản phẩm">
                    <div class="card-body d-flex flex-column">
                        <span class="badge badge-vendor w-auto align-self-start mb-2"><i class="fa-solid fa-shop me-1"></i>Xưởng In X-3D</span>
                        <h5 class="card-title text-truncate">Mô hình IronMan Chibi STL</h5>
                        <p class="text-muted small mb-2">Chất liệu: Resin cao cấp</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="text-danger fw-bold fs-5">150.000đ</span>
                            <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-cart-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sản phẩm 2 -->
            <div class="col-md-4 col-lg-3">
                <div class="card h-100 product-card">
                    <img src="https://via.placeholder.com/300x200?text=3D+Model+2" class="card-img-top" alt="Sản phẩm">
                    <div class="card-body d-flex flex-column">
                        <span class="badge badge-vendor w-auto align-self-start mb-2"><i class="fa-solid fa-shop me-1"></i>Lab 3D Hà Nội</span>
                        <h5 class="card-title text-truncate">Giá đỡ điện thoại khớp cơ khí</h5>
                        <p class="text-muted small mb-2">Chất liệu: Nhựa PLA+</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="text-danger fw-bold fs-5">85.000đ</span>
                            <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-cart-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>