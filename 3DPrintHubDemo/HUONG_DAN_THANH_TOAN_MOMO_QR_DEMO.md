# 3DPrintHub - Thanh toán MoMo DEMO / QR Ngân hàng DEMO

**LƯU Ý: KHÔNG THU TIỀN THẬT, KHÔNG TÍCH HỢP MÓMO HAY NGÂN HÀNG.** Đây là mô phỏng dành cho thuyết trình đồ án trên Laragon. Hai hình QR chứa văn bản 'DEMO / KHÔNG CHUYỂN TIỀN', không phải QR chuyển khoản.

## Cài đặt bản FULL (khi cài mới hoàn toàn, không có dữ liệu cũ)
1. Giải nén **3DPrintHub** vào `D:\laragon\www\` để có `D:\laragon\www\3DPrintHub\index.php`.
2. Trong Laragon nhấn `Start All`, mở `Database` (HeidiSQL) rồi `Open`.
3. HeidiSQL: `File > Run SQL file...` chạy lần lượt theo thứ tự:
   - `database.sql` (**CẢNH BÁO: xóa và tạo lại các bảng cũ!** Chỉ dùng khi cài MỚI)
   - `upgrade.sql`
   - `payment_upgrade.sql`
   - `payos_upgrade.sql`
   - `wallet_upgrade.sql`
   - `demo_payments_upgrade.sql`
4. Mở `http://localhost/3DPrintHub/`.

## Cài đặt bản UPDATE (nếu đã có database và website hoạt động)
1. Sao lưu thư mục dự án hiện tại và database MySQL.
2. Giải nén ZIP cập nhật, chép các file bên trong `3DPrintHub` vào đúng thư mục đang chứa `index.php`, chọn ghi đè.
3. Nếu đã chạy đủ các SQL từ phiên bản Số dư + COD, **chỉ chạy** `demo_payments_upgrade.sql` bằng HeidiSQL > File > Run SQL file.
4. Nhấn F5 làm mới bảng, kiểm tra bảng `demo_payments`. Nhấn Ctrl + F5 trong trình duyệt.
5. Nếu có lỗi 'Unknown column payment_provider' hoặc 'wallet_topups doesn't exist', chạy lần lượt các script nâng cấp còn thiếu `payment_upgrade.sql`, `payos_upgrade.sql`, `wallet_upgrade.sql`, cuối cùng `demo_payments_upgrade.sql`.

## Đăng nhập tài khoản mẫu
- Admin: `admin@3dprinthub.vn` / `password`
- Khách hàng: `nguyenvana@gmail.com` / `password`
- Người bán: `techlab@3dprinthub.vn` / `password`
Mật khẩu trên chỉ dùng cho dữ liệu mẫu. Đổi khi triển khai ra mạng.

## Thử thanh toán mua hàng
1. Đăng nhập tài khoản **khách hàng**.
2. Vào giỏ hàng, thêm sản phẩm, nhập thông tin người nhận và địa chỉ.
3. Chọn **MoMo DEMO** hoặc **QR ngân hàng DEMO**, bấm Đặt hàng.
4. Mở trang QR, nhấn **Mô phỏng thanh toán thành công** (không quét / chuyển tiền thật).
5. Hệ thống đổi `order_payments.status` thành `confirmed` và `demo_payments.status` thành `paid`.
6. Trong trang đơn hàng, thấy trạng thái đã thanh toán; đăng nhập người bán để xử lý đơn.

## Thử nạp vào số dư bằng MoMo DEMO / QR DEMO
1. Đăng nhập tài khoản khách, mở **Số dư**.
2. Khu vực **Nạp bằng MoMo / QR DEMO**, chọn số tiền 50.000–500.000đ và phương thức.
3. Nhấn tạo giao dịch, trên trang QR nhấn **Mô phỏng thanh toán thành công**.
4. Hệ thống cộng số dư và ghi lịch sử `wallet_transactions`. Nạp lặp một yêu cầu không cộng tiền lần 2.
5. Có thể mua bằng số dư đã cộng. COD vẫn hoạt động.

## Cách hoạt động bên trong
- Sử dụng bản PHP + MySQL hiện có và giao dịch PDO.
- Không tự cộng tiền trước khi người dùng bấm xác nhận (chỉ mô phỏng).
- Chỉ cho xác nhận DEMO từ `localhost`/`127.0.0.1` và đúng tài khoản chủ đơn/nạp.
- Đơn hàng nhiều người bán sẽ có từng trang QR riêng.
- Mã DEMO có thời gian chờ 15 phút, có nút làm mới nếu hết hạn.
- Nút xác nhận gửi POST + CSRF, cập nhật atomically trong transaction và chống ghi nhận trùng.
- Tính năng COD và ví hiện có vẫn dùng được.

## Lưu ý
Nếu đưa web lên hosting, tính năng xác nhận DEMO bị khóa theo thiết kế. Để thanh toán thật phải tích hợp API chính thức và webhook xác thực từ nhà cung cấp.
