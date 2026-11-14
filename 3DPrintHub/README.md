# 3DPrintHub — PHP + MySQL + Laragon

## Cài đặt
1. Bật **Start All** trong Laragon (Apache/Nginx và MySQL).
2. Giải nén thư mục `3DPrintHub` vào `C:\laragon\www\3DPrintHub`.
3. Mở phpMyAdmin / HeidiSQL, chạy `database.sql` để tạo DB và dữ liệu mẫu.
4. **Sau đó** chạy `upgrade.sql` để thêm bảng lưu ảnh sản phẩm. Không chạy lại `database.sql` sau khi đã nhập dữ liệu thực vì file có DROP TABLE.
5. Kiểm tra `config.php`: mặc định MySQL host 127.0.0.1, root, mật khẩu trống.
6. Truy cập `http://localhost/3DPrintHub/` (hoặc `http://3dprinthub.test/` nếu Laragon tự tạo virtual host).

## Tài khoản mẫu
Các tài khoản trong `database.sql` có chung mật khẩu **password**:
- Admin: `admin@3dprinthub.vn`
- Người bán đã duyệt: `techlab@3dprinthub.vn`
- Người bán đã duyệt: `artprint@3dprinthub.vn`
- Khách: `nguyenvana@gmail.com`

**Đổi ngay mật khẩu tài khoản mẫu trước khi đưa lên môi trường thật.**

## Luồng sử dụng
- Khách đăng ký, thêm giỏ, thanh toán, xem đơn hàng, gửi file STL/OBJ báo giá.
- Khách đăng ký mở xưởng; Admin duyệt gian hàng, sau đó tài khoản chuyển sang vendor (cần đăng xuất và đăng nhập lại).
- Vendor đăng sản phẩm (chờ duyệt), sửa, ẩn, quản lý đơn, xét yêu cầu báo giá.
- Admin duyệt sản phẩm, gian hàng và khóa/mở tài khoản.
- Mỗi vendor trong giỏ được tách thành một đơn hàng riêng, tồn kho được cập nhật trong transaction.

## Giới hạn bản demo
- Giao diện mẫu dùng hình khối CSS cho các sản phẩm seed chưa có ảnh. Người bán có thể tải ảnh lên.
- File STL/OBJ được lưu nhưng **chưa tự tính thể tích từ hình học**; khách nhập thể tích ước lượng. Giá chỉ tạm tính.
- Chuyển khoản chưa tích hợp cổng thanh toán/xác minh giao dịch tự động.
- Chưa có quản lý vận chuyển, voucher, chat, email và xuất hóa đơn.
- Thư mục uploads cần quyền ghi; nên triển khai cơ chế lưu file ngoài webroot và xác thực file chuyên sâu trước khi đưa lên production.
- Đây là dự án demo học tập; cần kiểm thử bảo mật, giới hạn request, logging, xác minh email, hardening session và kiểm tra tải trước khi triển khai thực tế.
