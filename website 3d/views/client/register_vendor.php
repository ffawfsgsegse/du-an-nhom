<?php require_once '../layouts/header.php'; ?>

<style>
    .vendor-reg-card {
        max-width: 980px;
        margin: 40px auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 40px 60px;
    }
    /* Thanh tiến trình Stepper */
    .stepper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        margin-bottom: 45px;
    }
    .stepper-progress-line {
        position: absolute;
        top: 9px;
        left: 5%;
        right: 5%;
        height: 2px;
        background: #e9ecef;
        z-index: 1;
    }
    .step-item {
        position: relative;
        z-index: 2;
        text-align: center;
        background: #fff;
        padding: 0 12px;
    }
    .step-dot {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #dee2e6;
        margin: 0 auto 8px;
        transition: all 0.2s;
    }
    .step-item.active .step-dot {
        background: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
    }
    .step-item.completed .step-dot {
        background: #198754;
    }
    .step-label {
        font-size: 14px;
        color: #6c757d;
    }
    .step-item.active .step-label {
        color: #0d6efd;
        font-weight: 600;
    }
    .step-item.completed .step-label {
        color: #198754;
    }
    /* Form căn lề 2 cột kiểu Shopee */
    .form-row-custom {
        display: flex;
        align-items: flex-start;
        margin-bottom: 22px;
    }
    .label-custom {
        width: 190px;
        text-align: right;
        padding-right: 20px;
        padding-top: 7px;
        font-size: 14px;
        color: #333;
        font-weight: 500;
    }
    .input-wrap-custom {
        flex: 1;
        max-width: 500px;
    }
    /* Ẩn các bước chưa kích hoạt */
    .form-step {
        display: none;
    }
    .form-step.active {
        display: block;
    }
</style>

