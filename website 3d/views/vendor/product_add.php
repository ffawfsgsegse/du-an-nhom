<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sản Phẩm Mới - Vendor Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #212529; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php include_once '../layouts/vendor_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <h2 class="h3 fw-bold mb-0">Thêm Sản Phẩm In 3D Mới</h2>
                <a href="products.php" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
                </a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            
                            <!-- Tên sản phẩm -->
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" placeholder="Nhập tên mẫu 3D / sản phẩm" required>
                            </div>

                            <!-- Chọn danh mục -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    <option value="1">Mô hình / Figure</option>
                                    <option value="2">Phụ kiện & Cơ khí</option>
                                    <option value="3">Trang trí & Gia dụng</option>
                                </select>
                            </div>

                            <!-- Giá bán -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Giá bán (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="price" placeholder="Ví dụ: 150000" min="0" step="1000" required>
                            </div>

                            <!-- Upload Ảnh đại diện -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Hình ảnh sản phẩm (PNG, JPG)</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                            </div>

                            <!-- Upload File 3D preview -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold">File mẫu 3D (.STL / .OBJ) <small class="text-muted">(Dùng để hiển thị xem trước 360° cho khách)</small></label>
                                <input type="file" class="form-control" name="file_stl" accept=".stl,.obj">
                            </div>

                            <!-- Mô tả chi tiết -->
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Mô tả sản phẩm</label>
                                <textarea class="form-control" name="description" rows="4" placeholder="Nhập kích thước, độ phân giải layer đề xuất, loại nhựa phù hợp..."></textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold">
                                    <i class="fa-solid fa-floppy-disk me-2"></i>Lưu Sản Phẩm
                                </button>
                                <a href="products.php" class="btn btn-light btn-lg border ms-2">Hủy</a>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>