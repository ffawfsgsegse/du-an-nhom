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

    <!-- 1. HEADER / NAVBAR CHUNG -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary fs-3" href="home.php"><i class="fa-solid fa-cube me-2"></i>3DPrintHub</a>
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
                        <a class="btn btn-outline-warning text-white btn-sm" href="custom_print.php">
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
                            <li><a class="dropdown-item" href="#">Đơn hàng của tôi</a></li>
                            <li><a class="dropdown-item" href="#">Trang cá nhân</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-primary fw-bold" href="register_vendor.php">
                                    <i class="fa-solid fa-store me-1"></i> Đăng ký mở Gian hàng
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>