<div class="container">
    <div class="vendor-reg-card">
        
        <!-- THANH TIẾN TRÌNH STEPPER -->
        <div class="stepper">
            <div class="stepper-progress-line"></div>
            
            <div class="step-item active" id="step-dot-1">
                <div class="step-dot"></div>
                <div class="step-label">Thông tin Xưởng / Gian hàng</div>
            </div>
            <div class="step-item" id="step-dot-2">
                <div class="step-dot"></div>
                <div class="step-label">Năng lực in 3D</div>
            </div>
            <div class="step-item" id="step-dot-3">
                <div class="step-dot"></div>
                <div class="step-label">Thông tin đại diện</div>
            </div>
            <div class="step-item" id="step-dot-4">
                <div class="step-dot"></div>
                <div class="step-label">Hoàn tất</div>
            </div>
        </div>

        <!-- FORM ĐĂNG KÝ -->
        <form action="" method="POST" id="vendorRegForm">
            
            <!-- BƯỚC 1: THÔNG TIN XƯỞNG / GIAN HÀNG -->
            <div class="form-step active" id="step-1">
                <div class="form-row-custom">
                    <div class="label-custom">
                        <span class="text-danger me-1">*</span>Tên Xưởng / Gian Hàng
                    </div>
                    <div class="input-wrap-custom position-relative">
                        <input type="text" class="form-control" id="shopNameInput" name="shop_name" placeholder="Ví dụ: Xưởng In 3D Sài Gòn" maxlength="30" required>
                        <span class="position-absolute end-0 top-50 translate-middle-y me-3 text-muted small" id="charCount">0/30</span>
                    </div>
                </div>

                <div class="form-row-custom">
                    <div class="label-custom">
                        <span class="text-danger me-1">*</span>Tên người liên hệ
                    </div>
                    <div class="input-wrap-custom">
                        <input type="text" class="form-control" name="fullname" placeholder="Nhập họ và tên người đại diện" required>
                    </div>
                </div>

                <div class="form-row-custom">
                    <div class="label-custom">
                        <span class="text-danger me-1">*</span>Địa chỉ Xưởng in / Kho
                    </div>
                    <div class="input-wrap-custom">
                        <input type="text" class="form-control mb-2" name="address_detail" placeholder="Số nhà, tên đường..." required>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="district" placeholder="Quận / Huyện / Tỉnh" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="city" placeholder="Tỉnh / Thành phố" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row-custom">
                    <div class="label-custom">
                        <span class="text-danger me-1">*</span>Email liên hệ
                    </div>
                    <div class="input-wrap-custom">
                        <input type="email" class="form-control" name="email" placeholder="example@gmail.com" required>
                    </div>
                </div>

                <div class="form-row-custom">
                    <div class="label-custom">
                        <span class="text-danger me-1">*</span>Số điện thoại
                    </div>
                    <div class="input-wrap-custom">
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted">+84</span>
                            <input type="tel" class="form-control" name="phone" placeholder="Nhập số điện thoại" required>
                        </div>
                    </div>
                </div>

                <div class="form-row-custom">
                    <div class="label-custom">Mô tả thiết bị in 3D</div>
                    <div class="input-wrap-custom">
                        <textarea class="form-control" name="description" rows="3" placeholder="Giới thiệu máy in hỗ trợ (FDM, Resin SLA), vật liệu in sẵn có..."></textarea>
                    </div>
                </div>
            </div>

            <!-- BƯỚC 2: NĂNG LỰC IN 3D -->
            <div class="form-step" id="step-2">
                <div class="form-row-custom">
                    <div class="label-custom"><span class="text-danger me-1">*</span>Công nghệ in hỗ trợ</div>
                    <div class="input-wrap-custom pt-1">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="tech[]" id="techFdm" value="FDM">
                            <label class="form-check-label" for="techFdm">FDM (Nhựa sợi)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="tech[]" id="techSla" value="SLA/Resin">
                            <label class="form-check-label" for="techSla">SLA / Resin (Nhựa lỏng)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="tech[]" id="techSls" value="SLS">
                            <label class="form-check-label" for="techSls">SLS (In bột)</label>
                        </div>
                    </div>
                </div>

                <div class="form-row-custom">
                    <div class="label-custom"><span class="text-danger me-1">*</span>Vật liệu có sẵn</div>
                    <div class="input-wrap-custom">
                        <input type="text" class="form-control" name="materials" placeholder="Ví dụ: PLA, ABS, PETG, Resin Standard, Resin Tough...">
                    </div>
                </div>

                <div class="form-row-custom">
                    <div class="label-custom">Kích thước in tối đa (mm)</div>
                    <div class="input-wrap-custom">
                        <input type="text" class="form-control" name="max_size" placeholder="Ví dụ: 300 x 300 x 400 mm">
                    </div>
                </div>
            </div>

            <!-- BƯỚC 3: THÔNG TIN ĐẠI DIỆN -->
            <div class="form-step" id="step-3">
                <div class="form-row-custom">
                    <div class="label-custom"><span class="text-danger me-1">*</span>Số CCCD / CMND</div>
                    <div class="input-wrap-custom">
                        <input type="text" class="form-control" name="id_card" placeholder="Nhập số căn cước công dân">
                    </div>
                </div>

                <div class="form-row-custom">
                    <div class="label-custom">Mã số thuế (nếu có)</div>
                    <div class="input-wrap-custom">
                        <input type="text" class="form-control" name="tax_code" placeholder="Nhập mã số thuế cá nhân hoặc xưởng">
                    </div>
                </div>

                <div class="form-row-custom">
                    <div class="label-custom">Tài khoản ngân hàng</div>
                    <div class="input-wrap-custom">
                        <input type="text" class="form-control mb-2" name="bank_name" placeholder="Tên ngân hàng (ví dụ: Vietcombank)">
                        <input type="text" class="form-control" name="bank_account" placeholder="Số tài khoản nhận thanh toán">
                    </div>
                </div>
            </div>

            <!-- BƯỚC 4: HOÀN TẤT -->
            <div class="form-step" id="step-4">
                <div class="text-center py-4">
                    <i class="fa-solid fa-circle-check text-success fs-1 mb-3"></i>
                    <h4 class="fw-bold">Hồ sơ đã sẵn sàng gửi!</h4>
                    <p class="text-muted">Vui lòng kiểm tra kỹ thông tin trước khi nhấn nút Gửi đăng ký bên dưới. Ban quản trị 3DPrintHub sẽ duyệt hồ sơ của bạn trong vòng 24h.</p>
                </div>
            </div>

            <hr class="my-4" style="border-top: 1px dashed #dee2e6;">

            <!-- NÚT ĐIỀU HƯỚNG CHUYỂN BƯỚC -->
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary px-4 d-none" id="btnPrev">Quay lại</button>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4">Lưu tạm</button>
                    <button type="button" class="btn btn-primary px-4 fw-bold" id="btnNext">Tiếp theo</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold d-none" id="btnSubmit">Gửi Hồ Sơ</button>
                </div>
            </div>

        </form>

    </div>
</div>

<!-- NHÚNG FILE JS XỬ LÝ CHUYỂN BƯỚC -->
<script src="../public/js/register_vendor.js"></script>

<?php require_once '../layouts/footer.php'; ?>