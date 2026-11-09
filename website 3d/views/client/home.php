<?php require_once '../layouts/header.php'; ?>

<!-- 2. HERO BANNER IN 3D -->
<div class="hero-banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="display-4 fw-bold mb-3">Dịch Vụ In 3D Theo Yêu Cầu & Mẫu Thiết Kế Mới Nhất</h1>
                <p class="lead mb-4">Kết nối hàng trăm xưởng in 3D uy tín toàn quốc. Tải file .STL/.OBJ nhận báo giá tự động tức thì!</p>
                <div class="d-flex gap-3">
                    <a href="custom_print.php" class="btn btn-warning btn-lg fw-bold"><i class="fa-solid fa-upload me-2"></i>Tải file 3D Báo Giá</a>
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

<?php require_once '../layouts/footer.php'; ?>