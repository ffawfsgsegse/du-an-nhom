<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng - 3DPrintHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="home.php"><i class="fa-solid fa-cube me-2"></i>3DPrintHub</a>
        </div>
    </nav>

    <div class="container">
        <h3 class="fw-bold mb-4"><i class="fa-solid fa-cart-shopping me-2"></i>Giỏ Hàng Của Bạn</h3>

        <div class="row g-4">
            <div class="col-lg-8">
                <!-- NHÓM SẢN PHẨM THEO GIAN HÀNG 1 -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <span class="fw-bold"><i class="fa-solid fa-shop text-primary me-2"></i>Gian hàng: Xưởng In 3D Sài Gòn</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table align-middle mb-0">
                            <tbody>
                                <tr>
                                    <td style="width: 80px;">
                                        <img src="https://via.placeholder.com/70" class="rounded" alt="SP">
                                    </td>
                                    <td>
                                        <h6 class="mb-1 fw-bold">File Đặt In: robot_arm.stl</h6>
                                        <small class="text-muted">Nhựa PLA | Infill 20% | Màu Đen</small>
                                    </td>
                                    <td class="text-danger fw-bold">320.000đ</td>
                                    <td style="width: 120px;">
                                        <input type="number" class="form-control form-control-sm text-center" value="1">
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- NHÓM SẢN PHẨM THEO GIAN HÀNG 2 -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <span class="fw-bold"><i class="fa-solid fa-shop text-primary me-2"></i>Gian hàng: Lab 3D Hà Nội</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table align-middle mb-0">
                            <tbody>
                                <tr>
                                    <td style="width: 80px;">
                                        <img src="https://via.placeholder.com/70" class="rounded" alt="SP">
                                    </td>
                                    <td>
                                        <h6 class="mb-1 fw-bold">Giá đỡ điện thoại khớp cơ khí</h6>
                                        <small class="text-muted">Mẫu bán sẵn | Nhựa PLA+</small>
                                    </td>
                                    <td class="text-danger fw-bold">85.000đ</td>
                                    <td style="width: 120px;">
                                        <input type="number" class="form-control form-control-sm text-center" value="2">
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TỔNG KẾT THANH TOÁN -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Tóm Tắt Đơn Hàng</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tạm tính (2 gian hàng):</span>
                            <span>490.000đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Phí vận chuyển ước tính:</span>
                            <span>30.000đ</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5">Tổng cộng:</span>
                            <span class="text-danger fw-bold fs-4">520.000đ</span>
                        </div>
                        <button class="btn btn-primary w-100 btn-lg fw-bold">Proceed to Checkout</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>