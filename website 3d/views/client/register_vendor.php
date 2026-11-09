<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Mở Gian Hàng - 3DPrintHub</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <!-- Header dùng chung -->
    <?php include_once '../layouts/header.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h4 class="card-title fw-bold mb-0 text-center">
                            <i class="fa-solid fa-store me-2"></i>Đăng Ký Trở Thành Người Bán (Vendor)
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted text-center mb-4">
                            Mở gian hàng trên 3DPrintHub để tiếp cận hàng ngàn khách hàng có nhu cầu in 3D và bán file thiết kế toàn quốc!
                        </p>

                        <form action="" method="POST" enctype="multipart/form-data">
                            
                            <!-- THÔNG TIN CHỦ XƯỞNG / ĐẠI DIỆN -->
                            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">1. Thông tin người đại diện</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Họ và tên người đăng ký <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="fullname" placeholder="Nguyễn Văn A" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" name="phone" placeholder="0912345678" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-medium">Email tài khoản <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" placeholder="vendor@gmail.com" required>
                            </div>

                            <!-- THÔNG TIN GIAN HÀNG / XƯỞNG IN -->
                            <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">2. Thông tin Gian hàng / Xưởng In 3D</h6>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Tên Gian Hàng / Xưởng In <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="shop_name" placeholder="Ví dụ: Xưởng In 3D Sài Gòn" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium">Địa chỉ Xưởng in / Kho hàng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="address" placeholder="Số nhà, Tên đường, Quận/Huyện, Tỉnh/Thành phố" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium">Mô tả ngắn về Xưởng / Trang thiết bị in 3D</label>
                                <textarea class="form-control" name="description" rows="3" placeholder="Giới thiệu các loại máy in (FDM, SLA Resin), vật liệu hỗ trợ, công suất..."></textarea>
                            </div>

                            <!-- CAM KẾT VÀ NÚT GỬI -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                                <label class="form-check-label small" for="agreeTerms">
                                    Tôi cam kết các thông tin khai báo trên là chính xác và đồng ý với điều khoản dịch vụ dành cho Vendor của 3DPrintHub.
                                </label>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold">
                                    <i class="fa-solid fa-paper-plane me-2"></i>Gửi Hồ Sơ Đăng Ký Gian Hàng
                                </button>
                                <a href="home.php" class="btn btn-outline-secondary">Quay lại Trang chủ</a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer dùng chung -->
    <?php include_once '../layouts/footer.php'; ?>

</body>
</html>