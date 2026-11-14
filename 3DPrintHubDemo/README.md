# 3DPrintHub — PHP/MySQL + Số dư mua hàng + COD

**Hướng dẫn phiên bản mới:** `HUONG_DAN_SO_DU_VA_COD.md`

# 3DPrintHub — PHP + MySQL + Laragon (THANH TOÁN CHUYỂN KHOẢN)

## Cập nhật dự án đang có của bạn, KHÔNG mất dữ liệu

Dự án của bạn nằm tại:

`D:\laragon\www\3DPrintHub_Laragon\3DPrintHub\`

1. **Sao lưu** thư mục dự án và export database trong HeidiSQL (chọn `3dprinthub_db` → Tools → Export database as SQL).
2. Giải nén ZIP mới. **Chép các file/thư mục BÊN TRONG thư mục `3DPrintHub`** của ZIP vào đúng thư mục dự án trên, chọn Replace/Thay thế các file trùng tên. KHÔNG xóa thư mục cũ trước khi chép vì trong đó có ảnh đã upload.
3. Mở Laragon → **Start All** → **Database** (hoặc Menu → MySQL → HeidiSQL).
4. Ở HeidiSQL, chọn **File → Run SQL file...** → mở file `payment_upgrade.sql` trong thư mục dự án. Nếu mở ở Query editor thay vì chạy, nhấn **F9**.
5. Chọn `3dprinthub_db` bên trái → nhấn **F5**. Bạn phải thấy bảng mới **`order_payments`**; bảng `vendors` có thêm cột `bank_account_holder`.
6. Mở website đúng URL bạn đang dùng: `http://localhost/3DPrintHub_Laragon/3DPrintHub/`; Ctrl+F5 để cập nhật CSS/JS.

**QUAN TRỌNG:** Nếu bạn đã có `3dprinthub_db` và bảng `product_images`, **KHÔNG import lại `database.sql`**, vì file có lệnh `DROP TABLE` sẽ xóa dữ liệu cũ. `upgrade.sql` chỉ dùng cho bảng ảnh và chạy khi thiếu `product_images`.

### Nếu cài dự án mới từ đầu

Trong HeidiSQL chạy lần lượt: `database.sql` → `upgrade.sql` → `payment_upgrade.sql`.

### Cấu trúc phần thanh toán mới

- `index.php`: checkout và các luồng khách, người bán, Admin.
- `payments.php`: tiện ích VietQR.
- `views/payment.php`: trang thanh toán QR, nút copy, nộp biên lai.
- `views/orders.php`: danh sách đơn hàng và liên kết thanh toán.
- `views/seller_bank.php`: người bán cập nhật tài khoản nhận tiền.
- `views/seller_orders.php`: người bán xem và xử lý đơn.
- `views/admin_payments.php`: Admin đối soát thanh toán.
- `receipt.php`: chỉ khách sở hữu đơn/người bán liên quan/Admin xem được ảnh biên lai.
- `payment_upgrade.sql`: thêm cột chủ tài khoản và bảng order_payments; giữ dữ liệu đã có.

### Cách test

1. Đăng nhập **người bán** (`techlab@3dprinthub.vn` / `password`) → Kênh người bán → **Tài khoản nhận chuyển khoản** → nhập ngân hàng/số TK/tên chủ TK **do bạn sở hữu** (tài khoản có sẵn trong seed chỉ là mẫu). Nhấn Lưu.
2. Đăng xuất, đăng nhập **khách** (`nguyenvana@gmail.com` / `password`), thêm sản phẩm của xưởng vào giỏ.
3. Trong giỏ chọn **Chuyển khoản VietQR** → **Đặt hàng ngay**. Trang QR tự hiển thị số tiền và nội dung cho mã đơn duy nhất.
4. **Kiểm tra** tên người nhận, số TK, số tiền trong ứng dụng ngân hàng. KHÔNG chuyển vào các tài khoản mẫu trong database.
5. Với mục đích demo **không cần chuyển tiền thật**: bạn có thể bấm "Tôi đã chuyển khoản" để thử giao diện, nhưng không được dùng hành động này làm bằng chứng thanh toán.
6. Đăng nhập tài khoản người bán/Admin → mục Đơn hàng & thanh toán hoặc Đối soát chuyển khoản → xem trạng thái. Trong thực tế, chỉ xác nhận sau khi kiểm tra tiền về tài khoản.
7. Sau khi xác nhận, người bán mới có thể đổi trạng thái đơn chuyển khoản sang `printing`.

### Lưu ý quan trọng trước khi dùng thật

- Đây là **thanh toán VietQR với đối soát thủ công**, **KHÔNG** tích hợp tự động xác nhận giao dịch/ngân hàng hay hoàn tiền tự động.
- Mã QR tải từ dịch vụ ngoài `img.vietqr.io` (cần Internet; gửi tên ngân hàng, số tài khoản, số tiền và mã đơn đến nhà cung cấp QR). Nếu thiếu kết nối hoặc ngân hàng không hỗ trợ, khách vẫn thấy thông tin chuyển khoản thủ công.
- Mỗi gian hàng có **một đơn riêng và một nội dung chuyển khoản riêng**. Khách phải chuyển từng đơn.
- `bank_account_holder` chưa có trong dữ liệu mẫu; cần người bán khai báo. Không coi tên gian hàng là chủ tài khoản ngân hàng.
- Bằng chứng ảnh (tối đa 2MB JPG/PNG/WEBP) lưu ở MySQL, **không công khai đường dẫn ảnh**. Chỉ chủ đơn, xưởng liên quan hoặc Admin được xem.
- Mọi dữ liệu mẫu/mật khẩu `password` cần đổi và kiểm chứng trước khi dùng thật. Các tính năng quản lý tiền, xác thực giao dịch và chống gian lận phải được đánh giá thêm khi triển khai thương mại.


## Thêm tính năng MoMo DEMO / QR DEMO
Xem file `HUONG_DAN_THANH_TOAN_MOMO_QR_DEMO.md`. Chạy `demo_payments_upgrade.sql` sau `wallet_upgrade.sql`.
