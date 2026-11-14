# 3DPrintHub — Số dư mua hàng + COD (PHP/MySQL/Laragon)

## Tính năng đã thêm
- Trang `index.php?page=wallet`: xem số dư, lịch sử biến động, yêu cầu nạp tiền, tra trạng thái nạp.
- Đặt hàng: chọn **Số dư 3DPrintHub**, **Thanh toán COD**, hoặc **Chuyển khoản VietQR**.
- Khi chọn số dư, đơn hàng được tạo cùng với việc trừ số dư trong **một giao dịch MySQL**; thiếu tiền thì giao dịch bị hủy toàn bộ.
- Mỗi giao dịch có chứng từ riêng, duy nhất theo (user, loại giao dịch, loại tham chiếu, id tham chiếu).
- Đơn số dư hủy khi còn ở trạng thái chờ xử lý sẽ hoàn tiền tự động và trả lại tồn kho.
- Seller được ghi nhận khoản *phải trả*, chưa tự chuyển tiền về ngân hàng.
- COD: Seller đổi đơn sang `shipping`, bấm **Đã thu COD** sau khi thật sự thu tiền, sau đó mới bấm `completed`.
- Admin: xem tổng số dư và lịch sử nạp, có thể cấp **tiền DEMO** cho khách khi chạy từ localhost.
- Nạp tiền qua payOS có API và webhook kiểm tra chữ ký HMAC, số tiền, mã thanh toán và tham chiếu; không dựa vào ảnh biên lai hay tham số từ trình duyệt.

## CẢNH BÁO PHÁP LÝ
Đây là **phần mềm demo sổ số dư**, không phải dịch vụ ví điện tử được cấp phép. Cho người dùng nạp tiền thật để giữ giá trị và dùng thanh toán ở nhiều gian hàng có thể thuộc hoạt động trung gian thanh toán/ví điện tử được quản lý theo Nghị định 52/2024/NĐ-CP và các quy định liên quan. **Không bật chức năng nạp tiền thật để kinh doanh** trước khi có tư vấn pháp lý và hợp đồng/tích hợp phù hợp với ngân hàng hoặc tổ chức trung gian thanh toán được cấp phép. Chức năng nạp thật mặc định **TẮT**.

## Cập nhật dự án có sẵn (không mất dữ liệu)
Website hiện tại trên máy:

`D:\laragon\www\3DPrintHub_Laragon\3DPrintHub`

1. **Sao lưu** thư mục website. Trong HeidiSQL: chuột phải `3dprinthub_db` → **Export database as SQL** để sao lưu database.
2. Giải nén `3DPrintHub_SoDu_COD_Update.zip`. Sao chép **nội dung bên trong** thư mục `3DPrintHub` của ZIP vào thư mục website trên; nhấn **Replace** để ghi đè các tệp trùng tên. Không xóa thư mục cũ và không ghi đè thư mục `uploads` đã có ảnh.
3. Mở Laragon → Start All → Database / HeidiSQL.
4. Chọn menu File → Run SQL file... để chạy `wallet_upgrade.sql`. **Trước đó phải có** 3 bản cập nhật cũ `upgrade.sql`, `payment_upgrade.sql`, `payos_upgrade.sql`. Nếu còn thiếu bản nào, chạy theo đúng thứ tự trước `wallet_upgrade.sql`.
5. Nhấn F5 ở HeidiSQL; kiểm tra bảng `wallet_accounts`, `wallet_transactions`, `wallet_topups`, `cod_collections`, `vendor_earnings`.
6. Mở `http://localhost/3DPrintHub_Laragon/3DPrintHub/` rồi Ctrl+F5.
7. Không chạy lại `database.sql` trên dữ liệu đang dùng, vì có lệnh DROP TABLE.

## Thử trực tiếp trên Laragon (KHÔNG dùng tiền thật)
1. Đăng nhập tài khoản mẫu admin: `admin@3dprinthub.vn` / `password`.
2. Mở **Admin** → **Quản lý số dư / Nạp tiền** → chọn khách mẫu (Nguyễn Văn An) → cộng 100.000đ **DEMO**. Chức năng cấp demo chỉ hiện khi truy cập `localhost` từ máy của bạn.
3. Đăng xuất, đăng nhập `nguyenvana@gmail.com` / `password`.
4. Chọn **Số dư** trên thanh menu để xem 100.000đ demo và lịch sử.
5. Thêm sản phẩm có giá phù hợp (nhỏ hơn số dư), chọn **Thanh toán bằng số dư 3DPrintHub** để thử trừ tiền. Đơn thành công xem tại **Đơn hàng**.
6. Thử **Thanh toán khi nhận hàng (COD)** với một đơn khác. Người bán đăng nhập để đổi từ `pending` → `printing` → `shipping`, xác nhận **Đã thu COD**, sau đó mới `completed`.
7. Với đơn số dư, Seller có thể hủy khi đơn còn `pending`, hệ thống hoàn tiền vào sổ của khách.

**Lưu ý:** database seed có sản phẩm giá trên 100.000đ, Admin có thể cấp demo tối đa 200.000đ/lần để kiểm tra (có thể thực hiện nhiều lần trên localhost). Demo credits không có nguồn tiền ngân hàng tương ứng.

## Tích hợp tiền nạp thật — chỉ sau khi đủ điều kiện pháp lý
- Có kênh payOS của đơn vị vận hành và tài khoản ngân hàng hợp lệ; khóa đặt ngoài thư mục web theo `HUONG_DAN_PAYOS.md`.
- Website có HTTPS public, đúng `site_url` trong config, đăng ký webhook `https://ten-mien/3DPrintHub/payos_webhook.php`.
- Sau khi pháp lý/hợp đồng được xác nhận, nhà triển khai mới được cấu hình biến môi trường máy chủ `THREEDPRINT_STORED_VALUE_ENABLED=1`. Không được đưa secret và config thật lên GitHub.
- Khách gọi `wallet_topup_start.php`; payOS trả link checkout; khi webhook HMAC được xác thực thành công và đối chiếu đúng số tiền, link ID, transaction ref, server ghi một bút toán credit duy nhất. Không phát tiền từ `returnUrl`.
- Nếu có khiếu nại/hoàn tiền/rút tiền, quy trình xử lý thủ công cần bổ sung và được kiểm toán; phần demo không có chức năng rút tiền, chuyển tiền giữa người dùng, chi trả cho người bán tự động hay hoàn trả topup tự động.

## Những gì chưa phù hợp để chạy kinh doanh
- Chưa có hợp đồng tổ chức cung cấp ví điện tử/thu hộ, quy trình KYC/AML, kiểm soát gian lận, giới hạn, đối soát định kỳ, audit logging hoàn chỉnh, hoàn tiền về ngân hàng, quyết toán người bán, dispute, nền tảng vận chuyển hay kiểm thử bảo mật toàn diện.
- COD ghi nhận theo xác nhận thủ công của người bán, không tự xác minh hãng vận chuyển.
- Tài khoản và thông tin ngân hàng trong SQL seed chỉ để thử, phải thay đổi ngay trước khi đưa lên mạng.
- Chưa chạy kiểm thử end-to-end trên MySQL của máy người dùng và chưa xử lý tiền thật.
