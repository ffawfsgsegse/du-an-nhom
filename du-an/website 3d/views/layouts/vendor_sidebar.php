<?php
// Lấy tên file đang chạy (vd: products.php, dashboard.php)
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>

<!-- SIDEBAR GIAN HÀNG CHUNG -->
<div class="col-md-3 col-lg-2 px-0 sidebar collapse d-md-block" id="sidebarMenu">
    <div class="p-3 text-white text-center border-bottom border-secondary">
        <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-store me-2"></i>Vendor Portal</h5>
        <small class="text-muted">Gian hàng: Xưởng In 3D Sài Gòn</small>
    </div>
    <ul class="nav flex-column mt-3">
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" 
               href="/du-an-nhom/website%203d/views/vendor/dashboard.php">
                <i class="fa-solid fa-chart-line me-2"></i>Tổng quan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'products.php' || $current_page == 'product_add.php' || $current_page == 'product_edit.php') ? 'active' : ''; ?>" 
               href="/du-an-nhom/website%203d/views/vendor/products.php">
                <i class="fa-solid fa-box me-2"></i>Quản lý Sản phẩm
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>" href="#">
                <i class="fa-solid fa-receipt me-2"></i>Đơn đặt in 3D
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>" href="#">
                <i class="fa-solid fa-sliders me-2"></i>Cài đặt Giá vật liệu
            </a>
        </li>
        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="#">
                <i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất
            </a>
        </li>
    </ul>
</div>