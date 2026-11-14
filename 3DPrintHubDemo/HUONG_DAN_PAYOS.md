# 3DPrintHub – Nhận tiền thật bằng payOS (PHP + Laragon)

## Tính năng
- Tạo payment link VietQR **thật** bằng payOS HTTP API, không cần Composer.
- Xác nhận tiền về bằng **signed webhook**, kiểm tra mã đơn, số tiền, mã payment link.
- Dữ liệu đơn cũ và thanh toán thủ công trước đó không bị xóa.
- Giỏ hàng nhiều gian hàng vẫn tách thành nhiều đơn; mỗi đơn có nút thanh toán.

> QUAN TRỌNG: Đây là bản minh họa tích hợp **một kênh thu tiền của chủ sàn**. Tiền về ngân hàng đã liên kết với kênh payOS. Chưa có ví giữ hộ tiền kiểu Shopee, hoàn tiền tự động, chi hộ/payout cho từng người bán, hóa đơn điện tử, và chưa là hệ thống production được kiểm thử đầy đủ.

## 1. Sao lưu
Sao lưu thư mục project và export database bằng HeidiSQL trước khi ghi đè.

## 2. Sao chép file
Sao chép toàn bộ thư mục `3DPrintHub` trong ZIP vào thư mục cũ tại:
`D:\laragon\www\3DPrintHub_Laragon\3DPrintHub`.
Nếu bạn chạy bản ZIP Full và chưa từng cài `payment_upgrade.sql`, cần chạy SQL theo thứ tự: `database.sql` (CHỈ khi mới hoàn toàn), `upgrade.sql`, `payment_upgrade.sql`, `payos_upgrade.sql`.
Nếu đã có bảng `order_payments` từ bản cũ thì **CHỈ** chạy `payos_upgrade.sql`. Không chạy lại `database.sql`.

## 3. Tạo tài khoản payOS
- Truy cập https://my.payos.vn và đăng ký/xác thực.
- Liên kết ngân hàng muốn nhận tiền.
- Tạo kênh thanh toán, nhận `Client ID`, `API Key`, `Checksum Key`.
- Theo tài liệu chính thức: https://payos.vn/docs/sdks/back-end/php/ và https://payos.vn/docs/api/.

## 4. Khai báo bí mật ngoài thư mục website
- Tạo file: `D:\laragon\etc\3dprinthub-payos.php`
- Copy nội dung `payos_config.example.php` vào file trên (lưu ý đường dẫn khác nhau tùy bạn cài Laragon tại ổ nào).
- Điền ba key và `site_url` là **URL HTTPS công khai** đến thư mục ứng dụng, không phải `localhost`.
  Ví dụ: `https://shop.example.com/3DPrintHub`.
- Tuyệt đối không up file chứa key lên GitHub.
- Nếu bạn đặt project khác vị trí, đặt biến môi trường `THREEDPRINT_PAYOS_CONFIG` = đường dẫn tuyệt đối tới file key.
- PHP của Laragon phải bật cURL, OpenSSL và PDO MySQL.

## 5. Đăng ký webhook HTTPS
Điền URL public chính xác theo `site_url`:
`https://shop.example.com/3DPrintHub/payos_webhook.php`
trong kênh thanh toán payOS. endpoint phải truy cập được từ Internet bằng HTTPS.
`http://localhost/...` không nhận được webhook từ payOS. Dùng web hosting/VPS hoặc HTTPS tunnel chỉ cho việc thử nghiệm.

## 6. Kiểm tra
- Khởi động Laragon, truy cập site qua HTTPS public URL.
- Đăng nhập khách hàng, bỏ sản phẩm vào giỏ, chọn `Chuyển khoản VietQR tự động qua payOS`.
- Nhấn đặt hàng, vào `Đơn hàng của tôi`, nhấn thanh toán trên từng đơn.
- Trên trang đơn: nhấn `Thanh toán ngay qua payOS`.
- Quét QR, thực hiện chuyển khoản thật bằng tài khoản ngân hàng khác, số tiền nhỏ.
- Sau khi tiền về, xem `order_payments.status = confirmed` và `payos_transactions.status = paid`.
- Không đánh dấu paid từ returnUrl hoặc ảnh biên lai (chỉ webhook chữ ký hợp lệ được cập nhật tự động).

## 7. Luồng sàn nhiều gian hàng
Trong bản này tất cả giao dịch tự động nhận về **một** kênh ngân hàng payOS của chủ sàn. Không có chức năng tự động chuyển tiền sang người bán. Để vận hành như Shopee, cần ký hợp đồng và xây dựng quy trình chi trả/hoàn tiền/tranh chấp, tuân thủ pháp luật và quy định bên cổng thanh toán.

## Lỗi hay gặp
- Không thấy phương thức payOS: kiểm tra `D:\laragon\etc\3dprinthub-payos.php` và `site_url` HTTPS.
- Cổng trả về lỗi: kiểm tra Client ID/API Key/Checksum Key đúng kênh; xem Apache PHP error log.
- Có tiền nhưng đơn chưa xác nhận: kiểm tra webhook URL, public HTTPS, signature, mã đơn và số tiền; đối soát thật với payOS/ngân hàng.
- Chưa có `order_payments`: phải import `payment_upgrade.sql` trước `payos_upgrade.sql`.
- Nếu dùng my.payos.vn cho thử nghiệm: payOS hiện **không có sandbox riêng**, tiền dùng là tiền thật (theo docs payOS).
