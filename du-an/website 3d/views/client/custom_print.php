<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload & Xem Trước File 3D - 3DPrintHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        #viewport-3d {
            width: 100%;
            height: 400px;
            background-color: #1a1a1a;
            border-radius: 8px;
            position: relative;
        }
        .upload-area {
            border: 2px dashed #0d6efd;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            background-color: #f8f9fa;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Header dùng chung -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="home.php"><i class="fa-solid fa-cube me-2"></i>3DPrintHub</a>
            <span class="navbar-text text-white">Dịch vụ Báo giá & In 3D Tự động</span>
        </div>
    </nav>

    <div class="container my-4">
        <h3 class="fw-bold mb-4"><i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i>Đặt In 3D Theo Yêu Cầu</h3>

        <div class="row g-4">
            <!-- CỘT TRÁI: VIEWPORT BẢN VẼ 3D -->
            <div class="col-lg-7">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><i class="fa-solid fa-eye me-2"></i>Trình Xem Mô Hình 3D (Three.js)</h6>
                        <span class="badge bg-success">Sẵn sàng (.STL, .OBJ)</span>
                    </div>
                    <div class="card-body p-2">
                        <!-- Khung chứa canvas 3D -->
                        <div id="viewport-3d" class="d-flex align-items-center justify-content-center text-white-50">
                            <div class="text-center" id="placeholder-text">
                                <i class="fa-solid fa-cubes fs-1 mb-2"></i>
                                <p>Tải file 3D lên để xem mô hình 360° tại đây</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Khu vực Upload File -->
                <div class="upload-area">
                    <i class="fa-solid fa-file-arrow-up fs-1 text-primary mb-2"></i>
                    <h5>Kéo thả file .STL hoặc .OBJ vào đây</h5>
                    <p class="text-muted small">Dung lượng tối đa: 50MB</p>
                    <input type="file" id="fileInput" accept=".stl,.obj" class="d-none">
                    <button class="btn btn-outline-primary btn-sm" onclick="document.getElementById('fileInput').click()">Chọn file từ máy tính</button>
                </div>
            </div>

            <!-- CỘT PHẢI: FORM CẤU HÌNH THÔNG SỐ & BÁO GIÁ -->
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title fw-bold mb-0">Tùy Chọn In & Báo Giá</h5>
                    </div>
                    <div class="card-body">
                        <form>
                            <!-- Chọn Vendor / Xưởng in -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">1. Chọn Xưởng In (Vendor)</label>
                                <select class="form-select">
                                    <option selected>Xưởng In 3D Sài Gòn (Đánh giá: 4.9★)</option>
                                    <option>Lab 3D Hà Nội (Đánh giá: 4.8★)</option>
                                    <option>In 3D Nhanh Đà Nẵng (Đánh giá: 4.7★)</option>
                                </select>
                            </div>

                            <!-- Chọn Vật liệu -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">2. Vật Liệu In</label>
                                <select class="form-select" id="materialSelect">
                                    <option value="1500">Nhựa PLA (Phổ thông - 1.500đ/cm³)</option>
                                    <option value="2000">Nhựa ABS (Chịu nhiệt - 2.000đ/cm³)</option>
                                    <option value="3500">Nhựa Resin (Độ chi tiết cao - 3.500đ/cm³)</option>
                                </select>
                            </div>

                            <!-- Mật độ in (Infill %) -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">3. Mật Độ In (Infill %)</label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="range" class="form-range" min="10" max="100" step="10" value="20" id="infillRange">
                                    <span class="badge bg-secondary fs-6" id="infillValue">20%</span>
                                </div>
                            </div>

                            <!-- Số lượng -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">4. Số Lượng</label>
                                <input type="number" class="form-control" value="1" min="1">
                            </div>

                            <hr>

                            <!-- Khối tính tiền -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold">Thể tích ước tính:</span>
                                <span class="badge bg-light text-dark border">45.2 cm³</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fs-5 fw-bold">Tổng tiền tạm tính:</span>
                                <span class="text-danger fw-bold fs-4" id="totalPrice">67.800đ</span>
                            </div>

                            <button type="button" class="btn btn-warning w-100 btn-lg fw-bold text-dark">
                                <i class="fa-solid fa-cart-plus me-2"></i>Thêm Vào Giỏ Hàng
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tích hợp Three.js Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        // Cập nhật giá trị thanh trượt Infill %
        const infillRange = document.getElementById('infillRange');
        const infillValue = document.getElementById('infillValue');
        infillRange.addEventListener('input', (e) => {
            infillValue.textContent = e.target.value + '%';
        });
    </script>
</body>
</html>